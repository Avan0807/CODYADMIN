<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiPostCategoryController extends Controller
{
    /**
     * Hiển thị danh sách danh mục bài viết (API).
     */
    public function index()
    {
        $postCategories = PostCategory::orderBy('id', 'DESC')->paginate(10);
        return response()->json($postCategories);
    }

    /**
     * Tạo mới danh mục bài viết (API).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'  => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = PostCategory::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;

        $postCategory = PostCategory::create($data);

        if ($postCategory) {
            return response()->json(['success' => 'Đã thêm danh mục bài viết thành công'], 201);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 400);
        }
    }

    /**
     * Cập nhật danh mục bài viết (API).
     */
    public function update(Request $request, $id)
    {
        $postCategory = PostCategory::findOrFail($id);

        $this->validate($request, [
            'title'  => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        $status = $postCategory->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Đã cập nhật danh mục bài viết thành công']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 400);
        }
    }

    /**
     * Xóa danh mục bài viết (API).
     */
    public function destroy($id)
    {
        $postCategory = PostCategory::findOrFail($id);

        $status = $postCategory->delete();

        if ($status) {
            return response()->json(['success' => 'Đã xóa danh mục bài viết thành công']);
        } else {
            return response()->json(['error' => 'Lỗi khi xóa danh mục bài viết'], 400);
        }
    }
}
