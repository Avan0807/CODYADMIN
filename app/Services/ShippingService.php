<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Services\GHNService;

class ShippingService
{
    public function calculate(Request $request, $carts)
    {
        $weight = $carts->sum('quantity') * 200;

        $fromDistrictId = 1493;
        $toDistrictId = $request->ghn_to_district_id;

        // 1. Tự lấy danh sách service khả dụng
        $availableServices = app(GHNService::class)->getServices(
            config('services.ghn.shop_id'),
            $fromDistrictId,
            $toDistrictId
        );

        if (empty($availableServices)) {
            return [
                'success' => false,
                'message' => 'Không lấy được danh sách dịch vụ GHN'
            ];
        }

        // 2. Chọn service mặc định (lấy cái đầu tiên)
        $serviceId = $availableServices[0]['service_id'];

        $params = [
            'from_district_id' => $fromDistrictId,
            'to_district_id' => $toDistrictId,
            'to_ward_code' => $request->ghn_to_ward_code,
            'weight' => $weight,
            'service_id' => $serviceId,
        ];

        $result = app(GHNService::class)->calculateShippingFee($params);

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Không thể tính phí vận chuyển'
            ];
        }

        return [
            'success' => true,
            'shipping_fee' => $result['total_fee'],
            'service_id' => $serviceId,
            'service_name' => $this->getServiceNameById($availableServices, $serviceId)
        ];
    }

}
