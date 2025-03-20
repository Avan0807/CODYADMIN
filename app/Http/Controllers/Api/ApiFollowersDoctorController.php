<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorFollower;
use Illuminate\Support\Facades\Auth;

class ApiFollowersDoctorController extends Controller
{
    // Lấy tất cả bác sĩ trong wishlist của người dùng
    public function index(Request $request)
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        $user = $request->user();  // Lấy người dùng hiện tại từ request

        if (!$user) {
            return response()->json(['message' => 'Bạn cần đăng nhập để thực hiện thao tác này.'], 401);
        }

        // Lấy tất cả bác sĩ mà người dùng đã theo dõi
        $doctors = $user->doctors;

        return response()->json([
            'success' => true,
            'doctors' => $doctors
        ]);
    }

    // Thêm bác sĩ vào wishlist
    public function store(Request $request, $doctor_id)
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        $user = $request->user();  // Lấy người dùng hiện tại từ request

        if (!$user) {
            return response()->json(['message' => 'Bạn cần đăng nhập để thực hiện thao tác này.'], 401);
        }

        // Kiểm tra xem bác sĩ có trong hệ thống không
        $validatedData = [
            'doctor_id' => $doctor_id
        ];

        $doctor = \App\Models\Doctor::find($validatedData['doctor_id']);

        if (!$doctor) {
            return response()->json(['message' => 'Bác sĩ không tồn tại.'], 404);
        }

        // Kiểm tra xem bác sĩ đã có trong wishlist chưa
        $existingFollower = DoctorFollower::where('user_id', $user->id)
            ->where('doctor_id', $validatedData['doctor_id'])
            ->first();

        if ($existingFollower) {
            return response()->json(['message' => 'Bác sĩ này đã có trong wishlist của bạn.'], 400);
        }

        // Thêm bác sĩ vào wishlist
        $doctorFollower = DoctorFollower::create([
            'user_id' => $user->id,
            'doctor_id' => $validatedData['doctor_id'],
        ]);

        // Lấy thông tin bác sĩ và người dùng
        $doctor = $doctorFollower->doctor; // Lấy bác sĩ
        $user = $doctorFollower->user; // Lấy người dùng

        return response()->json([
            'success' => true,
            'message' => 'Bác sĩ đã được thêm vào wishlist của bạn!',
            'doctor_follower' => [
                'doctor_name' => $doctor->name,  // Tên bác sĩ
                'user_name' => $user->name,  // Tên người dùng
            ],
        ]);
    }

    // Xóa bác sĩ khỏi wishlist
    public function destroy($doctor_id)
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Bạn cần đăng nhập để thực hiện thao tác này.'], 401);
        }

        // Tìm bác sĩ trong wishlist của người dùng
        $doctorFollower = DoctorFollower::where('user_id', $user->id)
            ->where('doctor_id', $doctor_id)
            ->first();

        if (!$doctorFollower) {
            return response()->json(['message' => 'Bác sĩ này không có trong wishlist của bạn.'], 404);
        }

        // Lấy thông tin người dùng và bác sĩ
        $doctor = $doctorFollower->doctor; // Lấy bác sĩ
        $user = $doctorFollower->user; // Lấy người dùng

        // Xóa bác sĩ khỏi wishlist
        $doctorFollower->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bác sĩ đã được xóa khỏi wishlist của bạn!',
            'doctor_follower' => [
                'doctor_name' => $doctor->name,  // Tên bác sĩ
                'user_name' => $user->name,  // Tên người dùng
            ],
        ]);
    }
}
