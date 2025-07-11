<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentStockHistory;
use Illuminate\Http\Request;

class AgentStockHistoryController extends Controller
{
    /**
     * [Đại lý] Xem lịch sử của chính mình
     */
    public function myHistory()
    {
        $agentId = auth('agent')->id();

        $histories = AgentStockHistory::with('product')
            ->where('agent_id', $agentId)
            ->orderByDesc('created_at')
            ->get();

        return view('agent.stocks.history', compact('histories'));
    }
}
