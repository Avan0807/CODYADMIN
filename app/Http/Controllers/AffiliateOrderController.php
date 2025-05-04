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
        // Lấy tất cả các đơn hàng affiliate có liên kết với order và doctor
        $affiliateOrders = AffiliateOrder::with(['doctor', 'order'])
            ->whereHas('doctor')
            ->whereHas('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('backend.affiliate_orders.index', compact('affiliateOrders'));
    }

}
