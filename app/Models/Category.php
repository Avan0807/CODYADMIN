<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'type', 'parent_id', 'icon', 'status',
        'display_order', 'summary', 'photo'
    ];

    // ===== QUAN HỆ PHÂN CẤP =====

    // Danh mục cha (1-1)
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Danh mục con (1-n)
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // ===== QUAN HỆ DỮ LIỆU LIÊN KẾT =====

    // Sản phẩm thuộc danh mục
    public function products()
    {
        return $this->hasMany(Product::class, 'cat_id','id');
    }

    // Bài viết thuộc danh mục
    public function posts()
    {
        return $this->hasMany(Post::class, 'post_cat_id');
    }

    // Bác sĩ thuộc chuyên khoa
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'specialist_cat_id');
    }
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'category_brand', 'category_id', 'brand_id')
                    ->select('brands.id', 'brands.title', 'brands.slug', 'brands.logo')
                    ->withTimestamps();
    }


    // ===== SCOPES =====

    // Lọc theo loại danh mục
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Lọc danh mục gốc
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // ===== STATIC METHODS =====

    // Lấy toàn bộ danh mục có danh mục con (dùng cho admin, treeview)
    public static function getTree($type = null)
    {
        $query = self::with('children')->whereNull('parent_id');
        if ($type) {
            $query->where('type', $type);
        }
        return $query->orderBy('display_order')->get();
    }

    // Đếm tổng số danh mục theo type
    public static function countByType($type)
    {
        return self::where('type', $type)->count();
    }

    // Tìm theo slug
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }

    public static function getAllParentWithChild()
    {
        return self::whereNull('parent_id')
            ->with(['children.children']) // preload luôn cấp 2
            ->where('status', 'active')
            ->orderBy('display_order')
            ->get();
    }


    public function child_cat()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id')->where('status', 'active');
    }
}
