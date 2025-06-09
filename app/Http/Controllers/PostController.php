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
        $doctor = $request->user();

        if (!$doctor || !$doctor->doctorID) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này.',
            ], 403);
        }

        // Validation rules cơ bản
        $validationRules = [
            'title' => 'required|string|max:255',
            'summary' => 'required|string|max:500',
            'description' => 'required|string',
            'post_cat_id' => 'required|exists:categories,id', // Sửa lại table name
            'post_type' => 'required|in:post,event,story,research,video',
            'tags' => 'nullable|string|max:255',
            'quote' => 'nullable|string',
            'status' => 'in:active,inactive',
            'image_option' => 'required|in:upload,link,none',
        ];

        // Validation cho image
        if ($request->image_option == 'upload') {
            $validationRules['photo'] = 'required|file|mimes:webp,jpeg,png,jpg,gif|max:2048';
        } elseif ($request->image_option == 'link') {
            $validationRules['photo_url'] = 'required|url';
        }

        // Validation cho từng loại post
        if ($request->post_type == 'event') {
            $validationRules['meta_data.event_start_date'] = 'required|date_format:Y-m-d\TH:i';
            $validationRules['meta_data.event_end_date'] = 'required|date_format:Y-m-d\TH:i|after_or_equal:meta_data.event_start_date';
            $validationRules['meta_data.location'] = 'required|string';
        } elseif ($request->post_type == 'video') {
            $validationRules['meta_data.video_url'] = 'required|url';
        } elseif ($request->post_type == 'research') {
            $validationRules['document_file'] = 'nullable|file|mimes:pdf,doc,docx|max:10240';
        }

        // Validation cho clinic IDs
        if ($request->has('clinic_ids')) {
            $validationRules['clinic_ids'] = 'string'; // Chuỗi IDs cách nhau bởi dấu phẩy
        }

        try {
            $validated = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Xử lý metadata
            $metaData = $request->meta_data ?? [];

            // Xử lý đặc biệt cho research
            if ($request->post_type == 'research') {
                if (isset($metaData['co_authors'])) {
                    $metaData['co_authors'] = is_string($metaData['co_authors']) 
                        ? json_decode($metaData['co_authors'], true) 
                        : $metaData['co_authors'];
                }

                // Upload document file
                if ($request->hasFile('document_file')) {
                    $file = $request->file('document_file');
                    $fileName = Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();
                    $filePath = Storage::disk('s3')->putFileAs('documents/research', $file, $fileName, 'public');
                    $metaData['document_url'] = Storage::disk('s3')->url($filePath);
                }
            }

            // Xử lý video topics
            if ($request->post_type == 'video' && isset($metaData['topics'])) {
                $metaData['topics'] = is_string($metaData['topics']) 
                    ? json_decode($metaData['topics'], true) 
                    : $metaData['topics'];
            }

            // Xử lý ảnh
            $photoUrl = null;
            if ($request->image_option == 'upload' && $request->hasFile('photo')) {
                $file = $request->file('photo');
                $fileName = 'posts/' . Str::slug($request->title) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                Storage::disk('s3')->put($fileName, file_get_contents($file), 'public');
                $photoUrl = Storage::disk('s3')->url($fileName);
            } elseif ($request->image_option == 'link' && $request->filled('photo_url')) {
                $photoUrl = $request->photo_url;
            }

            // Tạo post
            $post = Post::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . uniqid(),
                'summary' => $request->summary,
                'description' => $request->description,
                'post_cat_id' => $request->post_cat_id,
                'status' => $request->status ?? 'active',
                'added_by' => $doctor->doctorID,
                'author_type' => 'doctor',
                'post_type' => $request->post_type,
                'tags' => $request->tags,
                'quote' => $request->quote,
                'photo' => $photoUrl,
                'meta_data' => $metaData,
            ]);

            // Xử lý clinic relationships
            if (in_array($post->post_cat_id, range(88, 100)) && $request->has('clinic_ids') && !empty($request->clinic_ids)) {
                $clinicIds = array_filter(
                    explode(',', $request->clinic_ids), 
                    function ($id) {
                        return is_numeric($id) && $id > 0;
                    }
                );

                if (!empty($clinicIds)) {
                    $post->clinics()->sync($clinicIds);
                }
            }

            // Load relationships cho response
            $post->load(['category', 'clinics']);

            return response()->json([
                'success' => true,
                'message' => 'Bài viết đã được tạo thành công!',
                'data' => $post
            ], 201);

        } catch (\Exception $e) {
            \Log::error('API Create Post Error: ' . $e->getMessage(), [
                'user_id' => $doctor->doctorID,
                'request_data' => $request->except(['photo', 'document_file'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Tạo bài viết thất bại!',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
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
