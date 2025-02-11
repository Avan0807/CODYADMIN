<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiNotificationController extends Controller
{
    /**
     * Hiển thị danh sách thông báo (API).
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(10);
        return response()->json($notifications);
    }

    /**
     * Hiển thị và đánh dấu thông báo đã đọc (API).
     */
    public function show(Request $request)
    {
        $notification = Auth::user()->notifications()->where('id', $request->id)->first();

        if (!$notification) {
            return response()->json(['error' => 'Thông báo không tìm thấy'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => 'Đánh dấu thông báo là đã đọc',
            'actionURL' => $notification->data['actionURL']
        ]);
    }

    /**
     * Xóa thông báo (API).
     */
    public function delete($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['error' => 'Thông báo không tìm thấy'], 404);
        }

        $status = $notification->delete();

        if ($status) {
            return response()->json(['success' => 'Thông báo đã được xóa thành công']);
        } else {
            return response()->json(['error' => 'Lỗi khi xóa thông báo'], 400);
        }
    }
}
