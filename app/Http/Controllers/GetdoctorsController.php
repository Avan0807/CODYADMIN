<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;

class GetdoctorsController extends Controller
{
    /**
     * Get list of doctors.
     * - Nếu user đăng nhập, lấy bác sĩ cùng tỉnh.
     * - Nếu không đăng nhập, trả về toàn bộ danh sách bác sĩ.
     */
    public function apiHome(Request $request)
    {
        try {
            $user = Auth::user(); // Lấy user nếu có đăng nhập

            if ($user) {
                \Log::info('User ID: ' . $user->id);
                \Log::info('User Province: ' . $user->province);

                // Lọc danh sách bác sĩ theo tỉnh của user
                $doctors = Doctor::where('location', 'like', "%{$user->province}%")->get();
            } else {
                // Nếu chưa đăng nhập, trả về toàn bộ danh sách bác sĩ
                $doctors = Doctor::all();
            }

            \Log::info('Doctors Found: ', $doctors->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Danh sách bác sĩ',
                'doctors' => $doctors,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in GetDoctors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách bác sĩ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
