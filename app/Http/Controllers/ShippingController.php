<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipping;
use App\Models\Coupon;
use Illuminate\Contracts\View\View as ViewContract;

class ShippingController extends Controller
{
    /**
     * Hiển thị danh sách phí vận chuyển (admin).
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shipping = Shipping::orderBy('id','DESC')->paginate(10);
        return view('backend.shipping.index')->with('shippings', $shipping);
    }

    /**
     * Trang thêm mới phí vận chuyển.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): ViewContract
    {
        return view('backend.shipping.create');
    }

    /**
     * Lưu phí vận chuyển mới.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'type'   => 'string|required',
            'price'  => 'nullable|numeric',
            'status' => 'required|in:active,inactive'
        ]);
        $data   = $request->all();
        $status = Shipping::create($data);

        if($status){
            request()->session()->flash('success','Thêm phí vận chuyển thành công');
        } else {
            request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại');
        }
        return redirect()->route('shipping.index');
    }

    /**
     * Hiển thị chi tiết phí vận chuyển (nếu cần).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Hiện chưa sử dụng
    }

    /**
     * Trang chỉnh sửa phí vận chuyển.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $shipping = Shipping::find($id);
        if(!$shipping){
            request()->session()->flash('error','Không tìm thấy phí vận chuyển');
        }
        return view('backend.shipping.edit')->with('shipping', $shipping);
    }

    /**
     * Cập nhật phí vận chuyển.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $shipping = Shipping::find($id);

        $this->validate($request, [
            'type'   => 'string|required',
            'price'  => 'nullable|numeric',
            'status' => 'required|in:active,inactive'
        ]);
        $data   = $request->all();
        $status = $shipping->fill($data)->save();

        if($status){
            request()->session()->flash('success','Cập nhật phí vận chuyển thành công');
        } else {
            request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại');
        }
        return redirect()->route('shipping.index');
    }

    /**
     * Xóa phí vận chuyển.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shipping = Shipping::find($id);
        if($shipping){
            $status = $shipping->delete();
            if($status){
                request()->session()->flash('success','Đã xóa phí vận chuyển');
            }
            else{
                request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại');
            }
            return redirect()->route('shipping.index');
        }
        else{
            request()->session()->flash('error','Không tìm thấy phí vận chuyển');
            return redirect()->back();
        }
    }
}
