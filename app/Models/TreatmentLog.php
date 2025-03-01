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
        'date',
    ];

    public $timestamps = true;

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id');
    }
}
