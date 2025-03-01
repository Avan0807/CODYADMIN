<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipping;
use Illuminate\Http\Request;

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
}
