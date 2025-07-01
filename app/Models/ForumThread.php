<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumThread extends Model
{
    protected $table = 'forum_threads';
    
    protected $fillable = [
        'title',
        'slug', 
        'content',
        'category_id',
        'user_id',
        'is_sticky',
        'is_locked',
        'view_count',
        'reply_count',
        'last_posted_at',
        'last_posted_by'
    ];

    protected $casts = [
        'is_sticky' => 'boolean',
        'is_locked' => 'boolean',
        'view_count' => 'integer',
        'reply_count' => 'integer',
        'last_posted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // ===== QUAN HỆ DỮ LIỆU =====

    // Danh mục của thread
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Người tạo thread
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Người post cuối cùng
    public function lastPoster()
    {
        return $this->belongsTo(User::class, 'last_posted_by');
    }
    // ===== SCOPES =====

    // Lọc thread sticky (ghim)
    public function scopeSticky($query)
    {
        return $query->where('is_sticky', true);
    }

    // Lọc thread không bị khóa
    public function scopeNotLocked($query)
    {
        return $query->where('is_locked', false);
    }

    // Lọc theo category
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Sắp xếp theo độ hot (sticky trước, sau đó theo last_posted_at)
    public function scopeHot($query)
    {
        return $query->orderBy('is_sticky', 'desc')
                    ->orderBy('last_posted_at', 'desc');
    }

    // ===== STATIC METHODS =====

    // Lấy thread phổ biến nhất
    public static function getMostViewed($limit = 10)
    {
        return self::orderBy('view_count', 'desc')
                   ->limit($limit)
                   ->get();
    }

    // Lấy thread hot nhất (nhiều reply)
    public static function getMostReplied($limit = 10)
    {
        return self::orderBy('reply_count', 'desc')
                   ->limit($limit)
                   ->get();
    }

    // Tìm theo slug
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }

    // ===== HELPER METHODS =====

    // Tăng view count
    public function incrementViews()
    {
        $this->increment('view_count');
    }

    // Tăng reply count
    public function incrementReplies($userId = null)
    {
        $this->increment('reply_count');
        
        if ($userId) {
            $this->update([
                'last_posted_at' => now(),
                'last_posted_by' => $userId
            ]);
        }
    }

    // Check xem thread có bị khóa không
    public function isLocked()
    {
        return $this->is_locked;
    }

    // Check xem thread có được ghim không
    public function isSticky()
    {
        return $this->is_sticky;
    }

    // Format thời gian post cuối
    public function getLastPostedTimeAttribute()
    {
        return $this->last_posted_at ? $this->last_posted_at->diffForHumans() : null;
    }

    // ===== SLUG HELPER =====
    public static function createSlug($title)
    {
        $slug = \Str::slug($title);
        $originalSlug = $slug;
        $i = 1;

        // Kiểm tra và đảm bảo slug là duy nhất
        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        return $slug;
    }

}