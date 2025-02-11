<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiPostTagController extends Controller
{
    /**
     * Hiển thị danh sách các PostTag (API).
     */
    public function index()
    {
        $postTags = PostTag::orderBy('id', 'DESC')->paginate(10);
        return response()->json($postTags);
    }

    /**
     * Tạo mới PostTag (API).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'  => 'string|required',
            'status' => 'required|in:active,inactive'
        ]);

        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = PostTag::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;

        $postTag = PostTag::create($data);

        if ($postTag) {
            return response()->json(['success' => 'Post Tag đã được thêm thành công'], 201);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 400);
        }
    }

    /**
     * Cập nhật PostTag (API).
     */
    public function update(Request $request, $id)
    {
        $postTag = PostTag::findOrFail($id);

        $this->validate($request, [
            'title'  => 'string|required',
            'status' => 'required|in:active,inactive'
        ]);

        $data = $request->all();
        $status = $postTag->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Post Tag đã được cập nhật']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 400);
        }
    }

    /**
     * Xóa PostTag (API).
     */
    public function destroy($id)
    {
        $postTag = PostTag::findOrFail($id);

        $status = $postTag->delete();

        if ($status) {
            return response()->json(['success' => 'Post Tag đã được xóa']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi xóa Post Tag'], 400);
        }
    }
}
