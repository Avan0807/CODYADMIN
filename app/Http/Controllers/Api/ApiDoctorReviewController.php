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
        $doctor = Doctor::find($doctor_id);

        if (!$doctor) {
            return response()->json([
                'error' => 'Bác sĩ không tồn tại.',
            ], 404);
        }

        // Lấy danh sách đánh giá của bác sĩ
        $reviews = DoctorReview::where('doctor_id', $doctor_id)->with('user')->get();

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
