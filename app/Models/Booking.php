<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;

class Booking extends Model
{
    // Tên bảng trong cơ sở dữ liệu
    
    // Các cột có thể điền dữ liệu (mass assignment)
    protected $fillable = [
        'name',
        'phone',
        'date',
        'time',
        'doctor_id', // Sửa từ 'doctor_id' thành 'doctorID' để khớp với cơ sở dữ liệu
        'status',
        'consultation_type', // Thêm cột này
        'note', // Thêm cột này
    ];

    /**
     * Liên kết với Model Doctor (Many-to-One)
     */
    public function doctor()
    {
        // Khóa ngoại là 'doctorID', liên kết với cột 'doctorID' trong bảng Doctors
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
}
