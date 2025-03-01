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

}
