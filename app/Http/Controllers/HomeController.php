<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\PostComment;
use App\Rules\MatchOldPassword;
use Hash;
use Illuminate\Contracts\View\View as ViewContract;

class HomeController extends Controller
{
    /**
     * Áp dụng middleware auth để đảm bảo chỉ user đã đăng nhập mới truy cập được
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Trang chủ sau khi đăng nhập của người dùng
     */
    public function index(){
        return view('user.index');
    }

    /**
     * Trang hồ sơ (profile) của người dùng
     */
    public function profile(){
        $profile = auth()->user();
        return view('user.users.profile')->with('profile', $profile);
    }

    /**
     * Cập nhật hồ sơ (profile) của người dùng
     */
    public function profileUpdate(Request $request, $id){
        $user   = User::findOrFail($id);
        $data   = $request->all();
        $status = $user->fill($data)->save();
        if($status){
            request()->session()->flash('success','Cập nhật hồ sơ thành công');
        }
        else{
            request()->session()->flash('error','Vui lòng thử lại!');
        }
        return redirect()->back();
    }

    /**
     * Danh sách đơn hàng của người dùng
     */
    public function orderIndex(){
        $orders = Order::orderBy('id','DESC')
                       ->where('user_id',auth()->user()->id)
                       ->paginate(10);
        return view('user.order.index')->with('orders',$orders);
    }

    /**
     * Xóa đơn hàng
     */
    public function userOrderDelete($id)
    {
        $order = Order::find($id);
        if($order){
           if($order->status == "process" || $order->status == 'delivered' || $order->status == 'cancel'){
                return redirect()->back()->with('error','Bạn không thể xóa đơn hàng này vào lúc này');
           }
           else{
                $status = $order->delete();
                if($status){
                    request()->session()->flash('success','Đã xóa đơn hàng thành công');
                }
                else{
                    request()->session()->flash('error','Không thể xóa đơn hàng, vui lòng thử lại');
                }
                return redirect()->route('user.order.index');
           }
        }
        else{
            request()->session()->flash('error','Không tìm thấy đơn hàng');
            return redirect()->back();
        }
    }

    /**
     * Xem chi tiết đơn hàng
     */
    public function orderShow($id)
    {
        $order = Order::find($id);
        return view('user.order.show')->with('order',$order);
    }

    /**
     * Danh sách đánh giá sản phẩm
     */
    public function productReviewIndex(){
        $reviews = ProductReview::getAllUserReview();
        return view('user.review.index')->with('reviews',$reviews);
    }

    /**
     * Trang chỉnh sửa đánh giá sản phẩm
     */
    public function productReviewEdit($id)
    {
        $review = ProductReview::find($id);
        return view('user.review.edit')->with('review',$review);
    }

    /**
     * Cập nhật đánh giá sản phẩm
     */
    public function productReviewUpdate(Request $request, $id)
    {
        $review = ProductReview::find($id);
        if($review){
            $data   = $request->all();
            $status = $review->fill($data)->update();
            if($status){
                request()->session()->flash('success','Cập nhật đánh giá thành công');
            }
            else{
                request()->session()->flash('error','Đã xảy ra lỗi! Vui lòng thử lại!');
            }
        }
        else{
            request()->session()->flash('error','Không tìm thấy đánh giá!');
        }

        return redirect()->route('user.productreview.index');
    }

    /**
     * Xóa đánh giá sản phẩm
     */
    public function productReviewDelete($id)
    {
        $review = ProductReview::find($id);
        $status = $review->delete();
        if($status){
            request()->session()->flash('success','Đã xóa đánh giá thành công');
        }
        else{
            request()->session()->flash('error','Đã xảy ra lỗi! Vui lòng thử lại');
        }
        return redirect()->route('user.productreview.index');
    }

    /**
     * Danh sách bình luận bài viết
     */
    public function userComment()
    {
        $comments = PostComment::getAllUserComments();
        return view('user.comment.index')->with('comments',$comments);
    }

    /**
     * Xóa bình luận bài viết
     */
    public function userCommentDelete($id){
        $comment = PostComment::find($id);
        if($comment){
            $status = $comment->delete();
            if($status){
                request()->session()->flash('success','Đã xóa bình luận bài viết');
            }
            else{
                request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại');
            }
            return back();
        }
        else{
            request()->session()->flash('error','Không tìm thấy bình luận bài viết');
            return redirect()->back();
        }
    }

    /**
     * Trang chỉnh sửa bình luận bài viết
     */
    public function userCommentEdit($id)
    {
        $comments = PostComment::find($id);
        if($comments){
            return view('user.comment.edit')->with('comment',$comments);
        }
        else{
            request()->session()->flash('error','Không tìm thấy bình luận');
            return redirect()->back();
        }
    }

    /**
     * Cập nhật bình luận bài viết
     */
    public function userCommentUpdate(Request $request, $id)
    {
        $comment = PostComment::find($id);
        if($comment){
            $data   = $request->all();
            $status = $comment->fill($data)->update();
            if($status){
                request()->session()->flash('success','Cập nhật bình luận thành công');
            }
            else{
                request()->session()->flash('error','Đã xảy ra lỗi! Vui lòng thử lại!');
            }
            return redirect()->route('user.post-comment.index');
        }
        else{
            request()->session()->flash('error','Không tìm thấy bình luận');
            return redirect()->back();
        }
    }

    /**
     * Trang đổi mật khẩu của người dùng
     */
    public function changePassword(){
        return view('user.layouts.userPasswordChange');
    }

    /**
     * Lưu mật khẩu mới
     */
    public function changPasswordStore(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password'     => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);
   
        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
   
        return redirect()->route('user')->with('success','Đổi mật khẩu thành công');
    }
}
