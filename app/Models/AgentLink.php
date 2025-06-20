<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'product_id',
        'product_link',
        'hash_ref',
        'commission_percentage',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
    ];

    // Relationships
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Accessors
    public function getFullLinkAttribute()
    {
        return url('/product/' . $this->product->slug . '?ref=' . $this->hash_ref);
    }
}