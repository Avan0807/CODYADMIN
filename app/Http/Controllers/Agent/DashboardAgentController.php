<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgentOrder;
use App\Models\AgentLink;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardAgentController extends Controller
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

    public function profile()
    {
        $agent = Auth::guard('agent')->user();
        return view('agent.profile', compact('agent'));
    }

    public function updateProfile(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agents,email,' . $agent->id,
            'phone' => 'required|string|max:20',
            'location' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->only(['name', 'email', 'phone', 'location', 'company', 'bio']);
        
        if ($request->hasFile('photo')) {
            // Upload photo logic
            $photoPath = $request->file('photo')->store('agents', 'public');
            $data['photo'] = $photoPath;
        }
        
        $agent->update($data);
        
        return redirect()->route('agent.profile')->with('success', 'Cập nhật hồ sơ thành công!');
    }


}