<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Contracts\View\View as ViewContract;

class NotificationController extends Controller
{
    public function index(){
        return view('backend.notification.index');
    }
    public function show(Request $request){
        $notification=Auth()->user()->notifications()->where('id',$request->id)->first();
        if($notification){
            $notification->markAsRead();
            return redirect($notification->data['actionURL']);
        }
    }
    public function delete($id){
        $notification=Notification::find($id);
        if($notification){
            $status=$notification->delete();
            if($status){
                request()->session()->flash('thành công','Thông báo đã được xóa thành công');
                return back();
            }
            else{
                request()->session()->flash('lỗi','Lỗi vui lòng thử lại');
                return back();
            }
        }
        else{
            request()->session()->flash('lỗi','Không tìm thấy thông báo');
            return back();
        }
    }
}
