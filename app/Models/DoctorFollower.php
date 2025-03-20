<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorFollower extends Model
{
    protected $table = 'doctor_followers';

    protected $fillable = [
        'user_id',
        'doctor_id',
    ];

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Quan hệ với Doctor
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
