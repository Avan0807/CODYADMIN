<?php
// Copy code này vào app/Services/GHNService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GHNService
{
    private $token;
    private $shopId;
    private $apiUrl;

    public function __construct()
    {
        $this->token = config('services.ghn.token');
        $this->shopId = config('services.ghn.shop_id');
        $this->apiUrl = config('services.ghn.api_url');
    }

    /**
     * Lấy danh sách tỉnh/thành phố
    */
    public function getProvinces()
    {
        try {
            $response = Http::withHeaders([
                'Token' => $this->token,
                'Content-Type' => 'application/json'
            ])->get($this->apiUrl . '/master-data/province');

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                
                // DEBUG: Log để xem full structure
                \Log::info('GHN Raw Response:', $data);
                
                return $data;
            }

            Log::error('GHN Get Provinces Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('GHN Get Provinces Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách quận/huyện theo tỉnh
     */
    public function getDistricts($provinceId)
    {
        try {
            $response = Http::withHeaders([
                'Token' => $this->token,
                'Content-Type' => 'application/json'
            ])->get($this->apiUrl . '/master-data/district', [
                'province_id' => $provinceId
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::error('GHN Get Districts Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('GHN Get Districts Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách phường/xã theo quận
     */
    public function getWards($districtId)
    {
        try {
            $response = Http::withHeaders([
                'Token' => $this->token,
                'Content-Type' => 'application/json'
            ])->get($this->apiUrl . '/master-data/ward', [
                'district_id' => $districtId
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::error('GHN Get Wards Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('GHN Get Wards Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách dịch vụ giao hàng - DÙNG V2
     */
    public function getServices($shopId, $fromDistrictId, $toDistrictId)
    {
        $response = Http::withHeaders([
            'Token' => $this->token,
            'ShopId' => $shopId,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/v2/shipping-order/available-services', [
            'shop_id' => (int)$shopId,
            'from_district' => (int)$fromDistrictId,
            'to_district' => (int)$toDistrictId
        ]);

        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }

        \Log::error('GHN Get Services Failed:', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return [];
    }


    /**
     * Tính phí vận chuyển - DÙNG V2 VÀ FALLBACK V1
     */
    public function calculateShippingFee($params)
    {
        try {
            $defaultParams = [
                'service_id' => null,
                'insurance_value' => 0,
                'coupon' => null,
                'from_district_id' => null,
                'to_district_id' => null,
                'to_ward_code' => null,
                'height' => 15,
                'length' => 15,
                'weight' => 200,
                'width' => 15,
            ];

            $requestParams = array_merge($defaultParams, $params);

            Log::info('[GHN][Fee] Request params:', $requestParams);

            // Thử API v2 trước
            $response = Http::withHeaders([
                'Token' => $this->token,
                'ShopId' => (string)$this->shopId,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiUrl . '/v2/shipping-order/fee', $requestParams);  // ← GIỮ /v2

            Log::info('[GHN][Fee] V2 Response:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            // Nếu v2 thất bại, thử v1
            if (!$response->successful()) {
                Log::info('[GHN][Fee] V2 failed, trying V1...');

                $response = Http::withHeaders([
                    'Token' => $this->token,
                    'ShopId' => (string)$this->shopId,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($this->apiUrl . '/shipping-order/fee', $requestParams);  // ← V1 KHÔNG CÓ /v2

                Log::info('[GHN][Fee] V1 Response:', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
            }

            if ($response->successful()) {
                $responseData = $response->json();

                // Kiểm tra response code
                if (isset($responseData['code']) && $responseData['code'] !== 200) {
                    Log::error('[GHN][Fee] API Error:', $responseData);
                    return [
                        'success' => false,
                        'message' => $responseData['message'] ?? 'API Error',
                        'error' => $responseData
                    ];
                }

                $data = $responseData['data'] ?? [];
                return [
                    'success' => true,
                    'total_fee' => $data['total'] ?? 0,
                    'service_fee' => $data['service_fee'] ?? 0,
                    'insurance_fee' => $data['insurance_fee'] ?? 0,
                    'pickup_fee' => $data['pick_station_fee'] ?? 0,
                    'coupon_value' => $data['coupon_value'] ?? 0,
                    'r2s_fee' => $data['r2s_fee'] ?? 0,
                    'data' => $data
                ];
            }

            Log::error('[GHN][Fee] HTTP Error:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'HTTP Error: ' . $response->status(),
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('[GHN][Fee] Exception: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Lỗi hệ thống khi tính phí vận chuyển',
                'error' => $e->getMessage()
            ];
        }
    }
    /**
     * Tạo đơn hàng vận chuyển
     */
    public function createShippingOrder($orderData)
    {
        try {
            $defaultData = [
                'payment_type_id' => 1, // 1: Shop/Seller trả phí, 2: Người nhận trả phí
                'note' => '',
                'required_note' => 'KHONGCHOXEMHANG', // CHOTHUHANG, CHOXEMHANGKHONGTHU, KHONGCHOXEMHANG
                'return_phone' => '',
                'return_address' => '',
                'return_district_id' => null,
                'return_ward_code' => '',
                'client_order_code' => '',
                'from_name' => '',
                'from_phone' => '',
                'from_address' => '',
                'from_ward_name' => '',
                'from_district_name' => '',
                'from_province_name' => '',
                'to_name' => '',
                'to_phone' => '',
                'to_address' => '',
                'to_ward_code' => '',
                'to_district_id' => null,
                'cod_amount' => 0,
                'content' => '',
                'weight' => 200,
                'length' => 15,
                'width' => 15,
                'height' => 15,
                'pick_station_id' => null,
                'insurance_value' => 0,
                'service_id' => 0,
                'service_type_id' => 2, // 2: E-commerce
                'items' => []
            ];

            $requestData = array_merge($defaultData, $orderData);

            $response = Http::withHeaders([
                'Token' => $this->token,
                'ShopId' => $this->shopId,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/shipping-order/create', $requestData);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                return [
                    'success' => true,
                    'order_code' => $data['order_code'] ?? '',
                    'sort_code' => $data['sort_code'] ?? '',
                    'trans_type' => $data['trans_type'] ?? '',
                    'ward_encode' => $data['ward_encode'] ?? '',
                    'district_encode' => $data['district_encode'] ?? '',
                    'fee' => $data['fee'] ?? [],
                    'data' => $data
                ];
            }

            Log::error('GHN Create Order Error: ' . $response->body());
            return [
                'success' => false,
                'message' => 'Không thể tạo đơn hàng vận chuyển',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('GHN Create Order Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống khi tạo đơn hàng',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Theo dõi đơn hàng
     */
    public function trackOrder($orderCode)
    {
        try {
            $response = Http::withHeaders([
                'Token' => $this->token,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/shipping-order/detail', [
                'order_code' => $orderCode
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data'] ?? []
                ];
            }

            Log::error('GHN Track Order Error: ' . $response->body());
            return [
                'success' => false,
                'message' => 'Không thể theo dõi đơn hàng',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('GHN Track Order Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống khi theo dõi đơn hàng',
                'error' => $e->getMessage()
            ];
        }
    }

    public function testGHN(GHNService $ghn)
    {
        $provinces = $ghn->getProvinces();
        dd($provinces);
    }
}
