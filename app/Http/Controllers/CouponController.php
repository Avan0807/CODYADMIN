<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;


class CouponController extends Controller
{
    /**
     * Hiển thị danh sách coupon.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupon = Coupon::orderBy('id','DESC')->paginate(10);
        return view('backend.coupon.index')->with('coupons', $coupon);
    }

    /**
     * Trang thêm mới coupon.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function create(): ViewContract
    {
        return view('backend.coupon.create');
    }

    /**
     * Lưu coupon mới vào CSDL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code'   => 'string|required',
            'type'   => 'required|in:fixed,percent',
            'value'  => 'required|numeric',
            'status' => 'required|in:active,inactive'
        ]);

        $data   = $request->all();
        $status = Coupon::create($data);

        if($status){
            request()->session()->flash('success','Thêm mã giảm giá thành công');
        } else {
            request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại!');
        }

        return redirect()->route('coupon.index');
    }

    /**
     * Trang chi tiết coupon (nếu cần).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Hiện tại không sử dụng
    }

    /**
     * Trang chỉnh sửa coupon.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coupon = Coupon::find($id);
        if($coupon){
            return view('backend.coupon.edit')->with('coupon', $coupon);
        } else {
            return view('backend.coupon.index')->with('error','Không tìm thấy mã giảm giá');
        }
    }

    /**
     * Cập nhật coupon trong CSDL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);

        $this->validate($request, [
            'code'   => 'string|required',
            'type'   => 'required|in:fixed,percent',
            'value'  => 'required|numeric',
            'status' => 'required|in:active,inactive'
        ]);

        $data   = $request->all();
        $status = $coupon->fill($data)->save();

        if($status){
            request()->session()->flash('success','Cập nhật mã giảm giá thành công');
        } else {
            request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại!');
        }

        return redirect()->route('coupon.index');
    }

    /**
     * Xóa coupon khỏi CSDL.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $coupon = Coupon::find($id);
        if($coupon){
            $status = $coupon->delete();
            if($status){
                request()->session()->flash('success','Đã xóa mã giảm giá');
            } else {
                request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại!');
            }
            return redirect()->route('coupon.index');
        } else {
            request()->session()->flash('error','Không tìm thấy mã giảm giá');
            return redirect()->back();
        }
    }

    /**
     * Áp dụng coupon trong giỏ hàng (frontend).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function couponStore(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->first();
    
        if (!$coupon) {
            request()->session()->flash('error', 'Mã giảm giá không hợp lệ, vui lòng thử lại!');
            return redirect()->back();
        }
    
        $total_price = Cart::where('user_id', auth()->user()->id)
                           ->where('order_id', null)
                           ->sum('price');
    
        // Kiểm tra giá trị giảm giá so với tổng giỏ hàng
        if ($coupon->discount($total_price) >= $total_price) {
            if ($total_price <= 1000) {
                // Nếu tổng giỏ hàng nhỏ hơn hoặc bằng 1.000đ, không áp dụng mã giảm giá
                request()->session()->flash('error', 'Tổng giỏ hàng không đủ điều kiện để áp dụng mã giảm giá.');
                return redirect()->back();
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
    
        request()->session()->flash('success', 'Áp dụng mã giảm giá thành công');
        return redirect()->back();
    }
    
    
}
