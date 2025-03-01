<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiPostController extends Controller
{
    /**
     * Hiển thị danh sách bài viết (API).
     */
    public function index()
    {
        $posts = Post::orderBy('id', 'DESC')->paginate(10);
        return response()->json($posts);
    }

    /**
     * Tạo mới bài viết (API).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'       => 'string|required',
            'quote'       => 'string|nullable',
            'summary'     => 'string|required',
            'description' => 'string|nullable',
            'photo'       => 'string|nullable',
            'tags'        => 'nullable',
            'added_by'    => 'nullable',
            'post_cat_id' => 'required',
            'status'      => 'required|in:active,inactive'
        ]);

        $data = $request->all();

        // Xử lý slug
        $slug  = Str::slug($request->title);
        $count = Post::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;

        // Xử lý tags
        $tags = $request->input('tags');
        if ($tags) {
            $data['tags'] = implode(',', $tags);
        } else {
            $data['tags'] = '';
        }

        $post = Post::create($data);

        if ($post) {
            return response()->json(['success' => 'Bài viết đã được thêm thành công'], 201);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 400);
        }
    }

    /**
     * Cập nhật bài viết (API).
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $this->validate($request, [
            'title'       => 'string|required',
            'quote'       => 'string|nullable',
            'summary'     => 'string|required',
            'description' => 'string|nullable',
            'photo'       => 'string|nullable',
            'tags'        => 'nullable',
            'added_by'    => 'nullable',
            'post_cat_id' => 'required',
            'status'      => 'required|in:active,inactive'
        ]);

        $data = $request->all();

        // Xử lý tags
        $tags = $request->input('tags');
        if ($tags) {
            $data['tags'] = implode(',', $tags);
        } else {
            $data['tags'] = '';
        }

        $status = $post->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Bài viết đã được cập nhật']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại'], 400);
        }
    }

    /**
     * Xóa bài viết (API).
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        $status = $post->delete();

        if ($status) {
            return response()->json(['success' => 'Bài viết đã được xóa']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi xóa bài viết'], 400);
        }
    }
}
