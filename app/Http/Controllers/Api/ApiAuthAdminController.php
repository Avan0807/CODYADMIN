<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ApiAuthAdminController extends Controller
{
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        // Kiểm tra thông tin đăng nhập và quyền admin
        $user = User::where('phone', $credentials['phone'])->where('role', 'admin')->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Thông tin đăng nhập không hợp lệ'], 401);
        }

        // Tạo token
        $token = $user->createToken('AdminToken')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Đã đăng xuất'], 200);
    }

    public function approvePayout($id) {
        // Lấy thông tin yêu cầu rút tiền
        $payout = \DB::table('doctor_payouts')->where('id', $id)->first();

        if (!$payout) {
            return response()->json(['error' => 'Không tìm thấy yêu cầu rút tiền.'], 404);
        }

        if ($payout->status !== 'pending') {
            return response()->json(['error' => 'Yêu cầu rút tiền đã được xử lý trước đó.'], 400);
        }

        // Lấy thông tin bác sĩ
        $doctor = \DB::table('doctors')->where('id', $payout->doctor_id)->first();

        if (!$doctor) {
            return response()->json(['error' => 'Không tìm thấy thông tin bác sĩ.'], 404);
        }

        // Kiểm tra số tiền rút có hợp lệ không
        if ($payout->amount <= 0 || $payout->amount > $doctor->total_commission) {
            return response()->json(['error' => 'Số tiền rút không đủ.'], 400);
        }

        // Cập nhật số dư hoa hồng của bác sĩ
        \DB::table('doctors')->where('id', $payout->doctor_id)->update([
            'total_commission' => $doctor->total_commission - $payout->amount
        ]);

        // Cập nhật trạng thái yêu cầu rút tiền thành "approved"
        \DB::table('doctor_payouts')->where('id', $id)->update([
            'status' => 'approved',
            'processed_at' => now()
        ]);

        return response()->json([
            'message' => 'Yêu cầu rút tiền đã được duyệt.',
            'amount' => $payout->amount
        ], 200);
    }




    public function rejectPayout($id) {
        $payout = \DB::table('doctor_payouts')->where('id', $id)->first();

        if (!$payout) {
            return response()->json(['error' => 'Không tìm thấy yêu cầu rút tiền.'], 404);
        }

        if ($payout->status !== 'pending') {
            return response()->json(['error' => 'Yêu cầu rút tiền đã được xử lý trước đó.'], 400);
        }

        // Cập nhật trạng thái thành 'rejected'
        \DB::table('doctor_payouts')->where('id', $id)->update([
            'status' => 'rejected',
            'processed_at' => now() // Lưu thời gian từ chối
        ]);

        return response()->json([
            'message' => 'Yêu cầu rút tiền đã bị từ chối.',
            'payout_id' => $id
        ], 200);
    }

}
