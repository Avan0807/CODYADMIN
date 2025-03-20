<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DoctorReview extends Model
{
    use HasFactory;

    protected $table = 'doctor_reviews';

    protected $fillable = [
        'doctor_id',
        'user_id',
        'rating',
        'review',
    ];

    // Trong model DoctorReview
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

}
