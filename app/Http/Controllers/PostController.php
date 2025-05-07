<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\Category;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Storage;

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
        $categories = Category::type('other')->orderBy('name')->get();
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
        $categories = Category::type('other')->orderBy('name')->get();
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


    //API---------------------------------------------------------------------------------------------------------------------


    public function apiCreatePost(Request $request)
    {
        $doctor = $request->user(); // Authenticated doctor

        if (!$doctor || !$doctor->doctorID) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này.',
            ], 403);
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'tags'         => 'required|string|max:255',
            'summary'      => 'required|string|max:500',
            'description'  => 'required|string',
            'photo'        => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048',
            'quote'        => 'nullable|string',
            'post_cat_id'  => 'required|exists:post_categories,id',
            'post_tag_id'  => 'nullable|exists:post_tags,id',
            'status'       => 'required|in:active,inactive',
        ]);

        try {
            $photoUrl = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $fileName = 'posts/' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();

                Storage::disk('s3')->put($fileName, file_get_contents($file));
                $photoUrl = Storage::disk('s3')->url($fileName);
            }

            $post = Post::create([
                'title'        => $validated['title'],
                'slug'         => Str::slug($validated['title']) . '-' . uniqid(),
                'tags'         => $validated['tags'],
                'summary'      => $validated['summary'],
                'description'  => $validated['description'],
                'photo'        => $photoUrl,
                'quote'        => $validated['quote'] ?? null,
                'post_cat_id'  => 'required|exists:categories,id',
                'post_tag_id'  => $validated['post_tag_id'] ?? null,
                'status'       => $validated['status'],
                'added_by'     => $doctor->doctorID,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bài viết đã được tạo thành công!',
                'data'    => $post
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tạo bài viết thất bại!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function apiGetAllPosts()
    {
        try {
            $posts = Post::with(['category', 'doctor', 'user'])
                ->where('status', 'active')
                ->orderByDesc('id')
                ->paginate(10);


            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách bài đăng thành công.',
                'data'    => $posts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách bài đăng.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function apiGetPostBySlug($slug)
    {
        try {
            $post = Post::with(['category', 'tag_info', 'doctor', 'user'])
                ->where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bài đăng.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy bài đăng thành công.',
                'data'    => $post,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy bài viết.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
