<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;

class GetdoctorsController extends Controller
{
    /**
     * Get list of doctors in the same province as the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apihome(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập',
                ], 401);
            }

            \Log::info('User ID: ' . $user->id);
            \Log::info('User Province: ' . $user->province);

            $doctors = Doctor::where('location', 'like', "%{$user->province}%")->get();
            \Log::info('Doctors Found: ', $doctors->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Danh sách bác sĩ',
                'province' => $user->province,
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
