<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'sub_total',
        'quantity',
        'shipping_id',
        'status',
        'total_amount',
        'first_name',
        'last_name',
        'country',
        'post_code',
        'address1',
        'address2',
        'phone',
        'email',
        'payment_method',
        'payment_status',
        'coupon',
        'doctor_id',
        'commission',
        'created_at',
        'updated_at',
        'shipping_cost',
        // ✅ GHN Fields mới
        'ghn_order_code',
        'ghn_status',
        'ghn_tracking_url',
        'ghn_to_province_id', 
        'ghn_to_district_id',
        'ghn_to_ward_code',
        'ghn_service_id'
    ];

    /**
     * ✅ Data type casting
     */
    protected $casts = [
        'shipping_cost' => 'float',    // Đổi từ decimal:2 sang float
        'sub_total' => 'float',        // Đổi từ decimal:2 sang float  
        'total_amount' => 'float',     // Đổi từ decimal:2 sang float
        'coupon' => 'float',           // Đổi từ decimal:2 sang float
        'commission' => 'float',       // Đổi từ decimal:2 sang float
        'quantity' => 'integer',       // Thêm quantity
        'ghn_to_district_id' => 'integer',
        'ghn_service_id' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    public function cartInfo()
    {
        return $this->hasMany(Cart::class, 'order_id', 'id');
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ==================== STATIC METHODS (Legacy) ====================

    public static function getAllOrder($id)
    {
        return self::with('cartInfo')->find($id);
    }

    public static function countActiveOrder()
    {
        return self::count();
    }

    public static function countNewReceivedOrder()
    {
        return self::where('status', 'new')->count();
    }

    public static function countProcessingOrder()
    {
        return self::where('status', 'process')->count();
    }

    public static function countDeliveredOrder()
    {
        return self::where('status', 'delivered')->count();
    }

    public static function countCancelledOrder()
    {
        return self::where('status', 'cancel')->count();
    }

    // ==================== GHN METHODS ⭐ ====================

    /**
     * ✅ Kiểm tra đơn hàng có dùng GHN không
     */
    public function isGHNOrder()
    {
        return !empty($this->ghn_order_code);
    }

    /**
     * ✅ Lấy phương thức vận chuyển
     */
    public function getShippingMethod()
    {
        return 'GHN - ' . ucfirst($this->ghn_status ?? 'pending');
    }

    /**
     * ✅ Lấy link tracking
     */
    public function getTrackingUrl()
    {
        if ($this->isGHNOrder() && $this->ghn_tracking_url) {
            return $this->ghn_tracking_url;
        }
        
        if ($this->isGHNOrder() && $this->ghn_order_code) {
            return "https://donhang.ghn.vn/?order_code=" . $this->ghn_order_code;
        }
        
        return null;
    }

    /**
     * ✅ Lấy trạng thái shipping
     */
    public function getShippingStatus()
    {
        if ($this->isGHNOrder()) {
            return $this->ghn_status ?? 'pending';
        }
        
        return $this->status; // Legacy status
    }

    /**
     * ✅ Địa chỉ đầy đủ
     */
    public function getFullAddress()
    {
        $address = $this->address1;
        
        if ($this->address2) {
            $address .= ', ' . $this->address2;
        }
        
        return $address;
    }

    /**
     * ✅ Kiểm tra có thể track GHN không
     */
    public function canTrackGHN()
    {
        return $this->isGHNOrder() && !empty($this->ghn_order_code);
    }

    /**
     * ✅ Format shipping cost
     */
    public function getFormattedShippingCost()
    {
        return number_format($this->shipping_cost, 0, ',', '.') . 'đ';
    }


    // ==================== SCOPES ⭐ ====================

    /**
     * ✅ Scope: Chỉ orders GHN
     */
    public function scopeGhnOrders($query)
    {
        return $query->whereNotNull('ghn_order_code');
    }

    /**
     * ✅ Scope: Chỉ orders legacy
     */
    public function scopeLegacyOrders($query)
    {
        return $query->whereNull('ghn_order_code');
    }

    /**
     * ✅ Scope: Orders theo GHN status
     */
    public function scopeByGhnStatus($query, $status)
    {
        return $query->where('ghn_status', $status);
    }

        // Cache location names để tránh gọi API nhiều lần
    protected $ghnLocationCache = [];

    /**
     * Lấy tất cả tên địa điểm GHN (có cache)
     */
    public function getGHNLocationNames()
    {
        if (empty($this->ghnLocationCache)) {
            $ghn = app(\App\Services\GHNService::class);
            
            $provinceName = 'N/A';
            $districtName = 'N/A';
            $wardName = 'N/A';

            try {
                // Lấy tên tỉnh
                if ($this->ghn_province_id) {
                    $provinces = $ghn->getProvinces();
                    $province = collect($provinces)->firstWhere('ProvinceID', $this->ghn_province_id);
                    $provinceName = $province['ProvinceName'] ?? 'Unknown Province';
                }

                // Lấy tên quận/huyện
                if ($this->ghn_district_id && $this->ghn_province_id) {
                    $districts = $ghn->getDistricts($this->ghn_province_id);
                    $district = collect($districts)->firstWhere('DistrictID', $this->ghn_district_id);
                    $districtName = $district['DistrictName'] ?? 'Unknown District';
                }

                // Lấy tên phường/xã
                if ($this->ghn_ward_code && $this->ghn_district_id) {
                    $wards = $ghn->getWards($this->ghn_district_id);
                    $ward = collect($wards)->firstWhere('WardCode', $this->ghn_ward_code);
                    $wardName = $ward['WardName'] ?? 'Unknown Ward';
                }

            } catch (\Exception $e) {
                \Log::error('Error getting GHN location names: ' . $e->getMessage());
            }

            $this->ghnLocationCache = [
                'province' => $provinceName,
                'district' => $districtName,
                'ward' => $wardName,
                'full_address' => $wardName . ', ' . $districtName . ', ' . $provinceName
            ];
        }

        return $this->ghnLocationCache;
    }

    /**
     * Shortcut methods
     */
    public function getGHNProvinceName()
    {
        return $this->getGHNLocationNames()['province'];
    }

    public function getGHNDistrictName()
    {
        return $this->getGHNLocationNames()['district'];
    }

    public function getGHNWardName()
    {
        return $this->getGHNLocationNames()['ward'];
    }

    public function getGHNFullAddress()
    {
        return $this->getGHNLocationNames()['full_address'];
    }

}