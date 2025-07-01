<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgentOrder;
use App\Models\Agent;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CommissionAgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:agent');
    }

    /**
     * Trang thống kê commission
     */
    public function index(Request $request)
    {
        $agentId = Auth::guard('agent')->id();
        
        // ✅ Thống kê tổng quan
        $stats = $this->getCommissionOverview($agentId);
        
        // ✅ Lịch sử commission theo tháng
        $monthlyCommissions = $this->getMonthlyCommissions($agentId, 12);
        
        // ✅ Top sản phẩm theo commission
        $topProducts = $this->getTopProductsByCommission($agentId, 10);
        
        // ✅ Lịch sử commission gần đây
        $recentCommissions = AgentOrder::with(['order', 'product'])
            ->byAgent($agentId)
            ->whereHas('order')
            ->whereHas('product')
            ->where('commission', '>', 0)
            ->latest()
            ->paginate(15);

        return view('agent.commissions.index', compact(
            'stats', 'monthlyCommissions', 'topProducts', 'recentCommissions'
        ));
    }

    /**
     * Thống kê tổng quan commission
     */
    private function getCommissionOverview($agentId)
    {
        $now = Carbon::now();
        
        // ✅ Tổng commission từ trước đến nay
        $totalAllTime = AgentOrder::byAgent($agentId)->sum('commission');
        
        // ✅ Commission đã nhận
        $totalReceived = AgentOrder::byAgent($agentId)->paid()->sum('commission');
        
        // ✅ Commission chờ thanh toán
        $totalPending = AgentOrder::byAgent($agentId)->pending()->sum('commission');
        
        // ✅ Commission tháng này
        $thisMonth = AgentOrder::byAgent($agentId)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum('commission');
        
        // ✅ Commission tháng trước
        $lastMonth = AgentOrder::byAgent($agentId)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->whereMonth('created_at', $now->copy()->subMonth()->month)
            ->sum('commission');
        
        // ✅ Commission hôm nay
        $today = AgentOrder::byAgent($agentId)
            ->whereDate('created_at', $now->toDateString())
            ->sum('commission');
        
        // ✅ Commission tuần này
        $thisWeek = AgentOrder::byAgent($agentId)
            ->whereBetween('created_at', [
                $now->startOfWeek(),
                $now->endOfWeek()
            ])
            ->sum('commission');
        
        // ✅ Tính tỷ lệ tăng trưởng
        $growthRate = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;
        
        // ✅ Commission trung bình mỗi đơn
        $avgPerOrder = AgentOrder::byAgent($agentId)
            ->where('commission', '>', 0)
            ->avg('commission') ?? 0;
        
        // ✅ Số đơn có commission
        $ordersWithCommission = AgentOrder::byAgent($agentId)
            ->where('commission', '>', 0)
            ->count();

        return [
            'total_all_time' => $totalAllTime,
            'total_received' => $totalReceived,
            'total_pending' => $totalPending,
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'today' => $today,
            'this_week' => $thisWeek,
            'growth_rate' => $growthRate,
            'avg_per_order' => $avgPerOrder,
            'orders_with_commission' => $ordersWithCommission,
        ];
    }

    /**
     * Lấy commission theo từng tháng
     */
    private function getMonthlyCommissions($agentId, $months = 12)
    {
        $data = AgentOrder::byAgent($agentId)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(commission) as total, COUNT(*) as orders_count")
            ->where('created_at', '>=', now()->subMonths($months))
            ->where('commission', '>', 0)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // ✅ Đảm bảo có đủ data cho tất cả tháng
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthData = $data->firstWhere('month', $month);
            
            $result[] = [
                'month' => $month,
                'month_name' => now()->subMonths($i)->format('m/Y'),
                'total' => $monthData ? $monthData->total : 0,
                'orders_count' => $monthData ? $monthData->orders_count : 0,
            ];
        }

        return $result;
    }

    /**
     * Top sản phẩm theo commission
     */
    private function getTopProductsByCommission($agentId, $limit = 10)
    {
        return AgentOrder::with('product')
            ->byAgent($agentId)
            ->whereHas('product')
            ->selectRaw('product_id, COUNT(*) as orders_count, SUM(commission) as total_commission')
            ->where('commission', '>', 0)
            ->groupBy('product_id')
            ->orderBy('total_commission', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * API: Lấy dữ liệu cho charts
     */
    public function getChartData(Request $request)
    {
        $agentId = Auth::guard('agent')->id();
        $type = $request->get('type', 'monthly'); // monthly, weekly, daily
        $period = $request->get('period', 12); // số lượng period
        
        switch ($type) {
            case 'weekly':
                $data = $this->getWeeklyCommissions($agentId, $period);
                break;
            case 'daily':
                $data = $this->getDailyCommissions($agentId, $period);
                break;
            default:
                $data = $this->getMonthlyCommissions($agentId, $period);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Commission theo tuần
     */
    private function getWeeklyCommissions($agentId, $weeks = 8)
    {
        $data = AgentOrder::byAgent($agentId)
            ->selectRaw("YEARWEEK(created_at) as week, SUM(commission) as total, COUNT(*) as orders_count")
            ->where('created_at', '>=', now()->subWeeks($weeks))
            ->where('commission', '>', 0)
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();

        $result = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $yearWeek = $weekStart->format('oW');
            $weekData = $data->firstWhere('week', $yearWeek);
            
            $result[] = [
                'week' => $yearWeek,
                'week_name' => $weekStart->format('d/m') . ' - ' . $weekStart->endOfWeek()->format('d/m'),
                'total' => $weekData ? $weekData->total : 0,
                'orders_count' => $weekData ? $weekData->orders_count : 0,
            ];
        }

        return $result;
    }

    /**
     * Commission theo ngày
     */
    private function getDailyCommissions($agentId, $days = 30)
    {
        $data = AgentOrder::byAgent($agentId)
            ->selectRaw("DATE(created_at) as date, SUM(commission) as total, COUNT(*) as orders_count")
            ->where('created_at', '>=', now()->subDays($days))
            ->where('commission', '>', 0)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dayData = $data->firstWhere('date', $date);
            
            $result[] = [
                'date' => $date,
                'date_name' => now()->subDays($i)->format('d/m'),
                'total' => $dayData ? $dayData->total : 0,
                'orders_count' => $dayData ? $dayData->orders_count : 0,
            ];
        }

        return $result;
    }

    /**
     * Export commission report
     */
    public function export(Request $request)
    {
        $agentId = Auth::guard('agent')->id();
        $agent = Auth::guard('agent')->user();
        
        $commissions = AgentOrder::with(['order', 'product'])
            ->byAgent($agentId)
            ->whereHas('order')
            ->whereHas('product')
            ->where('commission', '>', 0);

        // Apply filters
        if ($request->filled('date_from')) {
            $commissions->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $commissions->whereDate('created_at', '<=', $request->date_to);
        }

        $commissions = $commissions->latest()->get();

        $filename = 'commission_report_' . $agent->name . '_' . now()->format('Y_m_d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'Ngày',
                'Mã Đơn Hàng',
                'Sản Phẩm',
                'Khách Hàng',
                'Giá Sản Phẩm',
                'Hoa Hồng',
                'Tỷ Lệ (%)',
                'Trạng Thái',
                'Ngày Thanh Toán'
            ]);

            // Data rows
            foreach ($commissions as $commission) {
                fputcsv($file, [
                    $commission->created_at->format('d/m/Y'),
                    $commission->order->order_number ?? '',
                    $commission->product->title ?? '',
                    ($commission->order->first_name ?? '') . ' ' . ($commission->order->last_name ?? ''),
                    number_format($commission->product->price ?? 0),
                    number_format($commission->commission),
                    $commission->commission_percentage . '%',
                    $commission->status,
                    $commission->paid_at ? $commission->paid_at->format('d/m/Y') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}