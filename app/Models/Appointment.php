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
        'service_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'guest_address',
        'specialization_id',
        'user_id',
        'date',
        'time',
        'status',
        'approval_status',
        'notes',
        'consultation_type',
        'consultation_fee',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'string', // hoặc 'time' nếu cần parse dạng thời gian
        'status' => 'string',
        'approval_status' => 'string',
        'consultation_type' => 'string',
        'consultation_fee' => 'integer',
    ];

    public $timestamps = true;

    /** Quan hệ với bác sĩ */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /** Quan hệ với người dùng đã đăng nhập */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Quan hệ với dịch vụ */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /** Quan hệ với chuyên khoa  */
    public function specialization()
    {
        return $this->belongsTo(Category::class, 'specialization_id');
}
}
