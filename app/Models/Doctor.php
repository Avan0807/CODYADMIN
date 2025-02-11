<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // 🔹 Đổi từ Model sang Authenticatable
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Authenticatable // 🔹 Kế thừa Authenticatable để hỗ trợ đăng nhập
{
    use HasFactory, HasApiTokens; // 🔹 Dùng Sanctum để tạo token

    protected $table = 'doctors';

    protected $fillable = [
        'name', 'specialization', 'services', 'experience', 'working_hours',
        'location', 'workplace', 'phone', 'email', 'photo', 'status',
        'rating', 'consultation_fee', 'bio', 'password', 'points'
    ];

    protected $hidden = [
        'password',
    ];
}
