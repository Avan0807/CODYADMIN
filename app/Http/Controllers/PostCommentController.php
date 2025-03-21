<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Notification;
use App\Models\User;
use App\Models\Doctor;
use App\Notifications\StatusNotification;
use App\Models\PostComment;
use Illuminate\Contracts\View\View as ViewContract;

class PostCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $comments = PostComment::getAllComments();
        return view('backend.comment.index')->with('comments', $comments);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();
        $post_info = Post::getPostBySlug($request->slug);
        // return $post_info;
        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        // $data['post_id']=$post_info->id;
        $data['status'] = 'active';
        // return $data;
        $status = PostComment::create($data);
        $user = User::where('role', 'admin')->get();
        $details = [
            'title' => "Bình luận mới được tạo",
            'actionURL' => route('blog.detail', $post_info->slug),
            'fas' => 'fas fa-comment'
        ];
        Notification::send($user, new StatusNotification($details));
        if ($status) {
            request()->session()->flash('success', 'Cảm ơn bạn đã bình luận');
        } else {
            request()->session()->flash('error', 'Có lỗi xảy ra! Vui lòng thử lại!!');
        }
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $comments = PostComment::find($id);
        if ($comments) {
            return view('backend.comment.edit')->with('comment', $comments);
        } else {
            request()->session()->flash('error', 'Không tìm thấy bình luận');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $comment = PostComment::find($id);
        if ($comment) {
            $data = $request->all();
            // return $data;
            $status = $comment->fill($data)->update();
            if ($status) {
                request()->session()->flash('success', 'Bình luận đã được cập nhật');
            } else {
                request()->session()->flash('error', 'Có lỗi xảy ra! Vui lòng thử lại!!');
            }
            return redirect()->route('comment.index');
        } else {
            request()->session()->flash('error', 'Không tìm thấy bình luận');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comment = PostComment::find($id);
        if ($comment) {
            $status = $comment->delete();
            if ($status) {
                request()->session()->flash('success', 'Đăng bình luận đã xóa');
            } else {
                request()->session()->flash('error', 'Đã xảy ra lỗi, vui lòng thử lại');
            }
            return redirect()->back();
        } else {
            request()->session()->flash('error', 'Đăng bình luận không tìm thấy');
            return redirect()->back();
        }
    }

    //API---------------------------------------------------------------------------

    public function getCommentsByPostId($postId)
    {
        try {
            $cacheKey = "comments_post_{$postId}";

            $comments = cache()->remember($cacheKey, now()->addMinutes(10), function () use ($postId) {
                return PostComment::where('post_id', $postId)
                    ->with(['user_info:id,name,email,phone', 'doctor_info:id,name,email,phone', 'replies:id,comment,parent_id,status,created_at'])
                    ->where('status', 'active')
                    ->paginate(10);
            });

            // Duyệt qua các bình luận để thêm thông tin user_info và doctor_info nếu có
            $comments->getCollection()->transform(function ($comment) {
                // Nếu có user_id, lấy thông tin user
                if ($comment->user_id) {
                    $comment->user_info = $comment->user_info ? $comment->user_info->only(['name', 'email', 'phone']) : null;
                }

                // Nếu có doctor_id, lấy thông tin doctor
                if ($comment->doctor_id) {
                    $comment->doctor_info = $comment->doctor_info ? $comment->doctor_info->only(['name', 'email', 'phone']) : null;
                }

                return $comment;
            });

            return response()->json([
                'success' => true,
                'data' => $comments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy dữ liệu!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiCreateComment(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id', // post_id phải tồn tại trong bảng posts
            'comment' => 'required|string|max:1000', // Nội dung comment không được trống
            'parent_id' => 'nullable|exists:post_comments,id', // parent_id hợp lệ nếu có
        ]);

        try {
            // Lấy thông tin người dùng hiện tại
            $user = auth()->user();

            // Kiểm tra nếu người dùng là bác sĩ (tìm bác sĩ trong bảng Doctors)
            $doctor = Doctor::where('id', $user->id)->first(); // Kiểm tra nếu là bác sĩ

            if ($doctor) {
                // Nếu là bác sĩ, lưu doctorID vào user_id
                $user_id = $doctor->doctorID;
            } else {
                // Nếu không phải bác sĩ, lưu user_id vào user_id
                $user_id = $user->id;
            }

            // Tạo comment mới
            $comment = PostComment::create([
                'user_id' => $user_id, // Lưu user_id hoặc doctorID tùy theo người đăng nhập
                'post_id' => $validated['post_id'], // ID bài viết
                'comment' => $validated['comment'], // Nội dung comment
                'parent_id' => $validated['parent_id'] ?? null, // Nếu không có parent_id thì null
                'status' => 'active', // Trạng thái mặc định là active
            ]);

            // Trả về kết quả thành công
            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'Comment đã được tạo thành công!',
            ], 201);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo thất bại
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo comment!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function apiGetCommentById($postId, $commentId)
    {
        try {
            // Kiểm tra bài viết có tồn tại hay không
            $post = Post::findOrFail($postId);

            // Lấy comment cụ thể theo ID trong bài viết
            $comment = PostComment::where('id', $commentId)
                ->where('post_id', $postId) // Chỉ lấy comment trong bài viết
                ->where('status', 'active') // Lấy comment có trạng thái 'active'
                ->with('user_info') // Nếu có thông tin người dùng liên quan
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $comment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy comment hoặc bài viết!',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    public function apiUpdateComment(Request $request, $postId, $commentId)
    {
        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'comment' => 'required|string|max:1000', // Nội dung comment là bắt buộc
        ]);

        try {
            // Kiểm tra bài viết có tồn tại hay không
            $post = Post::findOrFail($postId);

            // Tìm comment trong bài viết
            $comment = PostComment::where('id', $commentId)
                ->where('post_id', $postId) // Chỉ lấy comment trong bài viết này
                ->firstOrFail();

            // Lấy thông tin người dùng hiện tại
            $user = auth()->user();

            // Kiểm tra nếu người dùng là bác sĩ (tìm bác sĩ trong bảng Doctors)
            $doctor = Doctor::where('id', $user->id)->first(); // Kiểm tra nếu là bác sĩ

            if ($doctor) {
                // Nếu là bác sĩ, lưu doctorID vào user_id
                $user_id = $doctor->doctorID;
            } else {
                // Nếu không phải bác sĩ, lưu user_id vào user_id
                $user_id = $user->id;
            }

            // Kiểm tra quyền chỉnh sửa
            // Bác sĩ có thể chỉnh sửa comment của mình hoặc comment của bệnh nhân
            if ($comment->user_id !== $user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền chỉnh sửa comment này!',
                ], 403);
            }

            // Cập nhật comment
            $comment->update([
                'comment' => $validated['comment'],
            ]);

            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'Cập nhật comment thành công!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật comment!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function apiReplyComment(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'comment' => 'required|string|max:1000',
            'parent_id' => 'required|exists:post_comments,id', // Bắt buộc phải có parent_id để biết đang reply comment nào
        ]);

        try {
            // Lấy thông tin người dùng hiện tại
            $user = auth()->user();

            // Kiểm tra nếu người dùng là bác sĩ
            $doctor = Doctor::where('id', $user->id)->first();

            if ($doctor) {
                $user_id = null;
                $doctor_id = $doctor->doctorID;
            } else {
                $user_id = $user->id;
                $doctor_id = null;
            }

            // Tạo comment trả lời
            $reply = PostComment::create([
                'user_id' => $user_id,
                'doctor_id' => $doctor_id,
                'post_id' => $validated['post_id'],
                'comment' => $validated['comment'],
                'parent_id' => $validated['parent_id'],
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'data' => $reply,
                'message' => 'Phản hồi đã được tạo thành công!',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo phản hồi!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
