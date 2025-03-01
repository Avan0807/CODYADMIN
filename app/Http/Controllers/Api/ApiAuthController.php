<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ApiAuthController extends Controller
{
    /**
     * Đăng ký người dùng mới.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2',
            'phone' => 'required|digits:10|unique:users,phone',
            'email' => 'nullable|string|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'address' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ!',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'address' => $request->address,
                'province' => $request->province ?? '' // Thêm province mặc định
            ]);

            $token = $user->createToken('YourAppName')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công!',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đăng ký thất bại, vui lòng thử lại!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đăng nhập người dùng.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ!',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->where('status', 'active')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin đăng nhập không chính xác!'
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('YourAppName')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }

    /**
     * Cập nhật thông tin người dùng.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng!'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|min:2',
            'email' => 'nullable|string|email|unique:users,email,' . $id,
            'phone' => 'nullable|digits:10|unique:users,phone,' . $id,
            'password' => 'nullable|min:6|confirmed',
            'address' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ!',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'phone', 'password', 'address', 'province']));

        return response()->json([
            'success' => true,
            'message' => 'Thông tin người dùng đã được cập nhật!',
            'data' => $user
        ], 200);
    }

    /**
     * Xóa người dùng.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng!'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Người dùng đã bị xóa thành công!'
        ], 200);
    }
    /**
     * Đăng xuất người dùng.
     */
    public function logout(Request $request)
    {
        // Xóa tất cả token của người dùng hiện tại
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công!'
        ], 200);
    }

}
