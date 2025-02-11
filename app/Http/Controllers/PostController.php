<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Contracts\View\View as ViewContract;

class PostController extends Controller
{
    /**
     * Hiển thị danh sách bài viết (admin).
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::getAllPost();
        return view('backend.post.index')->with('posts', $posts);
    }

    /**
     * Trang thêm mới bài viết (admin).
     */
    public function create()
    {
        $categories = PostCategory::get();
        $tags       = PostTag::get();
        $users      = User::get();
        return view('backend.post.create')
            ->with('users', $users)
            ->with('categories', $categories)
            ->with('tags', $tags);
    }

    /**
     * Lưu bài viết mới.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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

        $status = Post::create($data);
        if ($status) {
            request()->session()->flash('success', 'Bài viết đã được thêm');
        } else {
            request()->session()->flash('error', 'Vui lòng thử lại!!');
        }
        return redirect()->route('post.index');
    }

    /**
     * Hiển thị chi tiết bài viết (nếu cần).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Hiện chưa sử dụng
    }

    /**
     * Trang chỉnh sửa bài viết (admin).
     */
    public function edit($id)
    {
        $post       = Post::findOrFail($id);
        $categories = PostCategory::get();
        $tags       = PostTag::get();
        $users      = User::get();

        return view('backend.post.edit')
            ->with('categories', $categories)
            ->with('users', $users)
            ->with('tags', $tags)
            ->with('post', $post);
    }

    /**
     * Cập nhật bài viết.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
            request()->session()->flash('success', 'Bài viết đã được cập nhật');
        } else {
            request()->session()->flash('error', 'Vui lòng thử lại!!');
        }
        return redirect()->route('post.index');
    }

    /**
     * Xóa bài viết.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post   = Post::findOrFail($id);
        $status = $post->delete();

        if ($status) {
            request()->session()->flash('success', 'Bài viết đã được xóa');
        } else {
            request()->session()->flash('error', 'Có lỗi xảy ra khi xóa bài viết');
        }
        return redirect()->route('post.index');
    }

}
