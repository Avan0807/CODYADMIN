<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipping;
use App\Models\Province;
use App\Models\District;
use App\Models\ShippingLocation;
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
        $shipping = Shipping::orderBy('id','DESC')->get();
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

    // Quản lý tỉnh/thành phố
    public function provinces()
    {
        $provinces = Province::orderBy('name')->paginate(20);
        return view('backend.shipping.provinces', compact('provinces'));
    }

    // Thêm tỉnh/thành phố mới
    public function createProvince()
    {
        return view('backend.shipping.province-create');
    }

    // Lưu tỉnh/thành phố mới
    public function storeProvince(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'region_id' => 'required|integer|between:1,3',
        ]);

        $data = $request->all();
        $status = Province::create($data);

        if($status){
            request()->session()->flash('success','Thêm tỉnh/thành phố thành công');
        } else {
            request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại');
        }
        return redirect()->route('shipping.provinces');
    }

    // Sửa tỉnh/thành phố
    public function editProvince($id)
    {
        $province = Province::find($id);
        if(!$province){
            request()->session()->flash('error','Không tìm thấy tỉnh/thành phố');
            return redirect()->back();
        }
        return view('backend.shipping.province-edit', compact('province'));
    }

    // Cập nhật tỉnh/thành phố
    public function updateProvince(Request $request, $id)
    {
        $province = Province::find($id);
        if(!$province){
            request()->session()->flash('error','Không tìm thấy tỉnh/thành phố');
            return redirect()->back();
        }

        $this->validate($request, [
            'name' => 'required|string',
            'region_id' => 'required|integer|between:1,3',
        ]);

        $data = $request->all();
        $status = $province->update($data);

        if($status){
            request()->session()->flash('success','Cập nhật tỉnh/thành phố thành công');
        } else {
            request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại');
        }
        return redirect()->route('shipping.provinces');
    }

    // Xóa tỉnh/thành phố
    public function destroyProvince($id)
    {
        $province = Province::find($id);
        if(!$province){
            request()->session()->flash('error','Không tìm thấy tỉnh/thành phố');
            return redirect()->back();
        }

        $status = $province->delete();
        if($status){
            request()->session()->flash('success','Đã xóa tỉnh/thành phố');
        } else {
            request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại');
        }
        return redirect()->route('shipping.provinces');
    }

    // Quản lý shipping_locations
    public function locations()
    {
        $locations = ShippingLocation::with(['shipping', 'fromProvince', 'toProvince'])->paginate(15);
        return view('backend.shipping.locations', compact('locations'));
    }

    // Thêm quy tắc tính phí vận chuyển mới
    public function createLocation()
    {
        $shippings = Shipping::where('status', 'active')->get();
        $provinces = Province::orderBy('name')->get();
        return view('backend.shipping.location-create', compact('shippings', 'provinces'));
    }

    // Lưu quy tắc tính phí vận chuyển mới
    public function storeLocation(Request $request)
    {
        $this->validate($request, [
            'shipping_id' => 'required|exists:shippings,id',
            'from_province_id' => 'required|exists:provinces,id',
            'to_province_id' => 'required|exists:provinces,id',
            'price' => 'required|numeric|min:0',
            'weight_price' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $status = ShippingLocation::create($data);

        if($status){
            request()->session()->flash('success','Thêm quy tắc tính phí vận chuyển thành công');
        } else {
            request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại');
        }
        return redirect()->route('shipping.locations');
    }


}
