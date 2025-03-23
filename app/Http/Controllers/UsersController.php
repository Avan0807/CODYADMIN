<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('id', 'ASC')->get();
        return view('backend.users.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): ViewContract
    {
        return view('backend.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'string|required|max:30',
                'email' => 'string|required|email|unique:users,email',
                'phone' => 'required|string|max:15',
                'password' => 'required|string|min:6',
                'role' => 'required|in:admin,user',
                'status' => 'required|in:active,inactive',
                'photo' => 'nullable|string',
            ]
        );

        $data = $request->all();
        $data['password'] = Hash::make($request->password); // Mã hóa mật khẩu

        // Kiểm tra nếu người dùng không nhập ảnh thì gán ảnh mặc định
        if (empty($data['photo'])) {
            $data['photo'] = 'default-avatar.png'; // Thay bằng ảnh mặc định của bạn
        }

        $status = User::create($data);

        if ($status) {
            request()->session()->flash('success', 'Người dùng đã được thêm thành công');
        } else {
            request()->session()->flash('error', 'Đã xảy ra lỗi khi thêm người dùng');
        }

        return redirect()->route('users.index');
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
        $user = User::findOrFail($id);
        return view('backend.users.edit')->with('user', $user);
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
        $user = User::findOrFail($id);

        $this->validate(
            $request,
            [
                'name' => 'string|required|max:30',
                'email' => 'string|required|email|unique:users,email,'.$id,
                'phone' => 'required|string|max:15',
                'role' => 'required|in:admin,user',
                'status' => 'required|in:active,inactive',
                'photo' => 'nullable|string',
            ]
        );

        // dd($request->all());
        $data = $request->all();
        // dd($data);

        $status = $user->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Đã cập nhật thành công');
        } else {
            request()->session()->flash('error', 'Đã xảy ra lỗi khi cập nhật');
        }
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = User::findorFail($id);
        $status = $delete->delete();
        if ($status) {
            request()->session()->flash('success', 'Người dùng đã bị xóa thành công');
        } else {
            request()->session()->flash('error', 'Có lỗi khi xóa người dùng');
        }
        return redirect()->route('users.index');
    }

    public function apiGetUserByID($id)
    {
        try {
            // Tìm user theo userID
            $user = User::find($id);

            // Kiểm tra nếu user không tồn tại
            if (!$user) {
                return response()->json([
                    'message' => 'Không tìm thấy người dùng.'
                ], 404);
            }

            return response()->json([
                'message' => 'Người dùng đã truy xuất thành công.',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi truy xuất người dùng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function apiUpdateUser(Request $request)
    {
        try {
            // Lấy user đang đăng nhập
            $user = auth()->user();

            // Xác thực dữ liệu
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:191',
                'email' => 'nullable|email|max:191|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
                'photo' => 'nullable|string',
                'address' => 'nullable|string|max:255',
            ]);

            // Loại bỏ trường 'role' và 'password' (không cập nhật trong API này)
            unset($validatedData['role']);
            unset($validatedData['password']);

            // Xử lý ảnh đại diện nếu có
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = 'uploads/photos/' . $fileName;

                Storage::disk('s3')->put($filePath, file_get_contents($file));
                $validatedData['photo'] = Storage::disk('s3')->url($filePath);

                // Xóa ảnh cũ trên S3 nếu có
                if ($user->photo) {
                    $oldPath = str_replace(Storage::disk('s3')->url(''), '', $user->photo);
                    Storage::disk('s3')->delete($oldPath);
                }
            }

            // Cập nhật dữ liệu
            $user->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công.',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thông tin.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy tất cả thông báo của user
     */
    public function getNotifications(Request $request, $userID)
    {
        $user = $request->user();  // Người dùng hiện tại

        // Kiểm tra quyền truy cập
        if ($user->id != $userID) {
            return response()->json([
                'success' => false,
                'message' => 'Truy cập trái phép vào thông báo của người dùng.',
            ], 403);
        }

        // Lấy thông tin người dùng
        $targetUser = User::find($userID);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng.',
            ], 404);
        }

        // Lấy tất cả thông báo mà không phân trang
        $notifications = $targetUser->notifications()->get();  // Dùng get() để lấy tất cả các thông báo

        return response()->json([
            'success' => true,
            'user_id' => $targetUser->id,
            'notifications' => $notifications,  // Trả về tất cả thông báo
        ], 200);
    }



    /**
     * Đánh dấu một thông báo là đã đọc
     */
    public function markNotificationAsRead(Request $request, $notificationID)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationID);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Thông báo không tồn tại.'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Thông báo đã được đánh dấu là đã đọc.'
        ]);
    }

    /**
     * Lấy danh sách thông báo chưa đọc của user
     */
    public function getUnreadNotifications(Request $request, $userID)
    {
        // Lấy thông tin người dùng đã đăng nhập (user)
        $user = $request->user();

        // Kiểm tra xem người dùng có quyền truy cập thông báo của chính mình không
        if ($user->id != $userID) {
            return response()->json([
                'success' => false,
                'message' => 'Truy cập trái phép vào thông báo của người dùng.',
            ], 403); // Nếu không phải user hiện tại, trả về lỗi 403
        }

        // Lấy các thông báo chưa đọc của user
        $unreadNotifications = $user->unreadNotifications;

        // Trả về thông báo chưa đọc của user
        return response()->json([
            'success' => true,
            'notifications' => $unreadNotifications,
        ], 200);
    }


    /**
     * Xóa một thông báo
     */
    public function deleteNotification(Request $request, $notificationID)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationID);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Thông báo không tồn tại.'], 404);
        }

        $notification->delete();

        return response()->json(['success' => true, 'message' => 'Thông báo đã bị xóa.']);
    }



}
