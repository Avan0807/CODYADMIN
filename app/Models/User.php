<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'photo',
        'status',
        'provider',
        'provider_id',
        'address' // Thêm dòng này để Laravel chấp nhận lưu `address`
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    /**
     * Quan hệ với bảng `orders`
     */
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    /**
     * Xác thực Token API sử dụng Laravel Sanctum.
     */
    public function createSanctumToken()
    {
        return $this->createToken('AuthToken')->plainTextToken;
    }


}
