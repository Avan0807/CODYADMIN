<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AgentOrder;
use App\Models\Agent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AgentOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentOrder::with(['agent', 'product', 'order']);

        // Search
        if ($request->search) {
            $query->whereHas('agent', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->orWhereHas('product', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by agent
        if ($request->agent_id) {
            $query->where('agent_id', $request->agent_id);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $agentOrders = $query->orderBy('created_at', 'desc')->paginate(20);
        $agents = Agent::active()->get();

        // Statistics
        $stats = [
            'total_orders' => AgentOrder::count(),
            'pending_orders' => AgentOrder::pending()->count(),
            'paid_orders' => AgentOrder::paid()->count(),
            'total_commission' => AgentOrder::sum('commission'),
            'pending_commission' => AgentOrder::pending()->sum('commission'),
            'paid_commission' => AgentOrder::paid()->sum('commission'),
        ];

        return view('backend.agent.agent-orders.index', compact('agentOrders', 'agents', 'stats'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,cancelled'
        ]);

        $agentOrder = AgentOrder::findOrFail($id);
        $oldStatus = $agentOrder->status;
        
        $agentOrder->update([
            'status' => $request->status,
            'paid_at' => $request->status === 'paid' ? now() : null
        ]);

        // Update agent total commission if status changed to paid
        if ($request->status === 'paid' && $oldStatus !== 'paid') {
            $agent = $agentOrder->agent;
            $agent->increment('total_commission', $agentOrder->commission);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agent order status updated successfully'
        ]);
    }

    public function stats(Request $request)
    {
        $period = $request->period ?? 'month'; // day, week, month, year

        $query = AgentOrder::query();

        switch ($period) {
            case 'day':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        $stats = [
            'total_orders' => $query->count(),
            'total_commission' => $query->sum('commission'),
            'pending_commission' => $query->pending()->sum('commission'),
            'paid_commission' => $query->paid()->sum('commission'),
            'top_agents' => AgentOrder::with('agent')
                                     ->selectRaw('agent_id, COUNT(*) as orders_count, SUM(commission) as total_commission')
                                     ->groupBy('agent_id')
                                     ->orderByDesc('total_commission')
                                     ->limit(10)
                                     ->get()
        ];

        return response()->json($stats);
    }

    public function reportByAgent(Request $request)
    {
        $agentId = $request->agent_id;
        $fromDate = $request->from_date ?? now()->startOfMonth();
        $toDate = $request->to_date ?? now()->endOfMonth();

        $query = AgentOrder::with(['agent', 'product', 'order'])
                           ->whereBetween('created_at', [$fromDate, $toDate]);

        if ($agentId) {
            $query->where('agent_id', $agentId);
        }

        $orders = $query->get();

        $report = $orders->groupBy('agent_id')->map(function ($agentOrders) {
            $agent = $agentOrders->first()->agent;
            return [
                'agent' => $agent,
                'total_orders' => $agentOrders->count(),
                'total_commission' => $agentOrders->sum('commission'),
                'pending_commission' => $agentOrders->where('status', 'pending')->sum('commission'),
                'paid_commission' => $agentOrders->where('status', 'paid')->sum('commission'),
                'orders' => $agentOrders
            ];
        });

        return view('admin.agent-orders.report', compact('report', 'fromDate', 'toDate'));
    }

    public function bulkPay(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:agent_orders,id'
        ]);

        $orders = AgentOrder::whereIn('id', $request->order_ids)
                           ->where('status', 'pending')
                           ->get();

        $totalCommission = 0;

        foreach ($orders as $order) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

            // Update agent total commission
            $order->agent->increment('total_commission', $order->commission);
            $totalCommission += $order->commission;
        }

        return response()->json([
            'success' => true,
            'message' => "Paid {$orders->count()} orders with total commission: $" . number_format($totalCommission, 2)
        ]);
    }

    public function export(Request $request)
    {
        $query = AgentOrder::with(['agent', 'product', 'order']);

        // Apply same filters as index
        if ($request->agent_id) {
            $query->where('agent_id', $request->agent_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $filename = 'agent_orders_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID', 'Order ID', 'Agent Name', 'Product', 'Commission', 
                'Commission %', 'Status', 'Created At', 'Paid At'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->order_id,
                    $order->agent->name,
                    $order->product->title,
                    $order->commission,
                    $order->commission_percentage,
                    $order->status,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}