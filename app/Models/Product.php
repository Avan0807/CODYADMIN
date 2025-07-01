<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductReview;
use App\Models\AffiliateOrder;

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

    // ==================== SCOPES ====================
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeWithCommission($query)
    {
        return $query->where('commission_percentage', '>', 0);
    }

    // ==================== RELATIONSHIPS ====================

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id')
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'id');
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

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    // ✅ THÊM MỚI: Relationship với affiliate orders
    public function affiliateOrders()
    {
        return $this->hasMany(AffiliateOrder::class, 'product_id', 'id');
    }

    public function affiliateLinks()
    {
        return $this->hasMany(AffiliateLink::class, 'product_id', 'id');
    }

    // ==================== STATIC METHODS ====================

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

    // ==================== INSTANCE METHODS ====================

    public function getRelatedProducts()
    {
        $firstCategory = $this->categories()->first();

        if (!$firstCategory) {
            return Product::where('id', '!=', $this->id)
                ->where('status', 'active')
                ->where(function($query) {
                    if ($this->brand_id) {
                        $query->where('brand_id', $this->brand_id);
                    } else {
                        $minPrice = $this->price * 0.8;
                        $maxPrice = $this->price * 1.2;
                        $query->whereBetween('price', [$minPrice, $maxPrice]);
                    }
                })
                ->latest()
                ->take(8)
                ->get();
        }

        $relatedProducts = Product::whereHas('categories', function ($query) use ($firstCategory) {
                    $query->where('categories.id', $firstCategory->id);
                })
                ->where('id', '!=', $this->id)
                ->where('status', 'active')
                ->latest()
                ->take(8)
                ->get();

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::where('id', '!=', $this->id)
                ->where('status', 'active')
                ->latest()
                ->take(8)
                ->get();
        }

        return $relatedProducts;
    }

    // ✅ THÊM MỚI: Methods cho affiliate system

    /**
     * Kiểm tra sản phẩm có hoa hồng không
     */
    public function hasCommission()
    {
        return $this->commission_percentage > 0;
    }

    /**
     * Tính hoa hồng cho một số tiền cụ thể
     */
    public function calculateCommission($amount)
    {
        if (!$this->hasCommission()) {
            return 0;
        }
        
        return ($amount * $this->commission_percentage) / 100;
    }

    /**
     * Lấy tổng hoa hồng đã sinh ra từ sản phẩm này
     */
    public function getTotalCommissionGenerated()
    {
        return $this->affiliateOrders()->sum('commission');
    }

    /**
     * Lấy số lượng đã bán (từ các đơn hàng delivered)
     */
    public function getTotalSold()
    {
        return $this->carts()
            ->whereHas('order', function($query) {
                $query->where('status', 'delivered');
            })
            ->sum('quantity');
    }

    /**
     * Format giá với discount
     */
    public function getFormattedPrice()
    {
        if ($this->discount > 0) {
            $discountedPrice = $this->price - ($this->price * $this->discount / 100);
            return [
                'original' => number_format($this->price, 0, ',', '.') . 'đ',
                'discounted' => number_format($discountedPrice, 0, ',', '.') . 'đ',
                'discount_percent' => $this->discount . '%'
            ];
        }
        
        return [
            'price' => number_format($this->price, 0, ',', '.') . 'đ'
        ];
    }

    /**
     * Kiểm tra sản phẩm còn hàng không
     */
    public function isInStock()
    {
        return $this->stock > 0;
    }

    /**
     * Lấy rating stars HTML
     */
    public function getStarsHtml()
    {
        $fullStars = floor($this->reviews_avg_rate);
        $halfStar = ($this->reviews_avg_rate - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
        
        $html = '';
        
        // Full stars
        for ($i = 0; $i < $fullStars; $i++) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        }
        
        // Half star
        if ($halfStar) {
            $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        }
        
        // Empty stars
        for ($i = 0; $i < $emptyStars; $i++) {
            $html .= '<i class="far fa-star text-warning"></i>';
        }
        
        return $html;
    }

    /**
     * Lấy commission badge HTML
     */
    public function getCommissionBadge()
    {
        if (!$this->hasCommission()) {
            return '';
        }
        
        return '<span class="badge badge-success">Hoa hồng ' . $this->commission_percentage . '%</span>';
    }

    // ==================== ACCESSORS ====================

    public function getPriceAttribute($value)
    {
        return (float) $value;
    }

    public function getDiscountPriceAttribute()
    {
        if ($this->discount > 0) {
            return $this->price - ($this->price * $this->discount / 100);
        }
        return $this->price;
    }

    public function getIsDiscountedAttribute()
    {
        return $this->discount > 0;
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->stock <= 5) {
            return 'low_stock';
        }
        return 'in_stock';
    }
    
    public function agentStocks()
    {
        return $this->hasMany(AgentProductStock::class);
    }

    public function agentStockHistories()
    {
        return $this->hasMany(AgentStockHistory::class);
    }


}