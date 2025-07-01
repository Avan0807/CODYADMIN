<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentStockHistory;
use Illuminate\Http\Request;

class AdminAgentStockHistoryController extends Controller
{
    public function index($agentId)
    {
        $agent = Agent::findOrFail($agentId);

        $histories = AgentStockHistory::with('product')
            ->where('agent_id', $agentId)
            ->orderByDesc('created_at')
            ->get();

        return view('backend.agent_stock.history', compact('agent', 'histories'));
    }
}
