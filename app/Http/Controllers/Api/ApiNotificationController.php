<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class ApiNotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của user (cả đã đọc và chưa đọc)
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * Lấy danh sách thông báo chưa đọc của user
     */
    public function unread()
    {
        $notifications = Auth::user()->unreadNotifications()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'unread_count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }

    /**
     * Đánh dấu một thông báo là đã đọc
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json([
                'success' => true,
                'message' => 'Thông báo đã được đánh dấu là đã đọc.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy thông báo.'
        ], 404);
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Tất cả thông báo đã được đánh dấu là đã đọc.'
        ]);
    }

    /**
     * Xóa một thông báo
     */
    public function delete($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
            return response()->json([
                'success' => true,
                'message' => 'Thông báo đã được xóa thành công.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy thông báo.'
        ], 404);
    }

    /**
     * Xóa tất cả thông báo của user
     */
    public function deleteAll()
    {
        Auth::user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tất cả thông báo đã được xóa thành công.'
        ]);
    }
}
