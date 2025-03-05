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


class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('id', 'ASC')->paginate(10);
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
                    'message' => 'User not found.'
                ], 404);
            }

            return response()->json([
                'message' => 'User retrieved successfully.',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiuploadAvatar(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if (!$request->hasFile('photo')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng gửi file ảnh.',
                ], 400);
            }

            $file = $request->file('photo');
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Định dạng file không được hỗ trợ.',
                ], 400);
            }

            if ($file->getSize() > 2097152) { // 2MB
                return response()->json([
                    'success' => false,
                    'message' => 'Kích thước file vượt quá 2MB.',
                ], 400);
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = Str::slug($originalName);
            $fileName = $sanitizedName . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads/photos', $fileName, 'public');
            $fileUrl = asset('storage/' . $filePath);

            if ($user->photo) {
                $oldPath = str_replace(asset('storage/'), '', $user->photo);
                Storage::delete('public/' . $oldPath);
            }

            $user->photo = $fileUrl;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật ảnh đại diện thành công.',
                'url' => $fileUrl,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải ảnh.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function apigetAvatarByUserId($id)
    {
        try {
            // Tìm người dùng dựa trên ID
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => "Không tìm thấy người dùng với ID {$id}.",
                ], 404);
            }

            // Kiểm tra nếu người dùng không có ảnh
            if (!$user->photo) {
                return response()->json([
                    'success' => false,
                    'message' => "Người dùng với ID {$id} chưa có ảnh đại diện.",
                ], 404);
            }

            // Trả về URL ảnh đại diện
            return response()->json([
                'success' => true,
                'message' => 'Lấy ảnh đại diện thành công.',
                'photo_url' => $user->photo,
            ], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy ảnh đại diện.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function apiupdateAddress(Request $request, $id)
    {
        try {
            // Xác thực dữ liệu
            $request->validate([
                'address' => 'required|string|max:255',
            ], [
                'address.required' => 'Địa chỉ không được để trống.',
                'address.string' => 'Địa chỉ phải là chuỗi ký tự.',
                'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            ]);

            // Tìm user theo userID
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 404);
            }

            // Cập nhật địa chỉ
            $user->address = $request->address;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật địa chỉ thành công.',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật địa chỉ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy tất cả thông báo của user
     */
    public function getNotifications(Request $request, $userID)
    {
        // Kiểm tra xem người dùng có quyền truy cập thông báo của userID này hay không
        $user = $request->user();  // Người dùng hiện tại
    
        // Kiểm tra xem userID trong URL có phải là người dùng hiện tại không (hoặc có quyền xem thông báo của user khác)
        if ($user->id != $userID) {
            return response()->json([
                'success' => false,
                'message' => 'Truy cập trái phép vào thông báo của người dùng.',
            ], 403);
        }
    
        // Lấy thông tin user từ userID
        $targetUser = User::find($userID);
    
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng.',
            ], 404);
        }
    
        // Trả về thông báo và thông tin user (bao gồm ID)
        return response()->json([
            'success' => true,
            'user_id' => $targetUser->id,  // Trả về ID của user
            'notifications' => $targetUser->notifications,
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
