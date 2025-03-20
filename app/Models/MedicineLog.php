<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineLog extends Model
{
    use HasFactory;

    // Đặt tên bảng (Laravel sẽ tự động tìm bảng theo tên model, nhưng ở đây chúng ta chỉ rõ tên bảng)
    protected $table = 'medicine_logs';

    // Các trường có thể gán đại trà (mass assignable)
    protected $fillable = [
        'product_id',   // ID sản phẩm mà thông báo áp dụng
        'notification_message',  // Nội dung thông báo nhắc nhở
    ];

    // Các trường không thể gán đại trà (mass assignable)
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Đặt các kiểu dữ liệu cho trường timestamp
    protected $dates = ['created_at', 'updated_at'];

    // Mối quan hệ với bảng 'products'
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
