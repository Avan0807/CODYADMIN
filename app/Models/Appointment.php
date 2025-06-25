<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    protected $fillable = [
        'doctor_id',
        'service_id',           // ✅ Thêm
        'guest_name',           // ✅ Thêm
        'guest_phone',          // ✅ Thêm
        'guest_email',          // ✅ Thêm
        'guest_address',        // ✅ Thêm
        'specialization_id',    // ✅ Thêm
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
        'status' => 'string',
        'approval_status' => 'string',
        'consultation_type' => 'string',
    ];

    /**
     * Mối quan hệ với bảng Doctors
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Mối quan hệ với bảng Users
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ✅ Mối quan hệ với bảng Services
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    
}