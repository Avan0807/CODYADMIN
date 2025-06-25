<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\Product;
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
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $orders = Order::with(['cartInfo.product:id,title', 'shipping:id,price'])
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->get();

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
    public function store(Request $request)
    {
        $request->validate([
            'first_name'            => 'required|string',
            'last_name'             => 'required|string',
            'address1'              => 'required|string',
            'phone'                 => 'required|string',
            'email'                 => 'required|email',
            'ghn_to_province_id'    => 'required|integer',
            'ghn_to_district_id'    => 'required|integer',
            'ghn_to_ward_code'      => 'required|string',
            'ghn_service_id'        => 'nullable|integer',
            'shipping_fee'          => 'nullable|numeric|min:0',
            'payment_method'        => 'required|string',
        ]);

        $userId = auth()->id();
        $carts = Cart::where('user_id', $userId)->whereNull('order_id')->get();

        if ($carts->isEmpty()) {
            return response()->json(['error' => 'Giỏ hàng đang trống!'], 400);
        }

        // ✅ AUTO CALCULATE SHIPPING FEE nếu chưa có
        if (!$request->shipping_fee) {
            $shippingCalculation = $this->calculateShippingForOrder($request, $carts);

            if (!$shippingCalculation['success']) {
                return response()->json([
                    'error' => 'Không thể tính phí vận chuyển: ' . $shippingCalculation['message']
                ], 400);
            }

            $shippingFee = $shippingCalculation['shipping_fee'];
            $serviceId = $shippingCalculation['service_id'];
        } else {
            // Dùng shipping_fee từ frontend
            $shippingFee = $request->shipping_fee;
            $serviceId = $request->ghn_service_id ?? 53321;
        }

        $subTotal = $carts->sum('amount');
        $totalAmount = $subTotal + $shippingFee;

        // ✅ Tạo order
        $order = Order::create([
            'order_number'          => 'ORD-' . strtoupper(Str::random(10)),
            'user_id'               => $userId,
            'first_name'            => $request->first_name,
            'last_name'             => $request->last_name,
            'address1'              => $request->address1,
            'email'                 => $request->email,
            'phone'                 => $request->phone,
            'quantity'              => $carts->sum('quantity'),
            'sub_total'             => $subTotal,
            'shipping_cost'         => $shippingFee,
            'total_amount'          => $totalAmount,
            'payment_method'        => $request->payment_method,
            'payment_status'        => $request->payment_method == 'cod' ? 'unpaid' : 'paid',
            'status'                => 'new',

            // GHN fields
            'ghn_to_province_id'    => $request->ghn_to_province_id,
            'ghn_to_district_id'    => $request->ghn_to_district_id,
            'ghn_to_ward_code'      => $request->ghn_to_ward_code,
            'ghn_service_id'        => $serviceId,
            'ghn_status'            => 'pending',
        ]);

        // ✅ TẠO ĐƠN GHN TỰ ĐỘNG (nếu không phải COD)
        if ($request->payment_method !== 'cod') {
            try {
                $ghnResult = $this->createGHNOrderFromOrder($order);

                if ($ghnResult['success']) {
                    $order->update([
                        'ghn_order_code' => $ghnResult['order_code'],
                        'ghn_status' => 'confirmed',
                        'ghn_tracking_url' => "https://donhang.ghn.vn/?order_code=" . $ghnResult['order_code']
                    ]);

                    \Log::info('GHN order created for Order #' . $order->id, [
                        'ghn_order_code' => $ghnResult['order_code']
                    ]);
                } else {
                    \Log::warning('Failed to create GHN order for Order #' . $order->id, [
                        'error' => $ghnResult['message']
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('GHN order creation exception for Order #' . $order->id, [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // ✅ XỬ LÝ AFFILIATE - Thay vì chỉ update order_id
        $this->processAffiliateAPI($order, $carts);

        return response()->json([
            'success' => true,
            'message' => 'Đặt hàng thành công!',
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'ghn_status' => $order->ghn_status,
                    'ghn_order_code' => $order->ghn_order_code,
                    'tracking_url' => $order->ghn_tracking_url,
                    'order_date' => $order->created_at->format('d/m/Y H:i'),
                ],
                'customer' => [
                    'name' => $order->first_name . ' ' . $order->last_name,
                    'email' => $order->email,
                    'phone' => $order->phone,
                    'address' => $order->address1,
                ],
                'payment' => [
                    'method' => $order->payment_method,
                    'status' => $order->payment_status,
                    'sub_total' => $subTotal,
                    'shipping_fee' => $shippingFee,
                    'total_amount' => $totalAmount,
                    'formatted_sub_total' => number_format($subTotal, 0, ',', '.') . 'đ',
                    'formatted_shipping_fee' => number_format($shippingFee, 0, ',', '.') . 'đ',
                    'formatted_total' => number_format($totalAmount, 0, ',', '.') . 'đ',
                ],
                'shipping' => [
                    'service_id' => $serviceId,
                    'service_name' => $this->getServiceName($serviceId),
                    'province_id' => $order->ghn_to_province_id,
                    'district_id' => $order->ghn_to_district_id,
                    'ward_code' => $order->ghn_to_ward_code,
                    'estimated_delivery' => '2-3 ngày làm việc',
                    'shipping_source' => $request->shipping_fee ? 'manual' : 'auto_calculated'
                ],
                'items' => [
                    'count' => $carts->count(),
                    'total_quantity' => $carts->sum('quantity'),
                    'products' => $carts->map(function($cart) {
                        $product = Product::find($cart->product_id);
                        return [
                            'name' => $product ? $product->title : 'Sản phẩm không tồn tại',
                            'photo' => $product ? $product->photo : 'Ảnh không tồn tại',
                            'quantity' => $cart->quantity,
                            'price' => $cart->price,
                            'amount' => $cart->amount,
                            'doctor_id' => $cart->doctor_id, // ✅ Thêm thông tin affiliate
                            'commission' => $cart->commission, // ✅ Thêm commission info
                            'formatted_price' => number_format($cart->price, 0, ',', '.') . 'đ',
                            'formatted_amount' => number_format($cart->amount, 0, ',', '.') . 'đ',
                        ];
                    })
                ],
                // ✅ THÊM AFFILIATE INFO
                'affiliate' => [
                    'doctors_count' => $carts->where('doctor_id', '!=', null)->pluck('doctor_id')->unique()->count(),
                    'total_commission' => $carts->sum('commission'),
                    'formatted_commission' => number_format($carts->sum('commission'), 0, ',', '.') . 'đ',
                ]
            ]
        ]);
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

        $oldStatus = $order->status; // ✅ Lưu trạng thái cũ
        $newStatus = $request->status;

        // ✅ Chỉ xử lý stock khi trạng thái thực sự thay đổi
        if ($oldStatus !== $newStatus) {

            // Trường hợp chuyển sang 'delivered' (trừ stock)
            if ($newStatus == 'delivered' && $oldStatus != 'delivered') {
                foreach ($order->cart as $cart) {
                    $product = $cart->product;

                    // ✅ Kiểm tra stock trước khi trừ
                    if ($product->stock >= $cart->quantity) {
                        $product->stock -= $cart->quantity;
                        $product->save();
                    } else {
                        return back()->with('error', "Sản phẩm {$product->title} không đủ tồn kho!");
                    }
                }
            }

            // Trường hợp chuyển từ 'delivered' sang trạng thái khác (hoàn stock)
            if ($oldStatus == 'delivered' && $newStatus != 'delivered') {
                foreach ($order->cart as $cart) {
                    $product = $cart->product;
                    $product->stock += $cart->quantity; // ✅ Hoàn lại stock
                    $product->save();
                }
            }
        }
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
     * Lấy dữ liệu thống kê thu nhập theo bác sĩ theo tháng (phía admin).
     */
    public function doctorincomeChart(Request $request)
    {
        // Lấy năm mới nhất có dữ liệu trong bảng
        $latestYear = AffiliateOrder::selectRaw('YEAR(created_at) as year')
            ->orderBy('year', 'desc')
            ->limit(1)
            ->pluck('year')
            ->first() ?? \Carbon\Carbon::now()->year; // Nếu không có dữ liệu, lấy năm hiện tại

        // Lấy dữ liệu tổng hoa hồng theo tháng
        $items = AffiliateOrder::whereYear('created_at', $latestYear)
            ->selectRaw('MONTH(created_at) as month, SUM(commission) as total_commission')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $result = [];

        // Khởi tạo giá trị cho tất cả các tháng
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $result[$monthName] = 0; // Mặc định nếu không có dữ liệu
        }

        // Cập nhật dữ liệu cho các tháng có dữ liệu
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
            $orders = Order::where('user_id', Auth::id())
                ->with([
                    'cartInfo.product:id,title,photo,price,discount,stock',
                    'shipping:id,type,price',
                    'user:id,name'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng nào.',
                ], 404);
            }

            $ordersFormatted = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $order->user_id,
                    'customer_name' => $order->user->name ?? ($order->first_name . ' ' . $order->last_name),
                    'customer_email' => $order->user->email ?? $order->email,
                    
                    // ✅ Ép kiểu số rõ ràng
                    'sub_total' => floatval($order->sub_total ?? 0),
                    'shipping_cost' => floatval($order->shipping_cost ?? 0),
                    'total_amount' => floatval($order->total_amount ?? 0),
                    'quantity' => intval($order->quantity ?? 0),
                    'coupon' => floatval($order->coupon ?? 0),
                    
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'phone' => $order->phone,
                    'address' => $order->getFullAddress(),
                    'country' => $order->country,
                    'post_code' => $order->post_code,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    
                    // GHN fields
                    'ghn_order_code' => $order->ghn_order_code,
                    'ghn_status' => $order->ghn_status,
                    'tracking_url' => $order->getTrackingUrl(),
                    'shipping_method' => $order->getShippingMethod(),
                    
                    // Related data
                    'cart_info' => $order->cartInfo,
                    'shipping' => $order->shipping,
                    'user' => $order->user,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Danh sách đơn hàng của người dùng.',
                'orders' => $ordersFormatted,
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
            'payment_method' => 'required|in:cod,paypal,vnpay',
            'shipping_id' => 'nullable|exists:shippings,id',
            'coupon' => 'nullable|numeric|min:0',
            // ✅ Thêm GHN fields nếu cần
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // ✅ Kiểm tra giỏ hàng
            $carts = Cart::where('user_id', Auth::id())->whereNull('order_id')->get();
            if ($carts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giỏ hàng đang trống!',
                ], 400);
            }

            // ✅ Tạo đơn hàng
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'sub_total' => $request->sub_total,
                'quantity' => $request->quantity,
                'total_amount' => $request->total_amount,
                'status' => 'new',
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
                'shipping_cost' => $request->shipping_cost ?? 0,
                'coupon' => $request->coupon ?? 0,
            ]);

            // ✅ Xử lý affiliate (giống web version)
            $this->processAffiliateAPI($order, $carts);

            // ✅ Load order với cart items để return
            $order->load('cartInfo.product', 'cartInfo.doctor');

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
     * ✅ Xử lý affiliate cho API (giống web version)
     */
    private function processAffiliateAPI($order, $carts)
    {
        foreach ($carts as $cart) {
            $product = Product::find($cart->product_id);
            if ($product) {
                $commission = $cart->doctor_id
                    ? (($cart->price * $cart->quantity) * ($product->commission_percentage / 100))
                    : 0;

                $cart->update([
                    'order_id'   => $order->id,
                    'commission' => $commission,
                ]);
            }
        }

        // ✅ Tạo affiliate_order cho TỪNG doctor riêng biệt
        $doctorCommissions = $carts->where('doctor_id', '!=', null)->groupBy('doctor_id');

        foreach ($doctorCommissions as $doctor_id => $doctorCarts) {
            AffiliateOrder::firstOrCreate(
                ['order_id' => $order->id, 'doctor_id' => $doctor_id],
                ['commission' => $doctorCarts->sum('commission'), 'status' => 'new']
            );
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
