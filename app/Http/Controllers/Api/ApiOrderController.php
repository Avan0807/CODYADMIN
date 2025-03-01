<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Helper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Doctor;
use App\Models\AffiliateLink;

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
        // ✅ Kiểm tra nếu user chưa đăng nhập
        if (!Auth::check()) {
            return response()->json(['error' => 'Bạn cần đăng nhập để đặt hàng.'], 401);
        }

        // ✅ Kiểm tra giỏ hàng có sản phẩm không
        $cartItems = Cart::where('user_id', Auth::id())->whereNull('order_id')->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Giỏ hàng của bạn đang trống!'], 400);
        }

        // ✅ Lấy sản phẩm đầu tiên trong giỏ hàng
        $firstProduct = $cartItems->first();

        // ✅ Kiểm tra dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'address1'   => 'required|string|max:255',
            'address2'   => 'nullable|string|max:255',
            'phone'      => 'required|string|max:15',
            'email'      => 'required|email|max:255',
            'post_code'  => 'nullable|string|max:20',
            'shipping_id' => 'nullable|exists:shippings,id',
            'coupon'     => 'nullable|string',
            'payment_method' => 'required|string|in:cod,paypal,bank_transfer',
            'country'    => 'nullable|string|max:255' // ✅ Bổ sung country vào validate
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dữ liệu không hợp lệ.',
                'details' => $validator->errors()
            ], 400);
        }

        // ✅ Tính tổng giá trị đơn hàng
        $subTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // ✅ Lấy phí vận chuyển (nếu có)
        $shipping = Shipping::find($request->shipping_id);
        $shippingCost = $shipping ? $shipping->price : 0;

        // ✅ Áp dụng mã giảm giá (nếu có)
        $couponValue = session('coupon')['value'] ?? 0;

        // ✅ Tính tổng tiền đơn hàng
        $totalAmount = $subTotal + $shippingCost - $couponValue;

        // ✅ Tạo đơn hàng mới
        $order = new Order();
        $order->user_id = Auth::id();
        $order->product_id = $firstProduct->product_id;
        $order->order_number = 'ORD-' . strtoupper(Str::random(10));
        $order->sub_total = $subTotal;
        $order->total_amount = $totalAmount;
        $order->quantity = $cartItems->sum('quantity');
        $order->shipping_id = $request->shipping ?? null; // Thêm fallback nếu không có giá trị
        $order->coupon = $couponValue;
        $order->payment_method = $request->payment_method;
        $order->payment_status = $request->payment_method === 'paypal' ? 'paid' : 'unpaid';
        $order->status = "new";

        // ✅ Thông tin khách hàng
        $order->first_name = $request->first_name;
        $order->last_name = $request->last_name;
        $order->email = $request->email;
        $order->phone = $request->phone;
        $order->post_code = $request->post_code;
        $order->address1 = $request->address1;
        $order->address2 = $request->address2;
        $order->country = $request->country ?? 'Vietnam'; // ✅ Thêm country mặc định nếu không có

        // 🔥 Lưu đơn hàng vào database
        $order->save();

        // ✅ Gán order_id vào giỏ hàng
        Cart::where('user_id', Auth::id())->whereNull('order_id')->update(['order_id' => $order->id]);

        return response()->json([
            'message' => 'Đơn hàng của bạn đã được tạo thành công!',
            'order' => $order
        ], 201);
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

    public function storeDoctor(Request $request) {
        if (!Auth::check()) {
            return response()->json(['error' => 'Bạn cần đăng nhập để đặt hàng.'], 401);
        }

        // ✅ Kiểm tra sản phẩm có tồn tại không
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
        }

        // ✅ Tạo đơn hàng
        $order = new Order();
        $order->user_id = Auth::id();
        $order->product_id = $product->id;
        $order->quantity = $request->quantity;

        // ✅ Lấy giá sản phẩm từ CSDL
        $order->sub_total = $product->price * $order->quantity;
        $order->total_amount = $order->sub_total;

        // ✅ Tạo mã đơn hàng ngẫu nhiên
        $order->order_number = 'ORD-' . strtoupper(Str::random(10));

        // ✅ Lấy % hoa hồng từ `affiliate_links`
        $commissionPercentage = 0; // Mặc định là 0%

        if ($request->has('doctor_id') && !empty($request->doctor_id)) {
            $affiliateData = AffiliateLink::where([
                ['doctor_id', $request->doctor_id],
                ['product_id', $product->id]
            ])->first();

            if ($affiliateData) {
                $commissionPercentage = $affiliateData->commission_percentage;
            }

            // ✅ Gán bác sĩ và tính hoa hồng theo Affiliate link
            $order->doctor_id = $request->doctor_id;
            $order->commission = $order->sub_total * ($commissionPercentage / 100);
        } else {
            $order->commission = 0;
        }

        // ✅ Đơn hàng mặc định có trạng thái "new"
        $order->status = "new";
        $order->payment_status = "unpaid";

        // ✅ Thông tin khách hàng
        $order->first_name = $request->first_name ?? 'Unknown';
        $order->last_name = $request->last_name ?? 'Unknown';
        $order->email = $request->email ?? 'unknown@gmail.com';
        $order->phone = $request->phone ?? '0000000000';
        $order->country = $request->country ?? 'Vietnam';
        $order->address1 = $request->address1 ?? 'Default Address';
        $order->address2 = $request->address2 ?? null;

        // 🔥 Lưu vào database
        $order->save();

        return response()->json([
            'message' => 'Đơn hàng được tạo thành công!',
            'order' => $order
        ], 201);
    }

    public function updateOrderStatus(Request $request, $order_id) {
        $order = Order::find($order_id);

        if (!$order) {
            return response()->json(['error' => 'Không tìm thấy đơn hàng!'], 404);
        }

        // ✅ Chỉ cho phép cập nhật trạng thái hợp lệ
        $validStatuses = ['new', 'process', 'delivered', 'cancel'];
        if (!in_array($request->status, $validStatuses)) {
            return response()->json(['error' => 'Trạng thái không hợp lệ.'], 400);
        }

        // ✅ Nếu đơn hàng chuyển sang "delivered", cộng commission vào tổng của bác sĩ
        if ($request->status == "delivered" && $order->doctor_id) {
            $doctor = Doctor::find($order->doctor_id);
            if ($doctor) {
                // 🔥 Kiểm tra để tránh cộng dồn nhiều lần
                if ($order->status !== "delivered") {
                    $doctor->total_commission += $order->commission;
                    $doctor->save();
                }
            }
        }

        // ✅ Cập nhật trạng thái đơn hàng
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Trạng thái đơn hàng đã được cập nhật!',
            'order' => $order
        ], 200);
    }


}
