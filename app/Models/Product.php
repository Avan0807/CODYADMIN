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
        'price', 'brand_id', 'discount', 'status', 'photo', 'size', 'stock',
        'is_featured', 'condition', 'commission_percentage',
        'reviews_count', 'reviews_avg_rate'
    ];

    protected $casts = [
        'price' => 'float',
        'discount' => 'float',
        'is_featured' => 'boolean',
        'stock' => 'integer',
        'commission_percentage' => 'float',
        'reviews_count' => 'integer',
        'reviews_avg_rate' => 'float',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ✅ Mối quan hệ n-n với bảng categories thông qua bảng category_product
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id')
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'id');
    }

    public static function getProductBySlug($slug)
    {
        return Product::with(['categories', 'reviews'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public static function getAllProduct()
    {
        return Product::with(['categories'])
            ->orderBy('id', 'desc')
            ->paginate(10);
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
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    // ✅ Cập nhật reviews count và rate
    public static function updateReviewStats($product_id)
    {
        $product = self::find($product_id);
        if ($product) {
            $count = $product->reviews()->count();
            $avg = $product->reviews()->avg('rate');
            $product->update([
                'reviews_count' => $count,
                'reviews_avg_rate' => round($avg, 2)
            ]);
        }
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function getRelatedProducts()
    {
        // Lấy danh mục đầu tiên
        $firstCategory = $this->categories()->first();

        // Nếu không có danh mục, thử tìm sản phẩm tương tự theo giá hoặc thương hiệu
        if (!$firstCategory) {
            return Product::where('id', '!=', $this->id)
                ->where('status', 'active')
                ->where(function($query) {
                    // Thử tìm sản phẩm cùng thương hiệu
                    if ($this->brand_id) {
                        $query->where('brand_id', $this->brand_id);
                    }
                    // Hoặc sản phẩm trong khoảng giá tương đương (±20%)
                    else {
                        $minPrice = $this->price * 0.8;
                        $maxPrice = $this->price * 1.2;
                        $query->whereBetween('price', [$minPrice, $maxPrice]);
                    }
                })
                ->latest()
                ->take(8)
                ->get();
        }

        // Nếu có danh mục, lấy sản phẩm cùng danh mục
        $relatedProducts = Product::whereHas('categories', function ($query) use ($firstCategory) {
                    $query->where('categories.id', $firstCategory->id);
                })
                ->where('id', '!=', $this->id)
                ->where('status', 'active')
                ->latest()
                ->take(8)
                ->get();

        // Nếu không tìm được sản phẩm cùng danh mục, lấy 8 sản phẩm mới nhất
        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::where('id', '!=', $this->id)
                ->where('status', 'active')
                ->latest()
                ->take(8)
                ->get();
        }

        return $relatedProducts;
    }
}
