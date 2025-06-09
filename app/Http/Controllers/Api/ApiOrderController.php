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
use App\Models\AffiliateOrder;
use App\Models\AffiliateLink;
use App\Services\GHNService;

class ApiOrderController extends Controller
{
    protected $ghnService;

    public function __construct(GHNService $ghnService)
    {
        $this->ghnService = $ghnService;
    }
    /**
     * Hiển thị danh sách đơn hàng (API).
     */
    public function index(Request $request)
    {
        $orders = Order::orderBy('id', 'DESC')->paginate(10);
        return response()->json($orders);
    }
    /**
     * Lấy đơn hàng theo trạng thái của người dùng đăng nhập.
     */
    public function getOrdersByStatus(Request $request)
    {
        // Kiểm tra nếu người dùng chưa đăng nhập
        if (!Auth::check()) {
            return response()->json(['error' => 'Bạn cần đăng nhập để xem đơn hàng.'], 401);
        }

        // Lấy thông tin trạng thái từ request, mặc định là 'new'
        $status = $request->input('status', 'new');

        // Kiểm tra trạng thái có hợp lệ không
        $validStatuses = ['new', 'process', 'delivered', 'cancel'];
        if (!in_array($status, $validStatuses)) {
            return response()->json(['error' => 'Trạng thái đơn hàng không hợp lệ.'], 400);
        }

        // Lấy danh sách đơn hàng của người dùng theo trạng thái
        $orders = Order::where('user_id', auth()->id())
                        ->where('status', $status)
                        ->orderBy('id', 'DESC')
                        ->paginate(10);

        // Trả về kết quả
        return response()->json($orders);
    }

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

    // Tạo order
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

        // GHN fields - THÊM PROVINCE
        'ghn_to_province_id'    => $request->ghn_to_province_id,  // ← THÊM
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

    // Update cart
    foreach ($carts as $cart) {
        $cart->order_id = $order->id;
        $cart->save();
    }

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
                        'formatted_price' => number_format($cart->price, 0, ',', '.') . 'đ',
                        'formatted_amount' => number_format($cart->amount, 0, ',', '.') . 'đ',
                    ];
                })
            ]
        ]
    ]);
}

/**
 * ✅ Helper: Tính shipping fee cho order
 */
private function calculateShippingForOrder($request, $carts)
{
    try {
        // Tính trọng lượng
        $totalWeight = $carts->sum('quantity') * 200; // 200g/sản phẩm
        
        // Chọn service tối ưu
        $serviceId = $request->ghn_service_id ?? $this->selectOptimalService($carts, $totalWeight);
        
        // Params để tính phí
        $params = [
            'service_id' => $serviceId,
            'from_district_id' => 1493, // District shop của bạn
            'to_district_id' => $request->ghn_to_district_id,
            'to_ward_code' => $request->ghn_to_ward_code,
            'weight' => $totalWeight,
            'length' => 20,
            'width' => 20,
            'height' => 10,
            'insurance_value' => 0
        ];

        \Log::info('Calculating shipping for order with params:', $params);

        // Gọi GHN service
        $result = $this->ghnService->calculateShippingFee($params);

        if ($result['success']) {
            return [
                'success' => true,
                'shipping_fee' => $result['total_fee'],
                'service_id' => $serviceId,
                'source' => 'ghn'
            ];
        } else {
            // Fallback to fixed fee
            return [
                'success' => true,
                'shipping_fee' => $this->calculateFixedFee($params),
                'service_id' => $serviceId,
                'source' => 'fixed'
            ];
        }
        
    } catch (\Exception $e) {
        \Log::error('Calculate shipping for order exception:', [
            'message' => $e->getMessage()
        ]);
        
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * ✅ Helper: Chọn service tối ưu
 */
private function selectOptimalService($carts, $totalWeight)
{
    $totalValue = $carts->sum('amount');
    
    // Logic chọn service
    if ($totalWeight > 5000 || $totalValue > 2000000) {
        return 53320; // Standard cho hàng nặng/giá trị cao
    }
    
    return 53321; // Express cho hàng nhẹ
}


/**
 * ✅ Helper: Lấy tên service
 */
private function getServiceName($serviceId)
{
    $services = [
        53320 => 'GHN Standard',
        53321 => 'GHN Express',
    ];
    
    return $services[$serviceId] ?? 'GHN Standard';
}

/**
 * ✅ Helper: Tạo đơn GHN từ Order
 */
private function createGHNOrderFromOrder($order)
{
    $orderData = [
        'to_name' => $order->first_name . ' ' . $order->last_name,
        'to_phone' => $order->phone,
        'to_address' => $order->address1,
        'to_ward_code' => $order->ghn_to_ward_code,
        'to_district_id' => $order->ghn_to_district_id,
        'service_id' => $order->ghn_service_id,
        'cod_amount' => $order->payment_method === 'cod' ? $order->total_amount : 0,
        'content' => 'Đơn hàng #' . $order->order_number,
        'weight' => 500,
        'length' => 20,
        'width' => 20,
        'height' => 10,
        'note' => 'Đơn hàng từ mobile app',
        'client_order_code' => $order->order_number,
    ];

    return $this->ghnService->createShippingOrder($orderData);
}

    /**
     * ← Tạo đơn hàng GHN (đơn giản)
     */
    private function createGHNOrder($request, $carts, $order)
    {
        try {
            $orderData = [
                'to_name' => $request->first_name . ' ' . $request->last_name,
                'to_phone' => $request->phone,
                'to_address' => $request->address1,
                'to_ward_code' => $request->ghn_to_ward_code,
                'to_district_id' => (int)$request->ghn_to_district_id,
                'service_id' => (int)$request->ghn_service_id,
                'cod_amount' => $request->payment_method === 'cod' ? (int)$order->total_amount : 0,
                'content' => 'Đơn hàng TOIKHOE #' . $order->order_number,
                'weight' => $carts->sum('quantity') * 200, // 200g/sản phẩm
                'length' => 20,
                'width' => 15,
                'height' => 10,
                'note' => 'Đơn hàng từ app TOIKHOE',
                'client_order_code' => $order->order_number,
            ];

            $result = $this->ghnService->createShippingOrder($orderData);

            if ($result['success']) {
                return $result['order_code'];
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('GHN Order Creation Failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ← API theo dõi đơn hàng GHN
     */
    public function trackGHNOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::find($request->order_id);

        if (!$order->ghn_order_code) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này không sử dụng GHN'
            ], 400);
        }

        try {
            $result = $this->ghnService->trackOrder($order->ghn_order_code);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'order_code' => $order->ghn_order_code,
                        'tracking_info' => $result['data'],
                        'order_number' => $order->order_number,
                        'tracking_url' => $order->ghn_tracking_url
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể theo dõi đơn hàng'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống'
            ], 500);
        }
    }
/**
 * API tự động tính phí khi có đủ thông tin địa chỉ
 */
public function autoCalculateShipping(Request $request)
{
    try {
        $request->validate([
            'to_district_id' => 'required|integer',
            'to_ward_code' => 'required|string',
            'service_id' => 'nullable|integer' // Mặc định sẽ dùng service nhẹ
        ]);

        $userId = auth()->id();

        // Lấy cart items để tính trọng lượng
        $carts = Cart::where('user_id', $userId)->whereNull('order_id')->get();

        if ($carts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng đang trống!'
            ], 400);
        }

        // Tính trọng lượng từ cart
        $totalWeight = $carts->sum('quantity') * 200; // 200g/sản phẩm
        $serviceId = $request->service_id ?? 53321; // Mặc định service nhẹ

        // Params để tính phí
        $params = [
            'service_id' => $serviceId,
            'from_district_id' => 1493, // District shop của bạn
            'to_district_id' => $request->to_district_id,
            'to_ward_code' => $request->to_ward_code,
            'weight' => $totalWeight,
            'length' => 20,
            'width' => 20,
            'height' => 10,
            'insurance_value' => 0
        ];

        \Log::info('Auto calculate shipping with params:', $params);

        // Gọi service tính phí (có fallback)
        $result = $this->ghnService->calculateShippingFee($params);

        if ($result['success']) {
            // GHN thành công
            $shippingFee = $result['total_fee'];
            $source = 'ghn';
        } else {
            // Fallback to fixed fee
            \Log::warning('GHN failed, using fixed fee fallback');
            $shippingFee = $this->calculateFixedFee($params);
            $source = 'fixed';
        }

        // Tính tổng tiền
        $subTotal = $carts->sum('amount');
        $totalAmount = $subTotal + $shippingFee;

        return response()->json([
            'success' => true,
            'message' => 'Tính phí thành công',
            'data' => [
                'cart_summary' => [
                    'sub_total' => $subTotal,
                    'formatted_sub_total' => number_format($subTotal, 0, ',', '.') . 'đ',
                    'items_count' => $carts->count(),
                    'total_quantity' => $carts->sum('quantity'),
                    'total_weight' => $totalWeight
                ],
                'shipping_info' => [
                    'shipping_fee' => $shippingFee,
                    'formatted_fee' => number_format($shippingFee, 0, ',', '.') . 'đ',
                    'service_id' => $serviceId,
                    'service_name' => $serviceId == 53321 ? 'Hàng nhẹ' : 'Hàng nặng',
                    'estimated_delivery' => '2-3 ngày làm việc',
                    'source' => $source
                ],
                'order_total' => [
                    'total_amount' => $totalAmount,
                    'formatted_total' => number_format($totalAmount, 0, ',', '.') . 'đ'
                ],
                'address_info' => [
                    'to_district_id' => $request->to_district_id,
                    'to_ward_code' => $request->to_ward_code
                ]
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Auto calculate exception:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper method tính phí fixed (backup)
 */
private function calculateFixedFee($params)
{
    $fixedFees = [
        53321 => ['base_fee' => 15000, 'per_km' => 500], // Hàng nhẹ
        180039 => ['base_fee' => 25000, 'per_km' => 800] // Hàng nặng
    ];

    $serviceId = $params['service_id'];
    $weight = $params['weight'] ?? 500;

    if (isset($fixedFees[$serviceId])) {
        $baseFee = $fixedFees[$serviceId]['base_fee'];
        $perKm = $fixedFees[$serviceId]['per_km'];

        // Tính khoảng cách giả lập
        $distance = abs($params['from_district_id'] - $params['to_district_id']) * 2;
        $distanceFee = $distance * $perKm;

        // Phí theo trọng lượng
        $weightFee = $weight > 500 ? ($weight - 500) * 10 : 0;

        return $baseFee + $distanceFee + $weightFee;
    }

    return 16500; // Default fee
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
