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
        'rating', 'consultation_fee', 'bio', 'password', 'points'
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

    // 🔹 Bổ sung quan hệ với `Appointment`
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
    public function affiliateOrders()
    {
        return $this->hasMany(AffiliateOrder::class);
    }
    // Trong model Doctor
    public function reviews()
    {
        return $this->hasMany(DoctorReview::class);
    }
    public function doctorFollowers()
    {
        return $this->hasMany(DoctorFollower::class, 'doctor_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'doctor_followers', 'doctor_id', 'user_id');
    }

}
