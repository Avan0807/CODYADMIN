<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentStockHistory extends Model
{
    protected $table = 'agent_stock_histories';

    protected $fillable = [
        'agent_id',
        'product_id',
        'quantity',
        'action',
        'note',
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
