<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductReview;

class Product extends Model
{
    protected $fillable = [
        'title', 'slug', 'summary', 'description',
        'photo', 'stock', 'size', 'condition', 'status',
        'price', 'discount', 'is_featured',
        'cat_id', 'child_cat_id', 'brand_id',
        'commission_percentage',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'float',
        'is_featured' => 'boolean',
        'stock' => 'integer',
        'commission_percentage' => 'float',
    ];

    // Scope chỉ lấy sản phẩm đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Quan hệ với danh mục chính
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    // Quan hệ với danh mục con
    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'child_cat_id');
    }

    // Quan hệ với thương hiệu
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    // Quan hệ với đánh giá sản phẩm
    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id')->with('user_info');
    }

    // Quan hệ với giỏ hàng
    public function carts()
    {
        return $this->hasMany(Cart::class)->whereNotNull('order_id');
    }

    // Quan hệ với wishlist
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class)->whereNotNull('cart_id');
    }

    // Sản phẩm liên quan trong cùng danh mục
    public function relatedProducts()
    {
        return $this->hasMany(Product::class, 'cat_id', 'cat_id')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->limit(8);
    }

    // Lấy toàn bộ sản phẩm kèm danh mục & sub
    public static function getAllProduct()
    {
        return self::with(['category', 'subCategory'])
            ->orderByDesc('id')
            ->paginate(10);
    }

    // Lấy chi tiết sản phẩm theo slug
    public static function getProductBySlug($slug)
    {
        return self::with(['category', 'relatedProducts', 'reviews'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    // Đếm tổng số sản phẩm đang hoạt động (cache 60s)
    public static function countActiveProduct()
    {
        return Cache::remember('active_products_count', 60, function () {
            return self::where('status', 'active')->count();
        });
    }
    public static function getApiProducts($limit = 10)
    {
        return self::with(['category', 'subCategory'])
            ->active()
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

}
