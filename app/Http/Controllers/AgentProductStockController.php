<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Product;
use App\Models\AgentProductStock;
use App\Models\AgentStockHistory;
use Illuminate\Http\Request;

class AgentProductStockController extends Controller
{
    // Hiển thị tồn kho của tất cả đại lý
    public function index()
    {
        $stocks = AgentProductStock::with(['agent', 'product'])
                    ->orderByDesc('updated_at')
                    ->get();

        return view('backend.agent_stock.index', compact('stocks'));
    }

    // Form chia hàng
    public function create()
    {
        $agents = Agent::all();
        $products = Product::all();

        return view('backend.agent_stock.create', compact('agents', 'products'));
    }

    // Lưu dữ liệu chia hàng
    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $stock = AgentProductStock::firstOrNew([
            'agent_id' => $request->agent_id,
            'product_id' => $request->product_id,
        ]);

        $stock->quantity += $request->quantity;
        $stock->save();

        AgentStockHistory::create([
            'agent_id' => $request->agent_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'action' => 'import',
            'note' => 'Nhập hàng bởi admin'
        ]);

        return redirect()->route('admin.agent.stocks.index')->with('success', 'Đã chia hàng thành công');
    }
}
