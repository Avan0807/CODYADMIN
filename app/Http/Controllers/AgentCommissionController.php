<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AgentOrder;
use App\Models\Agent;
use Illuminate\Http\Request;

class AgentCommissionController extends Controller
{
    public function index(Request $request)
    {
        $agents = Agent::active()->get();
        
        // Tính toán commission summary cho từng agent
        $commissionSummary = Agent::with(['agentOrders'])
            ->get()
            ->map(function ($agent) {
                $orders = $agent->agentOrders;
                return [
                    'agent' => $agent,
                    'total_orders' => $orders->count(),
                    'total_commission' => $orders->sum('commission'),
                    'paid_commission' => $orders->where('status', 'paid')->sum('commission'),
                    'pending_commission' => $orders->where('status', 'pending')->sum('commission'),
                ];
            })
            ->filter(function ($summary) {
                return $summary['total_orders'] > 0;
            })
            ->sortByDesc('total_commission');

        // Tổng statistics
        $stats = [
            'total_orders' => AgentOrder::count(),
            'total_commission' => AgentOrder::sum('commission'),
            'paid_commission' => AgentOrder::where('status', 'paid')->sum('commission'),
            'pending_commission' => AgentOrder::where('status', 'pending')->sum('commission'),
        ];

        return view('backend.agent.agent-commissions.index', compact('agents', 'commissionSummary', 'stats'));
    }

    public function show($agentId)
    {
        $agent = Agent::findOrFail($agentId);
        $orders = AgentOrder::with(['product', 'order'])
                           ->where('agent_id', $agentId)
                           ->orderBy('created_at', 'desc')
                           ->paginate(20);

        $stats = [
            'total_orders' => $orders->total(),
            'total_commission' => AgentOrder::where('agent_id', $agentId)->sum('commission'),
            'paid_commission' => AgentOrder::where('agent_id', $agentId)->where('status', 'paid')->sum('commission'),
            'pending_commission' => AgentOrder::where('agent_id', $agentId)->where('status', 'pending')->sum('commission'),
        ];

        return view('backend.agent.agent-commissions.show', compact('agent', 'orders', 'stats'));
    }
}