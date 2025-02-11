<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use DB;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'sub_total', 'quantity', 'delivery_charge', 'status', 'total_amount',
        'first_name', 'last_name', 'country', 'post_code', 'address1', 'address2', 'phone', 'email', 
        'payment_method', 'payment_status', 'shipping_id', 'coupon'
    ];

    public function cart_info()
    {
        return $this->hasMany('App\Models\Cart', 'order_id', 'id');
    }

    public static function getAllOrder($id)
    {
        return Order::with('cart_info')->findOrFail($id);  // Dùng findOrFail thay vì find
    }

    public static function countOrdersByStatus()
    {
        return Cache::remember('order_status_counts', 60, function () {
            return Order::selectRaw('
                    status,
                    COUNT(*) as count
                ')
                ->whereIn('status', ['new', 'process', 'delivered', 'cancel'])
                ->groupBy('status')
                ->pluck('count', 'status');
        });
    }

    public static function countActiveOrder()
    {
        return Order::count() ?? 0;  // Đơn giản hóa và trả về 0 nếu không có dữ liệu
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public static function countNewReceivedOrder()
    {
        return Order::where('status', 'new')->count() ?? 0;  // Đơn giản hóa
    }

    public static function countProcessingOrder()
    {
        return Order::where('status', 'process')->count() ?? 0;
    }

    public static function countDeliveredOrder()
    {
        return Order::where('status', 'delivered')->count() ?? 0;
    }

    public static function countCancelledOrder()
    {
        return Order::where('status', 'cancel')->count() ?? 0;
    }
}
