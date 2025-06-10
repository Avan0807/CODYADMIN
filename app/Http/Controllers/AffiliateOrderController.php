<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliateOrder;
use App\Models\Order;
use App\Models\Doctor;
use Carbon\Carbon;

class AffiliateOrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng Affiliate.
     */
    public function index()
    {
        // Eager load với product để hiển thị đầy đủ thông tin
        $affiliateOrders = AffiliateOrder::with(['doctor', 'order', 'product'])
            ->whereHas('doctor')
            ->whereHas('order')
            ->latest('created_at')
            ->get();

        return view('backend.affiliate_orders.index', compact('affiliateOrders'));
    }

    /**
     * Cập nhật trạng thái affiliate order
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,cancelled'
        ]);

        $affiliateOrder = AffiliateOrder::findOrFail($id);
        
        // Cập nhật status
        $affiliateOrder->status = $request->status;
        
        // Nếu đánh dấu đã trả, lưu thời gian
        if ($request->status === 'paid') {
            $affiliateOrder->paid_at = now();
        } elseif ($request->status === 'pending') {
            $affiliateOrder->paid_at = null;
        }
        
        $affiliateOrder->save();

        // Flash message
        $statusText = [
            'paid' => 'đã thanh toán',
            'pending' => 'chờ thanh toán', 
            'cancelled' => 'đã hủy'
        ];

        return redirect()->back()->with('success', 
            "Đã cập nhật trạng thái hoa hồng thành '{$statusText[$request->status]}'"
        );
    }

    /**
     * Thống kê hoa hồng
     */
    public function stats()
    {
        $stats = [
            'total_commission' => AffiliateOrder::sum('commission'),
            'pending_commission' => AffiliateOrder::where('status', 'pending')->sum('commission'),
            'paid_commission' => AffiliateOrder::where('status', 'paid')->sum('commission'),
            'cancelled_commission' => AffiliateOrder::where('status', 'cancelled')->sum('commission'),
            'total_orders' => AffiliateOrder::count(),
            'this_month_commission' => AffiliateOrder::whereMonth('created_at', now()->month)
                                                   ->whereYear('created_at', now()->year)
                                                   ->sum('commission'),
        ];

        return response()->json($stats);
    }

    /**
     * Báo cáo hoa hồng theo bác sĩ
     */
    public function reportByDoctor()
    {
        $doctorStats = AffiliateOrder::with('doctor')
            ->selectRaw('doctor_id, 
                         COUNT(*) as total_orders,
                         SUM(commission) as total_commission,
                         SUM(CASE WHEN status = "pending" THEN commission ELSE 0 END) as pending_commission,
                         SUM(CASE WHEN status = "paid" THEN commission ELSE 0 END) as paid_commission')
            ->groupBy('doctor_id')
            ->having('total_commission', '>', 0)
            ->get();

        return view('backend.affiliate_orders.report', compact('doctorStats'));
    }

    /**
     * Thanh toán hàng loạt
     */
    public function bulkPay(Request $request)
    {
        $request->validate([
            'affiliate_order_ids' => 'required|array',
            'affiliate_order_ids.*' => 'exists:affiliate_orders,id'
        ]);

        $updated = AffiliateOrder::whereIn('id', $request->affiliate_order_ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 
            "Đã thanh toán {$updated} đơn hàng hoa hồng"
        );
    }

    /**
     * Export dữ liệu hoa hồng
     */
    public function export(Request $request)
    {
        $query = AffiliateOrder::with(['doctor', 'order', 'product']);
        
        // Filter theo ngày
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Filter theo trạng thái
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $affiliateOrders = $query->get();
        
        // Export CSV hoặc Excel
        return response()->streamDownload(function() use ($affiliateOrders) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, [
                'ID', 'Mã đơn hàng', 'Bác sĩ', 'Sản phẩm', 
                'Hoa hồng', 'Trạng thái', 'Ngày tạo', 'Ngày thanh toán'
            ]);
            
            // Data
            foreach ($affiliateOrders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->order->order_number ?? 'N/A',
                    $order->doctor->name ?? 'N/A',
                    $order->product->title ?? 'N/A',
                    number_format($order->commission, 0),
                    $order->status,
                    $order->created_at->format('d/m/Y'),
                    $order->paid_at ? $order->paid_at->format('d/m/Y') : ''
                ]);
            }
            
            fclose($handle);
        }, 'affiliate_orders_' . date('Y-m-d') . '.csv');
    }
}