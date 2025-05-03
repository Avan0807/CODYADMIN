<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;
    protected $table = 'meetings';
    protected $fillable = [
        'title',
        'description',
        'meet_link',
        'start_time',
        'end_time',
        'created_by_id',
        'created_by_type',
    ];
    
    // Quan hệ đa hình để xác định người tạo cuộc họp
    public function createdBy()
    {
        return $this->morphTo();
    }
    
    // Vẫn giữ lại quan hệ cũ với Doctor để đảm bảo tương thích ngược
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'created_by_id')
            ->where('created_by_type', Doctor::class);
    }
    
    // Thêm quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_id')
            ->where('created_by_type', User::class);
    }
}