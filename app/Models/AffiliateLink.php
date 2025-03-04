<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    use HasFactory;

    protected $table = 'affiliate_links'; // Tên bảng trong database

    protected $primaryKey = 'id'; // Khóa chính

    public $timestamps = true; // Cho phép timestamps

    protected $fillable = [
        'doctor_id',
        'product_id',
        'hash_ref',
        'product_link',  // ✅ Thêm trường lưu link sản phẩm
    ];

    /**
     * Liên kết với model Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Liên kết với model Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Tạo link affiliate mới với mã hash bảo mật + lưu link + % hoa hồng
     */
    public static function createAffiliateLink($doctorId, $productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return null; // Nếu không tìm thấy sản phẩm, không tạo link
        }

        $hashRef = hash('sha256', $doctorId . $productId . time()); // Mã hash an toàn
        $productLink = url("http://toikhoe.vn/product-detail/{$product->slug}?ref={$hashRef}");
        $commissionPercentage = 10.00; // Giá trị mặc định, có thể thay đổi nếu cần

        return self::create([
            'doctor_id' => $doctorId,
            'product_id' => $productId,
            'hash_ref' => $hashRef,
            'product_link' => $productLink,
        ]);
    }

    /**
     * Kiểm tra xem link affiliate có tồn tại không
     */
    public static function isAffiliateValid($doctorId, $productId)
    {
        return self::where('doctor_id', $doctorId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Tìm Affiliate theo hash_ref
     */
    public static function findByHash($hashRef)
    {
        return self::where('hash_ref', $hashRef)->first();
    }
}
