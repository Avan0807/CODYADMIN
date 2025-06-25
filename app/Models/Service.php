<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope: Chỉ lấy services đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Tìm theo slug
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Accessor: Lấy URL đầy đủ cho image
     */
    public function getImageUrlAttribute()
    {
        if ($this->image && !str_starts_with($this->image, 'http')) {
            return asset('storage/' . $this->image);
        }
        return $this->image;
    }

    /**
     * Accessor: Lấy URL đầy đủ cho icon
     */
    public function getIconUrlAttribute()
    {
        if ($this->icon && !str_starts_with($this->icon, 'http')) {
            return asset('storage/' . $this->icon);
        }
        return $this->icon;
    }

    /**
     * Mối quan hệ với bảng Appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'service_id');
    }

    /**
     * Đếm số appointments theo service
     */
    public function getAppointmentsCountAttribute()
    {
        return $this->appointments()->count();
    }

    /**
     * Lấy appointments đang hoạt động
     */
    public function activeAppointments()
    {
        return $this->appointments()->where('status', '!=', 'cancelled');
    }
}