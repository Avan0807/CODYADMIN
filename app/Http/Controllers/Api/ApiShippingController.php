<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipping;
use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\District;
use App\Models\ShippingLocation;
class ApiShippingController extends Controller
{
    public function index()
    {
        $shipping = Shipping::orderBy('id', 'DESC')->paginate(10);
        return response()->json($shipping);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'type'   => 'string|required',
            'price'  => 'nullable',
            'status' => 'required|in:active,inactive'
        ]);

        $data = $request->all();
        $shipping = Shipping::create($data);

        if ($shipping) {
            return response()->json(['success' => 'Thêm phí vận chuyển thành công'], 201);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi, vui lòng thử lại'], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $shipping = Shipping::find($id);

        if (!$shipping) {
            return response()->json(['error' => 'Phí vận chuyển không tồn tại'], 404);
        }

        $this->validate($request, [
            'type'   => 'string|required',
            'price'  => 'nullable',
            'status' => 'required|in:active,inactive'
        ]);

        $data = $request->all();
        $status = $shipping->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Cập nhật phí vận chuyển thành công']);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi, vui lòng thử lại'], 400);
        }
    }

    public function destroy($id)
    {
        $shipping = Shipping::find($id);

        if (!$shipping) {
            return response()->json(['error' => 'Phí vận chuyển không tồn tại'], 404);
        }

        $status = $shipping->delete();

        if ($status) {
            return response()->json(['success' => 'Đã xóa phí vận chuyển']);
        } else {
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa phí vận chuyển'], 400);
        }
    }

    // Lấy danh sách tỉnh/thành phố
    public function getProvinces()
    {
        $provinces = Province::orderBy('name')->get();
        return response()->json($provinces);
    }

    // Lấy danh sách quận/huyện theo tỉnh/thành phố
    public function getDistricts($provinceId)
    {
        $districts = District::where('province_id', $provinceId)
                        ->orderBy('name')
                        ->get();
        return response()->json($districts);
    }

    // Tính phí vận chuyển
    public function calculateShipping(Request $request)
    {
        $validatedData = $request->validate([
            'shipping_id' => 'required|exists:shippings,id',
            'from_province_id' => 'required|exists:provinces,id',
            'to_province_id' => 'required|exists:provinces,id',
            'weight' => 'numeric|nullable',
        ]);

        $shipping = Shipping::find($validatedData['shipping_id']);
        $weight = $validatedData['weight'] ?? 1;

        // Tìm trong bảng shipping_locations
        $location = ShippingLocation::where('shipping_id', $validatedData['shipping_id'])
                    ->where('from_province_id', $validatedData['from_province_id'])
                    ->where('to_province_id', $validatedData['to_province_id'])
                    ->first();

        if ($location) {
            $shippingCost = $location->price + ($weight > 1 ? ($weight - 1) * $location->weight_price : 0);
        } else {
            // Tính toán dựa trên vùng và giá cơ bản
            $fromProvince = Province::find($validatedData['from_province_id']);
            $toProvince = Province::find($validatedData['to_province_id']);

            $basePrice = $shipping->price;

            if ($validatedData['from_province_id'] == $validatedData['to_province_id']) {
                // Nội tỉnh - giảm 20%
                $shippingCost = $basePrice * 0.8;
            } elseif ($fromProvince->region_id == $toProvince->region_id) {
                // Cùng vùng - giữ nguyên giá
                $shippingCost = $basePrice;
            } else {
                // Khác vùng - tăng 30%
                $shippingCost = $basePrice * 1.3;
            }
        }

        return response()->json([
            'shipping_cost' => $shippingCost,
            'formatted_cost' => number_format($shippingCost, 0, ',', '.') . 'đ'
        ]);
    }

}
