<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Doctor extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $table = 'doctors';

    protected $fillable = [
        'name', 'specialization', 'services', 'experience', 'working_hours',
        'location', 'workplace', 'phone', 'email', 'photo', 'status',
        'rating', 'consultation_fee', 'bio', 'password', 'points', 'short_bio',
        'total_commission'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'consultation_fee' => 'decimal:2',
        'experience' => 'integer',
        'points' => 'integer',
        'total_commission' => 'double',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // 🔹 Quan hệ với lịch hẹn
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    // 🔹 Quan hệ với đơn affiliate
    public function affiliateOrders()
    {
        return $this->hasMany(AffiliateOrder::class);
    }

    // 🔹 Quan hệ với đánh giá bác sĩ
    public function reviews()
    {
        return $this->hasMany(DoctorReview::class);
    }

    // 🔹 Quan hệ followers (nhiều user theo dõi 1 bác sĩ)
    public function doctorFollowers()
    {
        return $this->hasMany(DoctorFollower::class, 'doctor_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'doctor_followers', 'doctor_id', 'user_id');
    }

    // 🔹 Quan hệ với các chuyên khoa (nhiều-nhiều với categories)
    public function specializations()
    {
        return $this->belongsToMany(Category::class, 'doctor_specializations', 'doctor_id', 'category_id');
    }

    // 🔹 Quan hệ các buổi tư vấn / meeting
    public function meetings()
    {
        return $this->morphMany(Meeting::class, 'created_by');
    }
}
