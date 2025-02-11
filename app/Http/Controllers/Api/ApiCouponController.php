<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Cart;
use Illuminate\Http\Request;

class ApiCouponController extends Controller
{
    /**
     * Danh sách coupon (API).
     */
    public function index()
    {
        $coupons = Coupon::orderBy('id', 'DESC')->paginate(10);
        return response()->json($coupons);
    }

    /**
     * Tạo coupon mới (API).
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code'   => 'string|required',
            'type'   => 'required|in:fixed,percent',
            'value'  => 'required',
            'status' => 'required|in:active,inactive'
        ]);

        $data   = $request->all();
        $coupon = Coupon::create($data);

        if ($coupon) {
            return response()->json(['success' => 'Thêm mã giảm giá thành công'], 201);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi, vui lòng thử lại!'], 400);
        }
    }

    /**
     * Cập nhật coupon (API).
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['error' => 'Mã giảm giá không tìm thấy'], 404);
        }

        $this->validate($request, [
            'code'   => 'string|required',
            'type'   => 'required|in:fixed,percent',
            'value'  => 'required',
            'status' => 'required|in:active,inactive'
        ]);

        $data   = $request->all();
        $status = $coupon->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Cập nhật mã giảm giá thành công']);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi, vui lòng thử lại!'], 400);
        }
    }

    /**
     * Xóa coupon (API).
     */
    public function destroy($id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json(['error' => 'Mã giảm giá không tìm thấy'], 404);
        }

        $status = $coupon->delete();

        if ($status) {
            return response()->json(['success' => 'Đã xóa mã giảm giá']);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa mã giảm giá'], 400);
        }
    }

    /**
     * Áp dụng coupon trong giỏ hàng (API).
     */
    public function couponStore(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->first();
    
        if (!$coupon) {
            return response()->json(['error' => 'Mã giảm giá không hợp lệ'], 400);
        }
    
        $total_price = Cart::where('user_id', auth()->user()->id)
                           ->where('order_id', null)
                           ->sum('price');
    
        // Kiểm tra giá trị giảm giá so với tổng giỏ hàng
        if ($coupon->discount($total_price) >= $total_price) {
            if ($total_price <= 1000) {
                // Nếu tổng giỏ hàng nhỏ hơn hoặc bằng 1.000đ, không áp dụng mã giảm giá
                return response()->json(['error' => 'Tổng giỏ hàng không đủ điều kiện để áp dụng mã giảm giá'], 400);
            }
    
            // Nếu giá trị giảm lớn hơn hoặc bằng tổng giá trị, giảm để còn lại 1.000đ
            $discount_value = $total_price - 1000;
        } else {
            // Áp dụng giảm giá bình thường
            $discount_value = $coupon->discount($total_price);
        }
    
        session()->put('coupon', [
            'id'    => $coupon->id,
            'code'  => $coupon->code,
            'value' => $discount_value,
        ]);
    
        return response()->json(['success' => 'Áp dụng mã giảm giá thành công']);
    }
}
