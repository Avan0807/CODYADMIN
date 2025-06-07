<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GHNService;
use Illuminate\Support\Facades\Validator;

class ApiMobileShippingController extends Controller
{
    protected $ghnService;

    public function __construct(GHNService $ghnService)
    {
        $this->ghnService = $ghnService;
    }

    /**
     * API: Lấy danh sách tỉnh/thành phố
     * GET /api/mobile/shipping/provinces
     */
    public function getProvinces()
    {
        try {
            $provinces = $this->ghnService->getProvinces();

            return $this->successResponse([
                'provinces' => $provinces
            ], 'Lấy danh sách tỉnh/thành phố thành công');

        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi khi lấy danh sách tỉnh/thành phố', 500);
        }
    }

    /**
     * API: Lấy danh sách quận/huyện theo tỉnh
     * GET /api/mobile/shipping/districts?province_id=201
     */
    public function getDistricts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Dữ liệu không hợp lệ', 422, $validator->errors());
        }

        try {
            $districts = $this->ghnService->getDistricts($request->province_id);

            return $this->successResponse([
                'districts' => $districts,
                'province_id' => $request->province_id
            ], 'Lấy danh sách quận/huyện thành công');

        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi khi lấy danh sách quận/huyện', 500);
        }
    }

    /**
     * API: Lấy danh sách phường/xã theo quận
     * GET /api/mobile/shipping/wards?district_id=1442
     */
    public function getWards(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Dữ liệu không hợp lệ', 422, $validator->errors());
        }

        try {
            $wards = $this->ghnService->getWards($request->district_id);

            return $this->successResponse([
                'wards' => $wards,
                'district_id' => $request->district_id
            ], 'Lấy danh sách phường/xã thành công');

        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi khi lấy danh sách phường/xã', 500);
        }
    }

    /**
     * API: Lấy danh sách dịch vụ vận chuyển
     * POST /api/mobile/shipping/services
     * Body: {"from_district_id": 1493, "to_district_id": 1442}
     */
    public function getShippingServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_district_id' => 'required|integer',
            'to_district_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Dữ liệu không hợp lệ', 422, $validator->errors());
        }

        try {
            $shopId = config('services.ghn.shop_id', 5822392);

            $services = $this->ghnService->getServices(
                $shopId,
                $request->from_district_id,
                $request->to_district_id
            );

            if (empty($services)) {
                return $this->errorResponse('Không có dịch vụ vận chuyển khả dụng cho tuyến đường này', 404);
            }

            return $this->successResponse([
                'services' => $services,
                'from_district_id' => $request->from_district_id,
                'to_district_id' => $request->to_district_id,
                'total_services' => count($services)
            ], 'Lấy danh sách dịch vụ vận chuyển thành công');

        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi khi lấy dịch vụ vận chuyển', 500);
        }
    }

    /**
     * API: Tính phí vận chuyển
     * POST /api/mobile/shipping/calculate
     * Body: {
     *   "service_id": 53320,
     *   "from_district_id": 1493,
     *   "to_district_id": 1442,
     *   "to_ward_code": "11007",
     *   "weight": 500,
     *   "length": 20,
     *   "width": 20,
     *   "height": 10
     * }
     */public function calculateShippingFee(Request $request)
{
    try {
        \Log::info('=== CALCULATE API START ===', $request->all());

        // Validation (giữ nguyên)
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|integer',
            'from_district_id' => 'required|integer',
            'to_district_id' => 'required|integer',
            'to_ward_code' => 'required|string',
            'weight' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare params (giữ nguyên)
        $params = [
            'service_id' => $request->service_id,
            'from_district_id' => $request->from_district_id,
            'to_district_id' => $request->to_district_id,
            'to_ward_code' => $request->to_ward_code,
            'weight' => $request->weight ?? 500,
            'length' => $request->length ?? 20,
            'width' => $request->width ?? 20,
            'height' => $request->height ?? 10,
            'insurance_value' => $request->insurance_value ?? 0
        ];

        \Log::info('Calling GHN with params:', $params);

        // ← TRY GHN FIRST
        $result = $this->ghnService->calculateShippingFee($params);
        \Log::info('GHN result:', $result);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Calculate success (GHN)',
                'data' => [
                    'shipping_fee' => $result['total_fee'],
                    'formatted_fee' => number_format($result['total_fee'], 0, ',', '.') . 'đ',
                    'service_fee' => $result['service_fee'] ?? 0,
                    'insurance_fee' => $result['insurance_fee'] ?? 0,
                    'pickup_fee' => $result['pickup_fee'] ?? 0,
                    'source' => 'ghn' // ← Đánh dấu nguồn
                ]
            ]);
        }

        // ← FALLBACK TO FIXED FEE nếu GHN fail
        \Log::warning('GHN failed, using fixed fee fallback');

        $fixedFee = $this->calculateFixedFee($params);

        return response()->json([
            'success' => true,
            'message' => 'Calculate success (Fixed)',
            'data' => [
                'shipping_fee' => $fixedFee,
                'formatted_fee' => number_format($fixedFee, 0, ',', '.') . 'đ',
                'service_fee' => $fixedFee,
                'insurance_fee' => 0,
                'pickup_fee' => 0,
                'source' => 'fixed', // ← Đánh dấu nguồn
                'note' => 'GHN tạm thời không khả dụng - sử dụng phí cố định'
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Calculate exception:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        // ← FALLBACK TO FIXED FEE khi có exception
        try {
            $params = [
                'service_id' => $request->service_id,
                'from_district_id' => $request->from_district_id,
                'to_district_id' => $request->to_district_id,
                'weight' => $request->weight ?? 500,
            ];

            $fixedFee = $this->calculateFixedFee($params);

            return response()->json([
                'success' => true,
                'message' => 'Calculate success (Fixed - Emergency)',
                'data' => [
                    'shipping_fee' => $fixedFee,
                    'formatted_fee' => number_format($fixedFee, 0, ',', '.') . 'đ',
                    'service_fee' => $fixedFee,
                    'insurance_fee' => 0,
                    'pickup_fee' => 0,
                    'source' => 'fixed_emergency'
                ]
            ]);
        } catch (\Exception $e2) {
            return response()->json([
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ], 500);
        }
    }
}

/**
 * ← Hàm tính phí cố định backup
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
     * API: Tạo đơn hàng vận chuyển
     * POST /api/mobile/shipping/create-order
     */
    public function createShippingOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to_name' => 'required|string|max:255',
            'to_phone' => 'required|string|max:20',
            'to_address' => 'required|string|max:500',
            'to_ward_code' => 'required|string',
            'to_district_id' => 'required|integer',
            'service_id' => 'required|integer',
            'cod_amount' => 'nullable|integer|min:0',
            'content' => 'required|string|max:500',
            'weight' => 'nullable|integer|min:1',
            'length' => 'nullable|integer|min:1',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'insurance_value' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Dữ liệu không hợp lệ', 422, $validator->errors());
        }

        try {
            $orderData = [
                'to_name' => $request->to_name,
                'to_phone' => $request->to_phone,
                'to_address' => $request->to_address,
                'to_ward_code' => $request->to_ward_code,
                'to_district_id' => $request->to_district_id,
                'service_id' => $request->service_id,
                'cod_amount' => $request->cod_amount ?? 0,
                'content' => $request->content,
                'weight' => $request->weight ?? 500,
                'length' => $request->length ?? 20,
                'width' => $request->width ?? 20,
                'height' => $request->height ?? 10,
                'insurance_value' => $request->insurance_value ?? 0,
                'note' => $request->note ?? '',
                'client_order_code' => 'MOBILE_' . time() . '_' . rand(1000, 9999),
            ];

            $result = $this->ghnService->createShippingOrder($orderData);

            if ($result['success']) {
                return $this->successResponse([
                    'order_code' => $result['order_code'],
                    'sort_code' => $result['sort_code'],
                    'fee' => $result['fee'],
                    'order_data' => $orderData
                ], 'Tạo đơn hàng vận chuyển thành công');
            }

            return $this->errorResponse($result['message'] ?? 'Không thể tạo đơn hàng', 400);

        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi khi tạo đơn hàng vận chuyển', 500);
        }
    }

    /**
     * API: Theo dõi đơn hàng
     * GET /api/mobile/shipping/track?order_code=L8EMKVW6
     */
    public function trackOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Dữ liệu không hợp lệ', 422, $validator->errors());
        }

        try {
            $result = $this->ghnService->trackOrder($request->order_code);

            if ($result['success']) {
                return $this->successResponse([
                    'order_info' => $result['data'],
                    'order_code' => $request->order_code
                ], 'Lấy thông tin đơn hàng thành công');
            }

            return $this->errorResponse($result['message'] ?? 'Không tìm thấy đơn hàng', 404);

        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi khi theo dõi đơn hàng', 500);
        }
    }

    /**
     * Response helper - Success
     */
    private function successResponse($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ], $code);
    }

    /**
     * Response helper - Error
     */
    private function errorResponse($message = 'Error', $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
