<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_check_in',
        'patient_name',
        'doctor_assigned',
        'disease',
        'status',
        'room_no',
    ];
}

