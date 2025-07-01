<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgentOrder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderAgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:agent');
    }

    /**
     * Hiển thị danh sách đơn hàng của agent
     */
    public function index(Request $request)
    {
        $agentId = Auth::guard('agent')->id();
        
        // Base query
        $query = AgentOrder::with(['order', 'product'])
            ->byAgent($agentId)
            ->whereHas('order') // Chỉ lấy có order
            ->whereHas('product'); // Chỉ lấy có product

        // Filter theo status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter theo thời gian
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search theo mã đơn hoặc tên sản phẩm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('order', function($orderQuery) use ($search) {
                    $orderQuery->where('order_number', 'like', "%{$search}%")
                             ->orWhere('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function($productQuery) use ($search) {
                    $productQuery->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Pagination
        $orders = $query->latest()->paginate(15);

        // Thống kê tổng quan
        $stats = $this->getOrderStats($agentId, $request);

        return view('agent.orders.index', compact('orders', 'stats'));
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        $agentId = Auth::guard('agent')->id();
        
        $agentOrder = AgentOrder::with(['order', 'product', 'agent'])
            ->byAgent($agentId)
            ->whereHas('order')
            ->whereHas('product')
            ->findOrFail($id);

        return view('agent.orders.show', compact('agentOrder'));
    }

    /**
     * Export đơn hàng ra Excel/CSV
     */
    public function export(Request $request)
    {
        $agentId = Auth::guard('agent')->id();
        
        $query = AgentOrder::with(['order', 'product'])
            ->byAgent($agentId)
            ->whereHas('order')
            ->whereHas('product');

        // Áp dụng các filter giống như index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->get();

        // Tạo CSV
        $filename = 'agent_orders_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'Mã Đơn',
                'Sản Phẩm', 
                'Khách Hàng',
                'Email',
                'Ngày Tạo',
                'Hoa Hồng',
                'Tỷ Lệ HH (%)',
                'Trạng Thái',
                'Ngày Thanh Toán'
            ]);

            // Data rows
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order->order_number ?? '',
                    $order->product->title ?? '',
                    ($order->order->first_name ?? '') . ' ' . ($order->order->last_name ?? ''),
                    $order->order->email ?? '',
                    $order->created_at->format('d/m/Y H:i'),
                    number_format($order->commission, 0, ',', '.'),
                    $order->commission_percentage,
                    $order->status,
                    $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Lấy thống kê đơn hàng
     */
    private function getOrderStats($agentId, $request)
    {
        $query = AgentOrder::byAgent($agentId);

        // Áp dụng filter thời gian nếu có
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $baseQuery = clone $query;

        return [
            'total_orders' => $baseQuery->count(),
            'total_commission' => $baseQuery->sum('commission'),
            'paid_commission' => $baseQuery->paid()->sum('commission'),
            'pending_commission' => $baseQuery->pending()->sum('commission'),
            'cancelled_orders' => $baseQuery->cancelled()->count(),
            
            // Thống kê theo status
            'pending_orders' => $baseQuery->pending()->count(),
            'paid_orders' => $baseQuery->paid()->count(),
            
            // Thống kê theo thời gian
            'orders_today' => AgentOrder::byAgent($agentId)->whereDate('created_at', today())->count(),
            'orders_this_week' => AgentOrder::byAgent($agentId)->whereBetween('created_at', [
                now()->startOfWeek(), 
                now()->endOfWeek()
            ])->count(),
            'orders_this_month' => AgentOrder::byAgent($agentId)->thisMonth()->count(),
            
            // Commission trong tháng
            'commission_this_month' => AgentOrder::byAgent($agentId)->thisMonth()->sum('commission'),
        ];
    }

    /**
     * Lấy dữ liệu biểu đồ commission theo tháng
     */
    public function getCommissionChart(Request $request)
    {
        $agentId = Auth::guard('agent')->id();
        
        $months = $request->get('months', 6); // Mặc định 6 tháng
        
        $data = AgentOrder::byAgent($agentId)
            ->selectRaw("DATE_FORMAT(created_at, '%m-%Y') as month, SUM(commission) as total")
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderByRaw("STR_TO_DATE(month, '%m-%Y')")
            ->get()
            ->pluck('total', 'month');

        return response()->json($data);
    }

    /**
     * Dashboard widgets data cho orders
     */
    public function getWidgetData()
    {
        $agentId = Auth::guard('agent')->id();
        
        $data = [
            'recent_orders' => AgentOrder::with(['order', 'product'])
                ->byAgent($agentId)
                ->whereHas('order')
                ->whereHas('product')
                ->latest()
                ->take(5)
                ->get(),
                
            'top_products' => AgentOrder::with('product')
                ->byAgent($agentId)
                ->whereHas('product')
                ->selectRaw('product_id, COUNT(*) as orders_count, SUM(commission) as total_commission')
                ->groupBy('product_id')
                ->orderBy('orders_count', 'desc')
                ->take(5)
                ->get(),
        ];

        return response()->json($data);
    }
}