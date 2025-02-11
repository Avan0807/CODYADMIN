<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Helper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ApiOrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng (API).
     */
    public function index(Request $request)
    {
        $orders = Order::orderBy('id', 'DESC')->paginate(10);
        return response()->json($orders);
    }

    /**
     * Tạo đơn hàng mới.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'string|required',
            'last_name'  => 'string|required',
            'address1'   => 'string|required',
            'address2'   => 'string|nullable',
            'coupon'     => 'nullable',
            'phone'      => 'required',
            'post_code'  => 'string|nullable',
            'email'      => 'string|required',
        ]);

        if (empty(Cart::where('user_id', auth()->user()->id)->where('order_id', null)->first())) {
            return response()->json(['error' => 'Giỏ hàng đang trống!'], 400);
        }

        // Create order
        $order_data = $request->all();
        $order_data['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $order_data['user_id'] = $request->user()->id;
        $order_data['shipping_id'] = $request->shipping;
        $shipping = Shipping::find($order_data['shipping_id']);
        $order_data['sub_total'] = Helper::totalCartPrice();
        $order_data['quantity'] = Helper::cartCount();
        $order_data['coupon'] = session('coupon')['value'] ?? 0;
        $order_data['total_amount'] = $order_data['sub_total'] + ($shipping ? $shipping->price : 0) - ($order_data['coupon'] ?? 0);
        $order_data['payment_method'] = request('payment_method');
        $order_data['payment_status'] = request('payment_method') === 'paypal' ? 'paid' : 'Unpaid';

        $order = new Order();
        $order->fill($order_data);
        $order->save();

        // Update cart and send notification
        Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);

        return response()->json(['success' => 'Đơn hàng của bạn đã được tạo. Cảm ơn bạn đã mua sắm!'], 201);
    }

    /**
     * Hiển thị chi tiết đơn hàng (API).
     */
    public function show($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tìm thấy'], 404);
        }
        return response()->json($order);
    }

    /**
     * Cập nhật đơn hàng (API).
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tìm thấy'], 404);
        }

        $this->validate($request, [
            'status' => 'required|in:new,process,delivered,cancel'
        ]);
        $data = $request->all();

        // Nếu đơn hàng được chuyển sang trạng thái 'delivered'
        // => Trừ stock của các sản phẩm liên quan
        if ($request->status == 'delivered') {
            foreach ($order->cart as $cart) {
                $product = $cart->product;
                $product->stock -= $cart->quantity;
                $product->save();
            }
        }

        $status = $order->fill($data)->save();
        if ($status) {
            return response()->json(['success' => 'Cập nhật đơn hàng thành công']);
        } else {
            return response()->json(['error' => 'Không thể cập nhật đơn hàng'], 400);
        }
    }

    /**
     * Xóa đơn hàng (API).
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tìm thấy'], 404);
        }

        $status = $order->delete();
        if ($status) {
            return response()->json(['success' => 'Đơn hàng đã bị xóa thành công']);
        } else {
            return response()->json(['error' => 'Không thể xóa đơn hàng'], 400);
        }
    }

    /**
     * Kiểm tra tình trạng đơn hàng (API).
     */
    public function productTrackOrder(Request $request)
    {
        $order = Order::where('user_id', auth()->user()->id)
            ->where('order_number', $request->order_number)
            ->first();

        if ($order) {
            if ($order->status == "new") {
                return response()->json(['success' => 'Đơn hàng của bạn đã được đặt.']);
            } elseif ($order->status == "process") {
                return response()->json(['success' => 'Đơn hàng của bạn đang được xử lý.']);
            } elseif ($order->status == "delivered") {
                return response()->json(['success' => 'Đơn hàng của bạn đã được giao. Cảm ơn bạn đã mua sắm!']);
            } else {
                return response()->json(['error' => 'Rất tiếc, đơn hàng của bạn đã bị hủy.'], 400);
            }
        } else {
            return response()->json(['error' => 'Mã đơn hàng không hợp lệ. Vui lòng thử lại!'], 400);
        }
    }

    /**
     * Xuất hóa đơn PDF (API).
     */
    public function pdf(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $file_name = $order->order_number . '-' . $order->first_name . '.pdf';

        // Tạo và xuất file PDF
        $pdf = PDF::loadView('backend.order.pdf', compact('order'));

        return $pdf->download($file_name);
    }

    /**
     * Lấy dữ liệu thống kê thu nhập theo tháng (API).
     */
    public function incomeChart(Request $request)
    {
        $year = \Carbon\Carbon::now()->year;
        $items = Order::with(['cart_info'])
            ->whereYear('created_at', $year)
            ->where('status', 'delivered')
            ->get()
            ->groupBy(function ($d) {
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });

        $result = [];
        foreach ($items as $month => $item_collections) {
            foreach ($item_collections as $item) {
                $amount = $item->cart_info->sum('amount');
                $m = intval($month);
                isset($result[$m]) ? $result[$m] += $amount : $result[$m] = $amount;
            }
        }

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $data[$monthName] = (!empty($result[$i]))
                ? number_format((float)($result[$i]), 2, '.', '')
                : 0.0;
        }

        return response()->json($data);
    }
}
