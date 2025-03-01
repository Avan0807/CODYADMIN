<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateOrder extends Model
{
    use HasFactory;

    protected $table = 'affiliate_orders';

    protected $fillable = [
        'order_id',
        'doctor_id',
        'commission',
        'status',
    ];

    /**
     * Định nghĩa quan hệ với bảng `orders`
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Định nghĩa quan hệ với bảng `doctors`
     */
    public function doctor()
    {
        return $this->hasOneThrough(
            Doctor::class,
            Order::class,
            'id', // Khóa chính của bảng `orders`
            'id', // Khóa chính của bảng `doctors`
            'order_id', // Khóa ngoại trong bảng `affiliate_orders` trỏ đến `orders`
            'doctor_id' // Khóa ngoại trong bảng `orders` trỏ đến `doctors`
        );
    }
    protected $casts = [
        'status' => 'string', // Chuyển ENUM thành string khi truy xuất
    ];

    /**
     * Đếm tổng số đơn hàng tiếp thị
     */
    public static function countAffiliateOrders()
    {
        return self::count();
    }

    /**
     * Tính tổng hoa hồng từ Affiliate Orders
     */
    public static function totalAffiliateCommission()
    {
        return self::where('status', 'pending')->sum('commission');
    }


}
