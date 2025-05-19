<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = ['title', 'slug', 'status'];

    // Liên kết: 1 brand -> nhiều product
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id', 'id')->where('status', 'active');
    }

    // Liên kết: nhiều brand -> nhiều category (qua bảng trung gian)
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_brand', 'brand_id', 'category_id')
                    ->withTimestamps();
    }

    // Hàm lấy brand kèm sản phẩm
    public static function getProductByBrand($slug)
    {
        return self::with('products')->where('slug', $slug)->first();
    }
        protected static function booted()
    {
        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->title);
            }
        });

        static::updating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->title);
            }
        });
    }
}
