<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        return view('backend.notification.index');
    }

    public function show(Request $request)
    {
        $notification = Auth::user()->notifications()->where('id', $request->id)->first();

        if ($notification) {
            $notification->markAsRead();

            if (isset($notification->data['actionURL'])) {
                return redirect($notification->data['actionURL']);
            }

            return back()->with('error', 'Không tìm thấy đường dẫn thông báo.');
        }

        return back()->with('error', 'Không tìm thấy thông báo.');
    }

    public function delete($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if ($notification) {
            if ($notification->delete()) {
                return back()->with('success', 'Thông báo đã được xóa thành công');
            } else {
                return back()->with('error', 'Lỗi vui lòng thử lại');
            }
        }

        return back()->with('error', 'Không tìm thấy thông báo.');
    }
}
