<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AffiliateOrder;
use Illuminate\Http\Request;

class ApiAffiliateOrdersController extends Controller
{
    /**
     * API: Danh sách đơn hàng Affiliate của bác sĩ đang đăng nhập.
     */
    public function index(Request $request)
    {
        try {
            // Lấy thông tin bác sĩ từ người dùng đăng nhập
            $doctor = auth()->user();

            // Kiểm tra quyền
            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền truy cập.',
                ], 403);
            }

            $query = AffiliateOrder::where('doctor_id', $doctor->id)
                ->with(['order']);

            // Lọc theo tháng/năm nếu có
            if ($request->has('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            if ($request->has('year')) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $orders = $query->orderByDesc('created_at')->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách đơn hàng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
