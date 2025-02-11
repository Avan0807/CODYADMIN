<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Contracts\View\View as ViewContract;

class WishlistController extends Controller
{
    protected $product = null;
    
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Thêm sản phẩm vào danh sách yêu thích (wishlist)
     */
    public function wishlist(Request $request)
    {
        if (empty($request->slug)) {
            request()->session()->flash('error','Sản phẩm không hợp lệ');
            return back();
        }

        $product = Product::where('slug', $request->slug)->first();
        if (empty($product)) {
            request()->session()->flash('error','Sản phẩm không hợp lệ');
            return back();
        }

        // Kiểm tra sản phẩm đã có trong wishlist hay chưa
        $already_wishlist = Wishlist::where('user_id', auth()->user()->id)
                                    ->where('cart_id', null)
                                    ->where('product_id', $product->id)
                                    ->first();

        if($already_wishlist) {
            request()->session()->flash('error','Sản phẩm đã có trong danh sách yêu thích của bạn');
            return back();
        } else {
            $wishlist = new Wishlist;
            $wishlist->user_id   = auth()->user()->id;
            $wishlist->product_id= $product->id;
            $wishlist->price     = ($product->price - ($product->price * $product->discount) / 100);
            $wishlist->quantity  = 1;
            $wishlist->amount    = $wishlist->price * $wishlist->quantity;

            if ($wishlist->product->stock < $wishlist->quantity || $wishlist->product->stock <= 0){
                return back()->with('error','Không đủ số lượng hàng trong kho');
            }
            $wishlist->save();
        }
        request()->session()->flash('success','Đã thêm sản phẩm vào danh sách yêu thích');
        return back();
    }  
    
    /**
     * Xóa sản phẩm khỏi danh sách yêu thích
     */
    public function wishlistDelete(Request $request)
    {
        $wishlist = Wishlist::find($request->id);
        if ($wishlist) {
            $wishlist->delete();
            request()->session()->flash('success','Đã xóa sản phẩm khỏi danh sách yêu thích');
            return back();  
        }
        request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại');
        return back();       
    }     
}
