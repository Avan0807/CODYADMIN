<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AffiliateOrder;

class ApiDoctorCommissionController extends Controller
{
    // API lấy hoa hồng và thông tin đơn hàng cho bác sĩ đăng nhập
    public function getDoctorCommission()
    {
        $doctor = Auth::user(); // Lấy thông tin bác sĩ đăng nhập

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng nhập.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Tính tổng hoa hồng đã nhận
        $totalCommission = AffiliateOrder::where('doctor_id', $doctor->id)
            ->where('status', 'delivered')
            ->sum('commission');

        // Lấy chi tiết đơn hàng và thông tin người mua
        $orders = DB::table('affiliate_orders')
            ->join('orders', 'affiliate_orders.order_id', '=', 'orders.id')
            ->select(
                'orders.id as order_id',
                'orders.order_number',
                'orders.first_name',
                'orders.last_name',
                'orders.phone',
                'orders.email',
                'orders.total_amount',
                'affiliate_orders.commission',
                'affiliate_orders.status'
            )
            ->where('affiliate_orders.doctor_id', $doctor->id)
            ->where('affiliate_orders.status', 'delivered')
            ->orderBy('orders.id', 'DESC')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Danh sách hoa hồng và đơn hàng của bác sĩ',
            'total_commission' => number_format($totalCommission, 0, ',', '.') . ' đ',
            'orders' => $orders
        ], Response::HTTP_OK);
    }
}
