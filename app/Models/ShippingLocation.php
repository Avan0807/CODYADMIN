<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_id',
        'from_province_id',
        'to_province_id',
        'price',
        'weight_price'
    ];

    /**
     * Lấy phương thức vận chuyển
     */
    public function shipping()
    {
        return $this->belongsTo(Shipping::class);
    }

    /**
     * Lấy tỉnh/thành phố gửi hàng
     */
    public function fromProvince()
    {
        return $this->belongsTo(Province::class, 'from_province_id');
    }

    /**
     * Lấy tỉnh/thành phố nhận hàng
     */
    public function toProvince()
    {
        return $this->belongsTo(Province::class, 'to_province_id');
    }

    /**
     * Tính phí vận chuyển dựa trên cân nặng
     */
    public function calculateCost($weight = 1)
    {
        // Phí cơ bản + phí theo cân nặng nếu > 1kg
        return $this->price + (($weight > 1) ? ($weight - 1) * $this->weight_price : 0);
    }

    /**
     * Định dạng phí vận chuyển
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.') . 'đ';
    }

    /**
     * Định dạng phí theo cân nặng
     */
    public function getFormattedWeightPriceAttribute()
    {
        return number_format($this->weight_price, 0, ',', '.') . 'đ';
    }
}
