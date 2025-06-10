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
        'product_id',  // ← Thêm field này
        'doctor_id',
        'commission',
        'commission_percentage',  // ← Nếu đã thêm vào DB
        'status',
        'paid_at',  // ← Nếu đã thêm vào DB
    ];

    protected $casts = [
        'commission' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function product()  // ← Thêm relationship này
    {
        return $this->belongsTo(Product::class);
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // ==================== STATIC METHODS ====================

    public static function countAffiliateOrders()
    {
        return self::count();
    }

    public static function totalAffiliateCommission($status = 'pending')
    {
        return self::where('status', $status)->sum('commission');
    }

    public static function totalPendingCommission()
    {
        return self::pending()->sum('commission');
    }

    public static function totalPaidCommission()
    {
        return self::paid()->sum('commission');
    }

    // ==================== INSTANCE METHODS ====================

    public function getFormattedCommission()
    {
        return number_format($this->commission, 0, ',', '.') . 'đ';
    }

    public function getStatusBadge()
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Chờ thanh toán</span>',
            'paid' => '<span class="badge badge-success">Đã thanh toán</span>',
            'cancelled' => '<span class="badge badge-danger">Đã hủy</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-secondary">Không xác định</span>';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

}