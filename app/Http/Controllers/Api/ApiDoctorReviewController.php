<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorReview;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ApiDoctorReviewController extends Controller
{
    public function __construct()
    {
        // Chỉ cho phép người dùng đã đăng nhập thực hiện đánh giá và xóa đánh giá
        $this->middleware('auth:sanctum')->only(['store', 'destroy']);
    }

    /**
     * Lấy danh sách đánh giá của bác sĩ (Ai cũng có thể xem).
     */
    public function index($doctor_id)
    {
        // Kiểm tra nếu bác sĩ tồn tại
        $doctor = Doctor::findOrFail($doctor_id); // Sử dụng firstOrFail để tự động trả về lỗi nếu không tìm thấy

        // Lấy danh sách đánh giá của bác sĩ
        $reviews = DoctorReview::where('doctor_id', $doctor_id)
                ->with(['doctor:id,name', 'user:id,name,email']) // Tải chỉ các trường cần thiết
                ->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'message' => 'Bác sĩ chưa có đánh giá nào.',
            ], 404);
        }

        return response()->json($reviews, 200);
    }


    /**
     * Người dùng đăng đánh giá bác sĩ (Chỉ người dùng đã đăng nhập).
     */
    public function store(Request $request)
    {
        try {
            // Kiểm tra nếu người dùng đã đánh giá bác sĩ này
            $existingReview = DoctorReview::where('doctor_id', $request->doctor_id)
                                         ->where('user_id', Auth::id())
                                         ->first();

            if ($existingReview) {
                return response()->json([
                    'error' => 'Bạn đã đánh giá bác sĩ này rồi.',
                ], 400);
            }

            $request->validate([
                'doctor_id' => 'required|exists:doctors,id',
                'rating'    => 'required|integer|min:1|max:5',
                'review'    => 'nullable|string',
            ]);

            $review = DoctorReview::create([
                'doctor_id' => $request->doctor_id,
                'user_id'   => Auth::id(),
                'rating'    => $request->rating,
                'review'    => $request->review,
            ]);

            return response()->json([
                'message' => 'Đánh giá của bạn đã được lưu!',
                'data'    => $review
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => 'Lỗi xác thực',
                'message' => $e->errors(),
            ], 422);
        }
    }


    /**
     * Xóa đánh giá (Chỉ admin có thể xóa).
     */
    public function destroy($id)
    {
        $review = DoctorReview::findOrFail($id);

        // Kiểm tra xem người dùng có phải là admin không
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'error' => 'Bạn không có quyền xóa đánh giá này.',
            ], 403);
        }

        // Xóa đánh giá
        $review->delete();

        return response()->json([
            'message' => 'Đánh giá đã được xóa.',
        ], 200);
    }
}
