<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class AffiliateController extends Controller
{

    public function index()
    {
        $products = Product::all(); // Lấy tất cả sản phẩm
        return view('doctor.affiliate.index', compact('products')); // Truyền biến $products vào view
    }
    
    public function createLink($id)
    {
        $product = Product::findOrFail($id);
        $affiliateLink = route('product.show', ['id' => $product->id]) . '?ref_code=' . auth()->user()->ref_code;

        // Trả về view hoặc thực hiện logic cần thiết (ví dụ: lưu link vào database)
        return view('doctor.affiliate.link', compact('product', 'affiliateLink'));
    }
}
