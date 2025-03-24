<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;
    protected $table = 'meetings';
    protected $fillable = [
        'title',
        'description',
        'meet_link',
        'start_time',
        'end_time',
        'created_by_id',
        'created_by_type',
    ];
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'created_by_id');
    }
}

