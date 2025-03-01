<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiBrandController extends Controller
{
    /**
     * Danh sách thương hiệu (API).
     */
    public function index()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10); // Lấy danh sách thương hiệu
        return response()->json($brands);
    }

    /**
     * Tạo mới một thương hiệu (API).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'string|required',
        ]);

        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = Brand::where('slug', $slug)->count();

        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }

        $data['slug'] = $slug;

        $brand = Brand::create($data);

        if ($brand) {
            return response()->json(['success' => 'Thêm thương hiệu thành công'], 201);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi, vui lòng thử lại'], 400);
        }
    }

    /**
     * Cập nhật thông tin thương hiệu (API).
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['error' => 'Không tìm thấy thương hiệu'], 404);
        }

        $this->validate($request, [
            'title' => 'string|required',
        ]);

        $data = $request->all();
        $status = $brand->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Cập nhật thương hiệu thành công']);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi, vui lòng thử lại'], 400);
        }
    }

    /**
     * Xóa thương hiệu (API).
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['error' => 'Không tìm thấy thương hiệu'], 404);
        }

        $status = $brand->delete();

        if ($status) {
            return response()->json(['success' => 'Xóa thương hiệu thành công']);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi, vui lòng thử lại'], 400);
        }
    }
}
