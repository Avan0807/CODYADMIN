<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentOrder;
use App\Models\AgentLink;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $agentId = Auth::guard('agent')->id();

        $totalOrders = AgentOrder::byAgent($agentId)->count();
        $ordersToday = AgentOrder::byAgent($agentId)->whereDate('created_at', now())->count();
        $totalCommission = AgentOrder::byAgent($agentId)->sum('commission');
        $paidCommission = AgentOrder::byAgent($agentId)->paid()->sum('commission');
        $pendingCommission = AgentOrder::byAgent($agentId)->pending()->sum('commission');
        $totalLinks = AgentLink::byAgent($agentId)->count();

        // ✅ Load relationships và lọc NULL
        $recentOrders = AgentOrder::with(['order', 'product'])
            ->byAgent($agentId)
            ->whereHas('order') // Chỉ lấy có order
            ->whereHas('product') // Chỉ lấy có product
            ->latest()
            ->take(10)
            ->get();

        $revenueChart = AgentOrder::byAgent($agentId)
            ->selectRaw("DATE_FORMAT(created_at, '%m-%Y') as month, SUM(commission) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderByRaw("STR_TO_DATE(month, '%m-%Y')")
            ->pluck('total', 'month');

        $totalRevenue = $paidCommission; // Doanh thu = hoa hồng đã thanh toán
        $newLinksThisWeek = AgentLink::byAgent($agentId)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();
        $pendingNotifications = 3;

        return view('agent.dashboard', compact(
            'totalOrders', 'ordersToday', 'totalCommission',
            'paidCommission', 'pendingCommission', 'totalLinks', 
            'revenueChart', 'recentOrders', 'totalRevenue', 
            'newLinksThisWeek', 'pendingNotifications'
        ));
    }
}