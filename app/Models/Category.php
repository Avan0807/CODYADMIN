<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cache;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'type', 'parent_id', 'icon', 'status',
        'display_order', 'summary', 'photo'
    ];

    // ===== QUAN HỆ PHÂN CẤP =====

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function child_cat()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id')->where('status', 'active');
    }

    // ===== QUAN HỆ DỮ LIỆU LIÊN KẾT =====

    public function products()
    {
        return $this->hasMany(Product::class, 'cat_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'post_cat_id');
    }

    // ✅ Quan hệ với bác sĩ qua bảng trung gian
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_specializations', 'specialization_id', 'doctor_id')
                    ->withTimestamps();
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'category_brand', 'category_id', 'brand_id')
                    ->select('brands.id', 'brands.title', 'brands.slug', 'brands.logo')
                    ->withTimestamps();
    }

    // ===== SCOPES =====

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSpecialization($query)
    {
        return $query->where('type', 'specialization');
    }

    // ===== STATIC METHODS =====

    public static function getTree($type = null)
    {
        $query = self::with('children')->whereNull('parent_id');
        if ($type) {
            $query->where('type', $type);
        }
        return $query->orderBy('display_order')->get();
    }

    public static function countByType($type)
    {
        return self::where('type', $type)->count();
    }

    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }

    public static function getAllParentWithChild()
    {
        return self::whereNull('parent_id')
            ->with(['children.children'])
            ->where('status', 'active')
            ->orderBy('display_order')
            ->get();
    }

    public static function countActiveCategory()
    {
        return Cache::remember('active_categories_count', 60, function () {
            return self::where('status', 'active')->count();
        });
    }

    public static function getAllCategory()
    {
        return self::with('parent')->orderBy('id', 'DESC')->get();
    }
}
