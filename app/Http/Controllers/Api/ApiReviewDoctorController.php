<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ApiReviewDoctorController extends Controller
{

    // Lấy danh sách đánh giá của một bác sĩ
    public function index($doctor_id)
    {
        // Lấy danh sách đánh giá của bác sĩ cùng với thông tin người dùng và bác sĩ
        $reviews = DoctorReview::with(['user', 'doctor']) // Eager load quan hệ user và doctor
            ->where('doctor_id', $doctor_id)
            ->latest()
            ->get();

        // Chỉ lấy thông tin cần thiết từ mối quan hệ user và doctor
        $reviews = $reviews->map(function ($review) {
            return [
                'user_name' => $review->user->name,
                'doctor_name' => $review->doctor->name,
                'review' => $review->review,
                'rating' => $review->rating,
            ];
        });

        return response()->json([
            'success' => true,
            'reviews' => $reviews
        ]);
    }


    // Thêm đánh giá mới
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Bạn cần đăng nhập để đánh giá bác sĩ.'], 401);
        }

        $validatedData = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string'
        ]);

        $review = DoctorReview::create([
            'doctor_id' => $validatedData['doctor_id'],
            'user_id' => Auth::id(),
            'rating' => $validatedData['rating'],
            'review' => $validatedData['review'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được lưu!',
            'review' => $review
        ]);
    }
}
