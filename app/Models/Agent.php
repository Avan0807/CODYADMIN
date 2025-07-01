<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'location',
        'company',
        'business_type',
        'experience',
        'photo',
        'bio',
        'short_bio',
        'status',
        'rating',
        'commission_rate',
        'total_commission',
        'total_sales',
        'points',
        'bank_info',
        'tax_code',
        'referral_code',
        'password',
    ];

    protected $hidden = [
        'password',
        'api_token',
    ];

    protected $casts = [
        'bank_info' => 'array',
        'rating' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'api_token_expires_at' => 'datetime',
        'last_api_access' => 'datetime',
    ];

    // Relationships
    public function agentLinks()
    {
        return $this->hasMany(AgentLink::class);
    }

    public function agentOrders()
    {
        return $this->hasMany(AgentOrder::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByRating($query, $minRating = 4.0)
    {
        return $query->where('rating', '>=', $minRating);
    }
    
    public function stocks()
    {
        return $this->hasMany(AgentProductStock::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(AgentStockHistory::class);
    }

}