<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentProductStock;
use App\Models\Agent;
use App\Models\Product;
use App\Models\AgentStockHistory;
use Illuminate\Http\Request;

class AgentProductStockController extends Controller
{

    /**
     * [Đại lý] Xem tồn kho của chính mình
     */
    public function myStock()
    {
        $agentId = auth('agent')->id();
        $stocks = AgentProductStock::with('product')
            ->where('agent_id', $agentId)
            ->get();

        return view('agent.stocks.index', compact('stocks'));
    }
}
