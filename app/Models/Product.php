<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\Cart;

class Product extends Model
{
    protected $fillable = [
        'title', 'slug', 'summary', 'description', 'cat_id', 'child_cat_id', 'price', 'brand_id', 'discount',
        'status', 'photo', 'size', 'stock', 'is_featured', 'condition'
    ];

    public function cat_info()
    {
        return $this->hasOne('App\Models\Category', 'id', 'cat_id');
    }

    public function sub_cat_info()
    {
        return $this->hasOne('App\Models\Category', 'id', 'child_cat_id');
    }

    public static function getAllProduct()
    {
        return Product::with(['cat_info', 'sub_cat_info', 'rel_prods', 'getReview'])
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function rel_prods()
    {
        return $this->hasMany('App\Models\Product', 'cat_id', 'cat_id')
            ->where('status', 'active')
            ->orderBy('id', 'DESC')
            ->take(8);  // Sử dụng take thay vì limit
    }

    public function getReview()
    {
        return $this->hasMany('App\Models\ProductReview', 'product_id', 'id')
            ->with('user_info')
            ->where('status', 'active')
            ->orderBy('id', 'DESC')
            ->take(8);  // Giới hạn số lượng đánh giá
    }

    public static function getProductBySlug($slug)
    {
        return Product::with(['cat_info', 'rel_prods', 'getReview'])
            ->where('slug', $slug)
            ->firstOrFail();  // Dùng firstOrFail để tự động xử lý khi không tìm thấy
    }

    public static function countActiveProduct()
    {
        return Cache::remember('active_products_count', 60, function () {
            return Product::where('status', 'active')->count();
        });
    }

    public function carts()
    {
        return $this->hasMany(Cart::class)->whereNotNull('order_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class)->whereNotNull('cart_id');
    }

    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }
}
