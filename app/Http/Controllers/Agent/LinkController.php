<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentLink;
use App\Models\Product;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LinkController extends Controller
{
    // ✅ Xem tất cả link tiếp thị của chính mình
    public function myLinks()
    {
        $agentId = Auth::guard('agent')->id();
        $links = AgentLink::with('product')
                    ->where('agent_id', $agentId)
                    ->latest()
                    ->get();

        return view('agent.links.index', compact('links'));
    }

    // ✅ Tạo hoặc lấy lại link tiếp thị cho sản phẩm cụ thể
    public function generateLink($slug)
    {
        $agentID = Auth::guard('agent')->id();

        // ✅ Tìm sản phẩm theo slug
        $product = Product::where('slug', $slug)->firstOrFail();

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

            // ✅ Tạo link duy nhất với format toikhoe.vn
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

    public function availableProducts()
    {
        $products = Product::where('commission_percentage', '>', 0)->latest()->get();
        return view('agent.links.create', compact('products'));
    }

}
