<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'region_id'];

    /**
     * Lấy tất cả quận/huyện thuộc tỉnh/thành phố này
     */
    public function districts()
    {
        return $this->hasMany(District::class);
    }

    /**
     * Lấy các quy tắc vận chuyển từ tỉnh/thành phố này
     */
    public function fromShippingLocations()
    {
        return $this->hasMany(ShippingLocation::class, 'from_province_id');
    }

    /**
     * Lấy các quy tắc vận chuyển đến tỉnh/thành phố này
     */
    public function toShippingLocations()
    {
        return $this->hasMany(ShippingLocation::class, 'to_province_id');
    }

    /**
     * Lấy tên vùng miền
     */
    public function getRegionNameAttribute()
    {
        switch ($this->region_id) {
            case 1:
                return 'Miền Bắc';
            case 2:
                return 'Miền Trung';
            case 3:
                return 'Miền Nam';
            default:
                return 'Không xác định';
        }
    }
}
