<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $fillable = [
        'title', 'slug', 'summary', 'description', 'cat_id', 'child_cat_id',
        'price', 'brand_id', 'discount', 'status', 'photo', 'size', 'stock',
        'is_featured', 'condition', 'commission_percentage'
    ];

    protected $casts = [
        'price' => 'float',
        'discount' => 'float',
        'is_featured' => 'boolean',
        'stock' => 'integer',
        'commission_percentage' => 'float',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function cat_info()
    {
        return $this->hasOne(Category::class, 'id', 'cat_id');
    }

    public function sub_cat_info()
    {
        return $this->hasOne(Category::class, 'id', 'child_cat_id');
    }

    public static function getAllProduct()
    {
        return Product::with(['cat_info', 'sub_cat_info'])
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function rel_prods()
    {
        return $this->hasMany(Product::class, 'cat_id', 'cat_id')
            ->where('status', 'active')
            ->orderBy('id', 'DESC')
            ->limit(8);
    }

    public function getReview()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'id')->with('user_info');
    }

    public static function getProductBySlug($slug)
    {
        return Product::with(['cat_info', 'rel_prods', 'getReview'])
            ->where('slug', $slug)
            ->firstOrFail();
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
