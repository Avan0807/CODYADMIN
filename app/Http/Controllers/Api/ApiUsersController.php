<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiUsersController extends Controller
{
    /**
     * Hiển thị danh sách người dùng.
     */
    public function index()
    {
        $users = User::orderBy('id', 'ASC')->paginate(10); // Lấy danh sách người dùng
        return response()->json($users);
    }

    /**
     * Tạo người dùng mới.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|required|max:30',
            'email' => 'string|required|unique:users,email',
            'phone' => 'required|digits:10|unique:users,phone',
            'password' => 'string|required',
            'role' => 'required|in:admin,user,doctor',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data = $request->all();
        $data['password'] = Hash::make($request->password);  // Mã hóa mật khẩu

        $user = User::create($data);
        return response()->json($user, 201);  // Trả về dữ liệu người dùng mới tạo
    }

    /**
     * Hiển thị thông tin người dùng (chi tiết).
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy người dùng'], 404); // Thông báo lỗi nếu không tìm thấy người dùng
        }

        return response()->json($user);  // Trả về thông tin người dùng
    }

    /**
     * Cập nhật thông tin người dùng.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy người dùng'], 404); // Thông báo lỗi nếu không tìm thấy người dùng
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|required|max:30',
            'email' => 'string|required|unique:users,email,' . $id,
            'phone' => 'required|digits:10|unique:users,phone',
            'role' => 'required|in:admin,user,doctor',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400); // Trả về lỗi nếu validate không thành công
        }

        $data = $request->all();
        $user->fill($data);
        $user->save();

        return response()->json($user);  // Trả về người dùng đã được cập nhật
    }

    /**
     * Xóa người dùng khỏi hệ thống.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy người dùng'], 404); // Thông báo lỗi nếu không tìm thấy người dùng
        }

        $user->delete();  // Xóa người dùng

        return response()->json(['message' => 'Người dùng đã bị xóa thành công']);  // Thông báo xóa thành công
    }
}
