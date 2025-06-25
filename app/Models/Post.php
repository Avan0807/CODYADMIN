<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title', 'tags', 'summary', 'slug', 'description', 'photo', 'quote',
        'post_cat_id', 'post_tag_id', 'added_by', 'status', 'is_featured', 'author_type', 'post_type', 'meta_data', 'views'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'meta_data' => 'array',
    ];


    /**
     * Quan hệ đến danh mục (sử dụng bảng categories).
    */
    public function category()
    {
        return $this->belongsTo(Category::class, 'post_cat_id')->withDefault([
            'name' => 'Không có danh mục'
        ]);
    }



    /**
     * Alias cho category nếu cần dùng tên cũ.
     */
    public function cat_info()
    {
        return $this->category();
    }

    /**
     * Quan hệ đến thẻ tag (1 bài viết có thể có nhiều tags).
     */
    public function tags()
    {
        return $this->belongsToMany(PostTag::class, 'post_tags', 'post_id', 'tag_id');
    }

    /**
     * Tag chính (nếu dùng post_tag_id đơn lẻ).
     */
    public function tag_info()
    {
        return $this->hasOne(PostTag::class, 'id', 'post_tag_id');
    }

    /**
     * Bài viết do User tạo (nếu không phải bác sĩ).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Bài viết do Bác sĩ tạo.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'added_by');
    }

    protected $appends = ['author_info'];
    /**
     * Ưu tiên trả về thông tin người tạo (doctor hoặc user).
     */
    public function getAuthorInfoAttribute()
    {
        // Ưu tiên Doctor trước
        if ($this->doctor) {
            return $this->doctor;
        }

        // Nếu không có doctor, kiểm tra user
        return $this->user;
    }

    /**
     * Lấy tất cả bài viết (dùng trong dashboard).
     */
    public static function getAllPost()
    {
        return self::with(['cat_info', 'user', 'doctor'])->orderBy('id', 'DESC')->paginate(10);
    }

    /**
     * Lấy bài viết theo slug.
     */
    public static function getPostBySlug($slug)
    {
        return self::with(['tag_info', 'author_info'])->where('slug', $slug)->where('status', 'active')->first();
    }

    /**
     * Quan hệ đến User trong bình luận.
     */
    public function user_info()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ đến Doctor trong bình luận.
     */
    public function doctor_info()
    {
        return $this->hasOne(Doctor::class, 'id', 'added_by');
    }


    /**
     * Các phản hồi trong bình luận.
     */
    public function replies()
    {
        return $this->hasMany(PostComment::class, 'parent_id')
            ->where('status', 'active')
            ->with(['user_info:id,name,email,phone', 'doctor_info:id,name,email,phone']);
    }

    /**
     * Danh sách bình luận cấp 1.
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class)
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->with(['user_info:id,name,email,phone', 'doctor_info:id,name,email,phone', 'replies'])
            ->orderBy('id', 'DESC');
    }

    /**
     * Tất cả bình luận (không phân cấp).
     */
    public function allComments()
    {
        return $this->hasMany(PostComment::class)->where('status', 'active');
    }

    /**
     * Lấy bài viết theo tag (slug).
     */
    public static function getBlogByTag($slug)
    {
        return self::where('tags', $slug)->paginate(8);
    }

    /**
     * Đếm số lượng bài viết đang hoạt động.
     */
    public static function countActivePost()
    {
        return self::where('status', 'active')->count() ?? 0;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'active')->whereNotNull('published_at');
    }

    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_post', 'post_id', 'clinic_id');
    }

}
