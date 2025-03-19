<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliateOrder;
use App\Models\Order;
use App\Models\Doctor;

class AffiliateOrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng Affiliate.
     */
    public function index()
    {
        $affiliateOrders = AffiliateOrder::whereHas('order', function ($query) {
            $query->whereNotNull('doctor_id');
        })->with(['doctor', 'order'])->get();

        return view('backend.affiliate_orders.index', compact('affiliateOrders'));
    }


    /**
     * Cập nhật trạng thái đơn hàng Affiliate.
     */

    public function updateStatus(Request $request, $id)
    {
        $affiliateOrder = AffiliateOrder::findOrFail($id);

        // Chỉ chấp nhận trạng thái hợp lệ
        $validStatuses = ['new', 'process', 'delivered', 'cancel'];
        if (!in_array($request->status, $validStatuses)) {
            return redirect()->back()->with('error', 'Trạng thái không hợp lệ!');
        }

        // Cập nhật trạng thái đơn hàng Affiliate
        $affiliateOrder->status = $request->status;
        $affiliateOrder->save();

        // Đồng bộ trạng thái của Order
        $order = Order::find($affiliateOrder->order_id);
        if ($order) {
            $order->status = $request->status;
            $order->save();
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công!');
    }

}
