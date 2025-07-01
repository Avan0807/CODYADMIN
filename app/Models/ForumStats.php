<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/ForumStats.php

class ForumStats extends Model
{
    protected $table = 'forum_stats';
    protected $primaryKey = 'category_id';
    public $incrementing = false;
    public $timestamps = false; // 🛑 Tắt tự động cập nhật created_at và updated_at

    protected $fillable = [
        'category_id',
        'thread_count',
        'post_count',
        'last_thread_id',
        'last_post_id',
        'last_posted_at',
        'last_posted_by',
    ];
}

