<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiMessageController extends Controller
{
    /**
     * Hiển thị danh sách tin nhắn (API).
     */
    public function index()
    {
        $messages = Message::paginate(20);
        return response()->json($messages);
    }

    /**
     * Lấy 5 tin nhắn chưa đọc (API).
     */
    public function messageFive()
    {
        $messages = Message::whereNull('read_at')->limit(5)->get();
        return response()->json($messages);
    }

    /**
     * Lưu tin nhắn mới (API).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'    => 'string|required|min:2',
            'email'   => 'email|required',
            'message' => 'required|min:20|max:200',
            'subject' => 'string|required',
            'phone'   => 'required',
        ]);

        // Lưu tin nhắn
        $message = Message::create($request->all());

        // Chuẩn bị dữ liệu để gửi qua event
        $data = [
            'url'     => route('message.show', $message->id),
            'date'    => $message->created_at->format('F d, Y h:i A'),
            'name'    => $message->name,
            'email'   => $message->email,
            'phone'   => $message->phone,
            'message' => $message->message,
            'subject' => $message->subject,
            'photo'   => Auth::user()->photo,
        ];

        // Gửi sự kiện MessageSent
        event(new MessageSent($data));

        return response()->json(['success' => 'Tin nhắn đã được gửi thành công'], 201);
    }

    /**
     * Hiển thị thông tin chi tiết của tin nhắn (API).
     */
    public function show($id)
    {
        $message = Message::find($id);

        if (!$message) {
            return response()->json(['error' => 'Tin nhắn không tồn tại'], 404);
        }

        // Đánh dấu tin nhắn là đã đọc
        $message->read_at = \Carbon\Carbon::now();
        $message->save();

        return response()->json($message);
    }

    /**
     * Xóa tin nhắn (API).
     */
    public function destroy($id)
    {
        $message = Message::find($id);

        if (!$message) {
            return response()->json(['error' => 'Không tìm thấy tin nhắn'], 404);
        }

        $status = $message->delete();

        if ($status) {
            return response()->json(['success' => 'Đã xóa tin nhắn thành công']);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa tin nhắn'], 400);
        }
    }
}
