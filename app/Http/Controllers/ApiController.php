<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;

class ApiController extends Controller
{
    // người dùng
    public function users()
    {
        $users = User::orderBy('id', 'ASC')->get();
        return response()->json($users, 200);
    }

    public function updateusers(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validatedData = $request->validate([
            'name' => 'string|required|max:30',
            'email' => 'string|required|unique:email',
            'password' => 'string|required',
            'role' => 'required|in:admin,user,doctor',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|string',
        ]);

        // Thêm dữ liệu vào database
        $validatedData['password'] = Hash::make($request->password);
        
        try {
            $user = User::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được thêm thành công',
                'data' => $user
            ], 201); 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi thêm người dùng',
                'error' => $e->getMessage()
            ], 500); 
        }
    }

    // sản phẩm
    public function products()
    {
        $products = Product::getAllProduct();
        return response()->json($products, 200); 
    }

    // thêm tiếp vào đây 
    
}
