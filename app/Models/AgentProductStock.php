<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentProductStock extends Model
{
    protected $table = 'agent_product_stocks';

    protected $fillable = [
        'agent_id',
        'product_id',
        'quantity',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
