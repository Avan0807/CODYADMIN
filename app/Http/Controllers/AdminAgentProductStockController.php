<?php

namespace App\Http\Controllers;

use App\Models\AgentProductStock;
use App\Models\AgentStockHistory;
use App\Models\Agent;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminAgentProductStockController extends Controller
{
    public function index()
    {
        $stocks = AgentProductStock::with(['agent', 'product'])
            ->orderByDesc('updated_at')->get();

        return view('backend.agent_stock.index', compact('stocks'));
    }

    public function create()
    {
        $agents = Agent::all();
        $products = Product::all();
        return view('backend.agent_stock.create', compact('agents', 'products'));
    }

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

        return redirect()->route('agent.stocks.index')->with('success', 'Đã chia hàng thành công');
    }
    
    public function revoke(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $stock = AgentProductStock::findOrFail($id);

        if ($stock->quantity < $request->quantity) {
            return back()->with('error', 'Không thể thu hồi quá số lượng hiện có.');
        }

        $stock->quantity -= $request->quantity;
        $stock->save();

        AgentStockHistory::create([
            'agent_id' => $stock->agent_id,
            'product_id' => $stock->product_id,
            'quantity' => -1 * $request->quantity,
            'action' => 'revoke',
            'note' => 'Thu hồi hàng bởi admin'
        ]);

        return back()->with('success', 'Đã thu hồi hàng thành công.');
    }

    /**
     * [Admin] Xem lịch sử nhập hàng của đại lý
     */
    public function history()
    {
        $histories = AgentStockHistory::with(['agent', 'product'])
            ->orderByDesc('created_at')
            ->get();

        return view('backend.agent_stock.history', compact('histories'));
    }

}
