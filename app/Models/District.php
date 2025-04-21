<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'province_id'];

    /**
     * Lấy tỉnh/thành phố mà quận/huyện này thuộc về
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Lấy tên đầy đủ của quận/huyện (kèm tên tỉnh/thành phố)
     */
    public function getFullNameAttribute()
    {
        return $this->name . ', ' . ($this->province ? $this->province->name : '');
    }
}
