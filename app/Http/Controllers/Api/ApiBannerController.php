<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiBannerController extends Controller
{
    /**
     * Hiển thị danh sách các banner.
     */
    public function index()
    {
        $banners = Banner::orderBy('id', 'DESC')->paginate(10);
        return response()->json($banners);
    }

    /**
     * Tạo mới một banner.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'string|required|max:50',
            'description' => 'string|nullable',
            'photo' => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = Banner::where('slug', $slug)->count();

        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }

        $data['slug'] = $slug;
        
        // Tạo mới banner
        $banner = Banner::create($data);

        if ($banner) {
            return response()->json(['success' => 'Banner đã được thêm thành công'], 201);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi khi thêm banner'], 400);
        }
    }

    /**
     * Hiển thị chi tiết banner.
     */
    public function show($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['error' => 'Banner không tồn tại'], 404);
        }

        return response()->json($banner);
    }

    /**
     * Cập nhật thông tin banner.
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $this->validate($request, [
            'title' => 'string|required|max:50',
            'description' => 'string|nullable',
            'photo' => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        $status = $banner->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Banner đã được cập nhật thành công']);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi khi cập nhật banner'], 400);
        }
    }

    /**
     * Xóa banner.
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->delete()) {
            return response()->json(['success' => 'Banner đã được xóa thành công']);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa banner'], 400);
        }
    }
}
