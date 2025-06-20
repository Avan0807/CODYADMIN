<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AgentLink;
use App\Models\Agent;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AgentLinkController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentLink::with(['agent', 'product']);

        // Search
        if ($request->search) {
            $query->whereHas('agent', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->orWhereHas('product', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
            })->orWhere('hash_ref', 'like', '%' . $request->search . '%');
        }

        // Filter by agent
        if ($request->agent_id) {
            $query->where('agent_id', $request->agent_id);
        }

        // Filter by product
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        $agentLinks = $query->orderBy('created_at', 'desc')->paginate(20);
        $agents = Agent::active()->get();
        $products = Product::where('commission_percentage', '>', 0)->get();

        // Statistics
        $stats = [
            'total_links' => AgentLink::count(),
            'total_agents' => AgentLink::distinct('agent_id')->count(),
            'total_products' => AgentLink::distinct('product_id')->count(),
            'avg_commission' => AgentLink::avg('commission_percentage'),
        ];

        return view('backend.agent.agent-links.index', compact('agentLinks', 'agents', 'products', 'stats'));
    }

    public function create()
    {
        $agents = Agent::active()->get();
        $products = Product::where('commission_percentage', '>', 0)->get();

        return view('backend.agent.agent-links.create', compact('agents', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'product_id' => 'required|exists:products,id',
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Check if link already exists
        $existingLink = AgentLink::where('agent_id', $request->agent_id)
                                ->where('product_id', $request->product_id)
                                ->first();

        if ($existingLink) {
            return back()->withErrors(['error' => 'Agent link for this product already exists']);
        }

        $product = Product::findOrFail($request->product_id);
        $agent = Agent::findOrFail($request->agent_id);

        // Generate unique hash reference
        do {
            $hashRef = 'AGT_' . strtoupper(Str::random(8));
        } while (AgentLink::where('hash_ref', $hashRef)->exists());

        // Create product link
        $productLink = url('/product/' . $product->slug . '?ref=' . $hashRef);

        AgentLink::create([
            'agent_id' => $request->agent_id,
            'product_id' => $request->product_id,
            'product_link' => $productLink,
            'hash_ref' => $hashRef,
            'commission_percentage' => $request->commission_percentage,
        ]);

        return redirect()->route('agent.links.index')
                        ->with('success', 'Agent link created successfully');
    }

    public function show(AgentLink $agentLink)
    {
        $agentLink->load(['agent', 'product']);
        
        // Get click statistics (if you have agent_clicks table)
        // $clickStats = AgentClick::where('hash_ref', $agentLink->hash_ref)
        //                        ->selectRaw('DATE(clicked_at) as date, COUNT(*) as clicks')
        //                        ->groupBy('date')
        //                        ->orderBy('date', 'desc')
        //                        ->limit(30)
        //                        ->get();

        return view('backend.agent.agent-links.show', compact('agentLink'));
    }

    public function edit($id)
    {
        $agentLink = AgentLink::findOrFail($id);
        $agents = Agent::active()->get();
        $products = Product::where('commission_percentage', '>', 0)->get();

        return view('backend.agent.agent-links.edit', compact('agentLink', 'agents', 'products'));
    }

    public function update(Request $request, $id)
    {
        $agentLink = AgentLink::findOrFail($id);

        $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'product_id' => 'required|exists:products,id',
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Check if changing to existing combination
        if ($request->agent_id != $agentLink->agent_id || $request->product_id != $agentLink->product_id) {
            $existingLink = AgentLink::where('agent_id', $request->agent_id)
                                    ->where('product_id', $request->product_id)
                                    ->where('id', '!=', $id)
                                    ->first();

            if ($existingLink) {
                return back()->withErrors(['error' => 'Agent link for this product already exists']);
            }
        }

        // Update product link if product changed
        if ($request->product_id != $agentLink->product_id) {
            $product = Product::findOrFail($request->product_id);
            $productLink = url('/product/' . $product->slug . '?ref=' . $agentLink->hash_ref);
            $agentLink->product_link = $productLink;
        }

        $agentLink->update([
            'agent_id' => $request->agent_id,
            'product_id' => $request->product_id,
            'commission_percentage' => $request->commission_percentage,
            'product_link' => $agentLink->product_link,
        ]);

        return redirect()->route('agent.links.index')
                        ->with('success', 'Agent link updated successfully');
    }

    public function destroy($id)
    {
        $agentLink = AgentLink::findOrFail($id);
        $agentLink->delete();

        return redirect()->route('agent.links.index')
                        ->with('success', 'Agent link deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'link_ids' => 'required|array',
            'link_ids.*' => 'exists:agent_links,id'
        ]);

        AgentLink::whereIn('id', $request->link_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected links deleted successfully'
        ]);
    }

    public function generateLink($product_slug)
    {
        $agentID = Auth::id();

        // ✅ Tìm sản phẩm theo slug
        $product = Product::where('slug', $product_slug)->firstOrFail();

        // ✅ Xóa các bản ghi trùng lặp, chỉ giữ lại 1 bản ghi duy nhất
        AgentLink::where('agent_id', $agentID)
            ->where('product_id', $product->id)
            ->orderBy('id', 'DESC')
            ->skip(1)
            ->delete();

        // ✅ Kiểm tra xem link đã tồn tại chưa
        $existingLink = AgentLink::where([
            ['agent_id', $agentID],
            ['product_id', $product->id]
        ])->first();

        $hashRef = "";
        $product_link = "";
        $commissionPercentage = 0;

        // ✅ Lấy commission từ agent nếu chưa có, fallback về product
        if (!$existingLink || is_null($existingLink->commission_percentage)) {
            $agent = Agent::find($agentID);
            $commissionData = Product::where('id', $product->id)->first();
            
            // Ưu tiên commission của agent, nếu không có thì lấy từ product
            $commissionPercentage = $agent && $agent->commission_rate > 0 
                ? $agent->commission_rate 
                : ($commissionData ? $commissionData->commission_percentage : 0);
        }

        if ($existingLink) {
            $hashRef = $existingLink->hash_ref;
            $product_link = "https://toikhoe.vn/deep-link/product-detail/{$product->slug}?ref={$hashRef}";

            // ✅ Cập nhật link hoặc commission nếu cần
            $existingLink->update([
                'product_link' => $product_link,
                'commission_percentage' => $existingLink->commission_percentage ?? $commissionPercentage
            ]);
        } else {
            // ✅ Tạo hash_ref mới cho agent (khác với doctor)
            $hashRef = 'AGT_' . hash('sha256', $agentID . $product->id . time());

            // ✅ Tạo link duy nhất
            $product_link = "https://toikhoe.vn/deep-link/product-detail/{$product->slug}?ref={$hashRef}";

            // ✅ Lưu link mới vào DB
            $existingLink = AgentLink::create([
                'agent_id' => $agentID,
                'product_id' => $product->id,
                'hash_ref' => $hashRef,
                'product_link' => $product_link,
                'commission_percentage' => $commissionPercentage
            ]);
        }

        // ✅ Open App Link (cùng link với product_link)
        $openAppLink = $product_link;

        return response()->json([
            'message' => $existingLink->wasRecentlyCreated ? 'Link Affiliate Agent được tạo thành công!' : 'Link Affiliate Agent đã tồn tại!',
            'product_link' => $product_link,  // ✅ Dùng chung một link duy nhất
            'open_app_link' => $openAppLink,  // ✅ Mở app với cùng link
            'fallback_url' => $product_link,  // ✅ Nếu không mở được app, dùng web link
            'commission_percentage' => $commissionPercentage,
            'data' => $existingLink
        ], $existingLink->wasRecentlyCreated ? 201 : 200);
    }

    public function copyLink($id)
    {
        $agentLink = AgentLink::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'link' => $agentLink->product_link
        ]);
    }
}