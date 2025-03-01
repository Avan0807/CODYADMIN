<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments'; // Tên bảng trong DB

    protected $fillable = [
        'doctor_id',
        'user_id',
        'date',
        'time',
        'status',
        'approval_status',
        'notes',
        'consultation_type',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i:s',
        'status' => 'string',
        'approval_status' => 'string',
    ];

    /**
     * Mối quan hệ với bảng Doctors
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Mối quan hệ với bảng Users
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
