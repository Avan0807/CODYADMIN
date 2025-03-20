<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\User;
use App\Models\AffiliateOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Helper;
Use Notification;
use Illuminate\Support\Str;
use App\Notifications\StatusNotification;
use Illuminate\Http\RedirectResponse;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Notifications\MedicineReminderNotification;
use App\Models\MedicineLog;
use Illuminate\Support\Facades\Log;


class OrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng (phía admin).
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::orderBy('id', 'DESC')->get();
        return view('backend.order.index')->with('orders', $orders);
    }

    /**
     * Tạo đơn hàng mới (trang create nếu có).
     */
    public function create()
    {
        //
    }

    /**
     * Lưu đơn hàng mới.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'first_name' => 'string|required',
            'last_name'  => 'string|required',
            'address1'   => 'string|required',
            'address2'   => 'string|nullable',
            'coupon'     => 'nullable|numeric',
            'phone'      => 'numeric|required',
            'post_code'  => 'string|nullable',
            'email'      => 'string|required',
        ]);

        // Kiểm tra giỏ hàng có trống hay không
        if (empty(Cart::where('user_id', auth()->user()->id)->where('order_id', null)->first())) {
            request()->session()->flash('error', 'Giỏ hàng đang trống!');
            return redirect()->back();
        }

        // Tạo đơn hàng
        $order        = new Order();
        $order_data   = $request->all();
        $order_data['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $order_data['user_id']      = $request->user()->id;
        $order_data['shipping_id']  = $request->shipping;

        // Lấy giá ship
        $shipping = Shipping::where('id', $order_data['shipping_id'])->pluck('price');

        // Tính sub_total, quantity, coupon,...
        $order_data['sub_total'] = Helper::totalCartPrice();
        $order_data['quantity']  = Helper::cartCount();

        // Nếu có áp dụng mã giảm giá
        if (session('coupon')) {
            $order_data['coupon'] = session('coupon')['value'];
        }

        // Tính toán total_amount
        if ($request->shipping) {
            if (session('coupon')) {
                $order_data['total_amount'] = Helper::totalCartPrice() + $shipping[0] - session('coupon')['value'];
            } else {
                $order_data['total_amount'] = Helper::totalCartPrice() + $shipping[0];
            }
        } else {
            if (session('coupon')) {
                $order_data['total_amount'] = Helper::totalCartPrice() - session('coupon')['value'];
            } else {
                $order_data['total_amount'] = Helper::totalCartPrice();
            }
        }

        // Xử lý phương thức thanh toán
        if (request('payment_method') == 'paypal') {
            $order_data['payment_method'] = 'paypal';
            $order_data['payment_status'] = 'paid';
        } elseif (request('payment_method') == 'cardpay') {
            $order_data['payment_method'] = 'cardpay';
            $order_data['payment_status'] = 'paid';
        } else {
            $order_data['payment_method'] = 'cod';
            $order_data['payment_status'] = 'Unpaid';
        }

        // Lưu thông tin đơn hàng
        $order->fill($order_data);
        $status = $order->save();

        if ($order) {
            // Gửi thông báo cho admin
            $users   = User::where('role', 'admin')->first();
            $details = [
                'title'     => 'Đơn hàng mới',
                'actionURL' => route('order.show', $order->id),
                'fas'       => 'fa-file-alt'
            ];
            Notification::send($users, new StatusNotification($details));
        }

        // Nếu thanh toán paypal, chuyển hướng sang trang thanh toán
        if (request('payment_method') == 'paypal') {
            return redirect()->route('payment')->with(['id' => $order->id]);
        } else {
            // Xóa session giỏ hàng, coupon nếu có
            session()->forget('cart');
            session()->forget('coupon');
        }

        // Cập nhật order_id cho giỏ hàng
        Cart::where('user_id', auth()->user()->id)
            ->where('order_id', null)
            ->update(['order_id' => $order->id]);

        request()->session()->flash('success', 'Đơn hàng của bạn đã được tạo. Cảm ơn bạn đã mua sắm!');
        return redirect()->route('home');
    }

    /**
     * Hiển thị chi tiết đơn hàng (phía admin).
     */
    public function show($id)
    {
        $order = Order::find($id);
        if (!$order) {
            // Ở đây có thể xử lý lỗi hoặc redirect nếu không tìm thấy đơn hàng
        }
        return view('backend.order.show')->with('order', $order);
    }

    /**
     * Form chỉnh sửa đơn hàng (phía admin).
     */
    public function edit($id)
    {
        $order = Order::find($id);
        return view('backend.order.edit')->with('order', $order);
    }

    /**
     * Cập nhật đơn hàng (phía admin).
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        $this->validate($request, [
            'status' => 'required|in:new,process,delivered,cancel'
        ]);
        $data = $request->all();

        // Nếu đơn hàng được chuyển sang trạng thái 'delivered'
        // => Trừ stock của các sản phẩm liên quan và gửi thông báo cho người dùng
        if ($request->status == 'delivered') {
            // Trừ stock của các sản phẩm trong giỏ hàng
            foreach ($order->cart as $cart) {
                $product = $cart->product;
                $product->stock -= $cart->quantity;
                $product->save();

                // Tìm thông tin nhắc nhở trong bảng medicine_logs
                $medicineLog = MedicineLog::where('product_id', $cart->product_id)
                                          ->first();

                // Nếu có thông tin nhắc nhở cho sản phẩm này
                if ($medicineLog) {
                    // Lấy tên sản phẩm từ bảng products
                    $productName = \App\Models\Product::find($cart->product_id)->title;

                    // Chuẩn bị thông tin chi tiết cho thông báo
                    $notificationDetails = [
                        'message' => $medicineLog->notification_message,  // Thông báo từ bảng medicine_logs
                        'product_name' => $productName,  // Tên sản phẩm từ bảng products
                    ];

                    // Gửi thông báo nhắc lịch uống thuốc cho người dùng
                    Notification::send($order->user, new MedicineReminderNotification($notificationDetails));
                }
            }
        }

        // Cập nhật trạng thái đơn hàng
        $status = $order->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Cập nhật đơn hàng thành công');
        } else {
            request()->session()->flash('error', 'Không thể cập nhật đơn hàng');
        }

        // Quay lại trang danh sách đơn hàng
        return redirect()->route('order.index');
    }



    /**
     * Xóa đơn hàng (phía admin).
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if ($order) {
            $status = $order->delete();
            if ($status) {
                request()->session()->flash('success', 'Đã xóa đơn hàng thành công');
            } else {
                request()->session()->flash('error', 'Không thể xóa đơn hàng');
            }
            return redirect()->route('order.index');
        } else {
            request()->session()->flash('error', 'Không tìm thấy đơn hàng');
            return redirect()->back();
        }
    }

    /**
     * Trang theo dõi đơn hàng (phía frontend).
     */
    public function orderTrack()
    {
        return view('frontend.pages.order-track');
    }

    /**
     * Kiểm tra tình trạng đơn hàng (phía frontend).
     */
    public function productTrackOrder(Request $request)
    {
        $order = Order::where('user_id', auth()->user()->id)
            ->where('order_number', $request->order_number)
            ->first();

        if ($order) {
            if ($order->status == "new") {
                request()->session()->flash('success', 'Đơn hàng của bạn đã được đặt.');
                return redirect()->route('home');
            } elseif ($order->status == "process") {
                request()->session()->flash('success', 'Đơn hàng của bạn đang được xử lý.');
                return redirect()->route('home');
            } elseif ($order->status == "delivered") {
                request()->session()->flash('success', 'Đơn hàng của bạn đã được giao. Cảm ơn bạn đã mua sắm!');
                return redirect()->route('home');
            } else {
                request()->session()->flash('error', 'Rất tiếc, đơn hàng của bạn đã bị hủy.');
                return redirect()->route('home');
            }
        } else {
            request()->session()->flash('error', 'Mã đơn hàng không hợp lệ. Vui lòng thử lại!');
            return back();
        }
    }

    /**
     * Xuất hóa đơn PDF (phía admin).
     */
    public function pdf(Request $request, $id)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true); // Bật isRemoteEnabled
        // Lấy dữ liệu đơn hàng
        $order = Order::findOrFail($id);

        // Tạo tên file
        $file_name = $order->order_number . '-' . $order->first_name . '.pdf';

        // Tải view và xuất PDF
        $pdf = PDF::loadView('backend.order.pdf', compact('order'));

        // Xuất file PDF
        return $pdf->download($file_name);
    }

    /**
     * Lấy dữ liệu thống kê thu nhập theo tháng (phía admin).
     */
    public function incomeChart(Request $request)
    {
        $year = \Carbon\Carbon::now()->year;

        $items = Order::whereYear('created_at', $year)
            ->where('status', 'delivered') // Chỉ lấy đơn hàng đã giao
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total_income')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Khởi tạo mảng kết quả với 12 tháng mặc định là 0
        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $result[$monthName] = 0;
        }

        // Gán dữ liệu vào mảng kết quả
        foreach ($items as $item) {
            $monthName = date('F', mktime(0, 0, 0, $item->month, 1));
            $result[$monthName] = (float) $item->total_income;
        }

        \Log::info("Income Chart Data:", $result);
        return response()->json($result);
    }


    /**
     * Lấy dữ liệu thống kê thu nhập theo tháng của doctor (phía admin).
     */
    public function doctorincomeChart(Request $request)
    {
        // Lấy năm mới nhất có dữ liệu trong bảng
        $latestYear = AffiliateOrder::selectRaw('YEAR(created_at) as year')
            ->orderBy('year', 'desc')
            ->limit(1)
            ->pluck('year')
            ->first() ?? \Carbon\Carbon::now()->year; // Nếu không có dữ liệu, lấy năm hiện tại

            $items = AffiliateOrder::whereYear('created_at', $latestYear)
            ->whereIn('status', ['delivered']) // Chỉ lấy trạng thái hợp lệ
            ->selectRaw('MONTH(created_at) as month, SUM(commission) as total_commission')
            ->groupBy('month')
            ->orderBy('month')
            ->get();


        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $result[$monthName] = 0; // Mặc định nếu không có dữ liệu
        }

        foreach ($items as $item) {
            $monthName = date('F', mktime(0, 0, 0, $item->month, 1));
            $result[$monthName] = intval($item->total_commission);
        }

        return response()->json($result);
    }



    //API section

    public function apiGetUserOrders()
    {
        try {
            // Lấy danh sách tất cả đơn hàng của user đang đăng nhập
            $orders = Order::where('user_id', Auth::id())
                ->with(['cart_info', 'shipping']) // Lấy cả thông tin giỏ hàng & vận chuyển
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng nào.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danh sách đơn hàng của user.',
                'orders' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đơn hàng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function apiCreateOrder(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'sub_total' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'post_code' => 'nullable|string|max:10',
            'address1' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:191',
            'payment_method' => 'required|in:cod,paypal',
            'shipping_id' => 'nullable|exists:shippings,id',
            'coupon' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => Str::random(10),
                'sub_total' => number_format($request->sub_total, 3, '.', ''), // Đảm bảo 3 số thập phân
                'quantity' => $request->quantity,
                'total_amount' => number_format($request->total_amount, 3, '.', ''),
                'status' => 'new', // Trạng thái hợp lệ theo enum
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'country' => $request->country,
                'post_code' => $request->post_code,
                'address1' => $request->address1,
                'phone' => $request->phone,
                'email' => $request->email,
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'shipping_id' => $request->shipping_id,
                'coupon' => $request->coupon ?? 0.000,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được tạo thành công!',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo đơn hàng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API lấy trạng thái của một đơn hàng cụ thể
     */
    public function apiGetOrderStatus($order_id)
    {
        try {
            // Kiểm tra đơn hàng có tồn tại không
            $order = Order::where('id', $order_id)
                ->where('user_id', Auth::id()) // Chỉ lấy đơn hàng của user đang đăng nhập
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Trạng thái đơn hàng.',
                'order_id' => $order->id,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy trạng thái đơn hàng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API lấy trạng thái tất cả đơn hàng của user
     */
    public function apiGetUserOrdersStatus()
    {
        try {
            // Lấy tất cả đơn hàng của user hiện tại
            $orders = Order::where('user_id', Auth::id())
                ->select('id', 'order_number', 'status', 'payment_status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng nào.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Danh sách trạng thái đơn hàng của user.',
                'orders' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách trạng thái đơn hàng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
