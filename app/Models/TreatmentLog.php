<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentLog extends Model
{
    use HasFactory;

    protected $table = 'treatment_logs';

    protected $fillable = [
        'medical_record_id',
        'description',
        'treatment_date', // Sửa 'date' thành 'treatment_date'
        'next_appointment_date' // Nếu bạn cần lưu ngày hẹn tái khám
    ];

    public $timestamps = true;

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id');
    }
}

