<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GHNService;
use Illuminate\Support\Facades\Log;

class GHNController extends Controller
{
    protected $ghnService;

    public function __construct(GHNService $ghnService)
    {
        $this->ghnService = $ghnService;
    }

    /**
     * Lấy danh sách tỉnh/thành phố từ GHN
     */
    public function getProvinces()
    {
        $provinces = $this->ghnService->getProvinces();

        return response()->json([
            'success' => true,
            'data' => $provinces
        ]);
    }

    /**
     * Lấy danh sách quận/huyện theo tỉnh
     */
    public function getDistricts(Request $request)
    {
        $request->validate([
            'province_id' => 'required|integer'
        ]);

        $districts = $this->ghnService->getDistricts($request->province_id);

        return response()->json([
            'success' => true,
            'data' => $districts
        ]);
    }

    /**
     * Lấy danh sách phường/xã theo quận
     */
    public function getWards(Request $request)
    {
        $request->validate([
            'district_id' => 'required|integer'
        ]);

        $wards = $this->ghnService->getWards($request->district_id);

        return response()->json([
            'success' => true,
            'data' => $wards
        ]);
    }

    /**
     * Lấy danh sách dịch vụ giao hàng 
     */
    public function getServices(Request $request)
    {
        try {
            $request->validate([
                'from_district_id' => 'required|integer',
                'to_district_id' => 'required|integer',
                'shop_id' => 'nullable|integer'
            ]);

            $shopId = $request->shop_id ?? config('services.ghn.shop_id') ?? 5822392;

            $services = $this->ghnService->getServices(
                $shopId,
                $request->from_district_id,
                $request->to_district_id
            );

            return response()->json([
                'success' => true,
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function calculateShippingFee(Request $request)
    {
        try {
            \Log::info('=== CALCULATE SHIPPING DEBUG ===');
            \Log::info('Request data:', $request->all());
            \Log::info('GHN Config:', [
                'api_url' => config('services.ghn.api_url'),
                'token_exists' => !empty(config('services.ghn.token')),
                'shop_id_exists' => !empty(config('services.ghn.shop_id'))
            ]);

            $request->validate([
                'service_id' => 'required|integer',
                'from_district_id' => 'required|integer',
                'to_district_id' => 'required|integer',
                'to_ward_code' => 'required|string',
            ]);

            $params = [
                'service_id' => $request->service_id,
                'from_district_id' => $request->from_district_id,
                'to_district_id' => $request->to_district_id,
                'to_ward_code' => $request->to_ward_code,
                'weight' => $request->weight ?? 200,
                'length' => $request->length ?? 15,
                'width' => $request->width ?? 15,
                'height' => $request->height ?? 15,
                'insurance_value' => $request->insurance_value ?? 0
            ];

            \Log::info('Calling GHN service with params:', $params);

            $result = $this->ghnService->calculateShippingFee($params);

            \Log::info('GHN service raw result:', [
                'type' => gettype($result),
                'value' => $result
            ]);

            if (is_array($result) && isset($result['success']) && $result['success']) {
                return response()->json([
                    'success' => true,
                    'shipping_fee' => $result['total_fee'],
                    'formatted_fee' => number_format($result['total_fee'], 0, ',', '.') . 'đ',
                    'details' => [
                        'service_fee' => $result['service_fee'] ?? 0,
                        'insurance_fee' => $result['insurance_fee'] ?? 0,
                        'pickup_fee' => $result['pickup_fee'] ?? 0,
                        'coupon_value' => $result['coupon_value'] ?? 0,
                        'r2s_fee' => $result['r2s_fee'] ?? 0
                    ],
                    'data' => $result['data'] ?? []
                ]);
            }

            \Log::warning('[GHN][calculateShippingFee] Kết quả không hợp lệ hoặc không thành công', [
                'response' => $result
            ]);

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Không thể tính phí vận chuyển',
                'error' => $result['error'] ?? 'Unknown error'
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Calculate shipping exception:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }




    /** 
     * Tạo đơn hàng vận chuyển
     */
    public function createShippingOrder(Request $request)
    {
        $request->validate([
            'to_name' => 'required|string|max:255',
            'to_phone' => 'required|string|max:20',
            'to_address' => 'required|string|max:500',
            'to_ward_code' => 'required|string',
            'to_district_id' => 'required|integer',
            'service_id' => 'integer',
            'cod_amount' => 'nullable|integer|min:0',
            'content' => 'required|string|max:500',
            'weight' => 'nullable|integer|min:1',
            'length' => 'nullable|integer|min:1',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'insurance_value' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:500',
            'required_note' => 'nullable|in:CHOTHUHANG,CHOXEMHANGKHONGTHU,KHONGCHOXEMHANG',
            'items' => 'nullable|array',
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.weight' => 'nullable|integer|min:1',
            'items.*.length' => 'nullable|integer|min:1',
            'items.*.width' => 'nullable|integer|min:1',
            'items.*.height' => 'nullable|integer|min:1'
        ]);

        // Chuẩn bị dữ liệu đơn hàng
        $orderData = [
            'to_name' => $request->to_name,
            'to_phone' => $request->to_phone,
            'to_address' => $request->to_address,
            'to_ward_code' => $request->to_ward_code,
            'to_district_id' => $request->to_district_id,
            'service_id' => $request->service_id,
            'cod_amount' => $request->cod_amount ?? 0,
            'content' => $request->content,
            'weight' => $request->weight ?? 200,
            'length' => $request->length ?? 15,
            'width' => $request->width ?? 15,
            'height' => $request->height ?? 15,
            'insurance_value' => $request->insurance_value ?? 0,
            'note' => $request->note ?? '',
            'required_note' => $request->required_note ?? 'KHONGCHOXEMHANG',
            'client_order_code' => 'ORDER_' . time() . '_' . rand(1000, 9999),
            'payment_type_id' => 1, // Shop trả phí
            'service_type_id' => 2, // E-commerce
        ];

        // Thêm thông tin items nếu có
        if ($request->has('items') && is_array($request->items)) {
            $orderData['items'] = [];
            foreach ($request->items as $item) {
                $orderData['items'][] = [
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'weight' => $item['weight'] ?? 200,
                    'length' => $item['length'] ?? 15,
                    'width' => $item['width'] ?? 15,
                    'height' => $item['height'] ?? 15
                ];
            }
        }

        $result = $this->ghnService->createShippingOrder($orderData);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'order_code' => $result['order_code'],
                'sort_code' => $result['sort_code'],
                'trans_type' => $result['trans_type'],
                'ward_encode' => $result['ward_encode'],
                'district_encode' => $result['district_encode'],
                'fee' => $result['fee'],
                'data' => $result['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'error' => $result['error']
        ], 400);
    }

    /**
     * Theo dõi đơn hàng
     */
    public function trackOrder(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string'
        ]);

        $result = $this->ghnService->trackOrder($request->order_code);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'error' => $result['error']
        ], 400);
    }
}
