<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Notifications\StatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ApiProductReviewController extends Controller
{
    /**
     * Hiển thị danh sách đánh giá sản phẩm (API).
     */
    public function index()
    {
        $reviews = ProductReview::orderBy('id', 'DESC')->paginate(10);
        return response()->json($reviews);
    }

    /**
     * Tạo mới đánh giá sản phẩm (API).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'rate' => 'required|min:1'
        ]);

        $product_info = Product::where('slug', $request->slug)->first();

        if (!$product_info) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        }

        $data = $request->all();
        $data['product_id'] = $product_info->id;
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'active';

        $status = ProductReview::create($data);

        // Gửi thông báo cho quản trị viên
        $user = User::where('role', 'admin')->get();
        $details = [
            'title' => 'Đánh giá sản phẩm mới!',
            'message' => 'Người dùng ' . $request->user()->name . ' đã đánh giá sản phẩm: ' . $product_info->title,
            'actionURL' => route('product-detail', $product_info->slug),
            'fas' => 'fa-star'
        ];

        Notification::send($user, new StatusNotification($details));

        if ($status) {
            return response()->json(['success' => 'Cảm ơn bạn đã đánh giá trung thực!'], 201);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra! Vui lòng thử lại!!'], 400);
        }
    }


    /**
     * Cập nhật đánh giá sản phẩm (API).
     */
    public function update(Request $request, $id)
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json(['error' => 'Đánh giá không tồn tại'], 404);
        }

        $this->validate($request, [
            'rate' => 'required|min:1'
        ]);

        $data = $request->all();
        $status = $review->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Đánh giá đã được cập nhật']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra! Vui lòng thử lại!!'], 400);
        }
    }

    /**
     * Xóa đánh giá sản phẩm (API).
     */
    public function destroy($id)
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json(['error' => 'Đánh giá không tồn tại'], 404);
        }

        $status = $review->delete();

        if ($status) {
            return response()->json(['success' => 'Đánh giá đã được xóa']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi xóa đánh giá'], 400);
        }
    }
}
