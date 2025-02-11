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
}
