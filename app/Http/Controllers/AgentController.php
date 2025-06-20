<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = Agent::query();

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by rating
        if ($request->min_rating) {
            $query->byRating($request->min_rating);
        }

        $agents = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.agent.agent-users.index', compact('agents'));
    }

    public function create()
    {
        return view('backend.agent.agent-users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agents',
            'phone' => 'required|string|unique:agents',
            'location' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'experience' => 'required|integer|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'bio' => 'nullable|string',
            'short_bio' => 'nullable|string|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'bank_info' => 'nullable|array',
            'tax_code' => 'nullable|string|max:50',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data = $request->all();
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('agents', 'public');
        }

        // Hash password
        $data['password'] = Hash::make($request->password);
        
        // Generate referral code
        $data['referral_code'] = 'AGT_' . strtoupper(Str::random(8));
        
        // Default status
        $data['status'] = $request->status ?? 'pending';

        Agent::create($data);

        return redirect()->route('admin.agents.index')
                        ->with('success', 'Agent created successfully');
    }

    public function show(Agent $agent)
    {
        $agent->load(['agentLinks.product', 'agentOrders.order']);
        
        // Statistics
        $stats = [
            'total_links' => $agent->agentLinks()->count(),
            'total_orders' => $agent->agentOrders()->count(),
            'pending_commission' => $agent->agentOrders()->pending()->sum('commission'),
            'paid_commission' => $agent->agentOrders()->paid()->sum('commission'),
            'this_month_orders' => $agent->agentOrders()->thisMonth()->count(),
            'this_month_commission' => $agent->agentOrders()->thisMonth()->sum('commission'),
        ];

        return view('admin.agents.show', compact('agent', 'stats'));
    }

    public function edit(Agent $agent)
    {
        return view('admin.agents.edit', compact('agent'));
    }

    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agents,email,' . $agent->id,
            'phone' => 'required|string|unique:agents,phone,' . $agent->id,
            'location' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'experience' => 'required|integer|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'bio' => 'nullable|string',
            'short_bio' => 'nullable|string|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'bank_info' => 'nullable|array',
            'tax_code' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,pending,suspended',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = $request->except(['password', 'password_confirmation']);
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('agents', 'public');
        }

        // Update password if provided
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $agent->update($data);

        return redirect()->route('admin.agents.index')
                        ->with('success', 'Agent updated successfully');
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();

        return redirect()->route('admin.agents.index')
                        ->with('success', 'Agent deleted successfully');
    }

    public function updateStatus(Request $request, Agent $agent)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,pending,suspended'
        ]);

        $agent->update(['status' => $request->status]);

        return back()->with('success', 'Agent status updated successfully');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'agent_ids' => 'required|array',
            'action' => 'required|in:activate,deactivate,suspend,delete'
        ]);

        $agents = Agent::whereIn('id', $request->agent_ids);

        switch ($request->action) {
            case 'activate':
                $agents->update(['status' => 'active']);
                $message = 'Agents activated successfully';
                break;
            case 'deactivate':
                $agents->update(['status' => 'inactive']);
                $message = 'Agents deactivated successfully';
                break;
            case 'suspend':
                $agents->update(['status' => 'suspended']);
                $message = 'Agents suspended successfully';
                break;
            case 'delete':
                $agents->delete();
                $message = 'Agents deleted successfully';
                break;
        }

        return back()->with('success', $message);
    }
}