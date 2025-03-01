<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $table = 'clinics'; // Bảng tương ứng trong database

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website'
    ];

    // Nếu có quan hệ với bác sĩ hoặc lịch hẹn, có thể thêm vào đây
}
