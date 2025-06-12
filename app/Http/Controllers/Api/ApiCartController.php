<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Cart;
use App\Models\AffiliateLink;
use Illuminate\Http\Request;

class ApiCartController extends Controller
{
    public function index()
    {
        // Lấy giỏ hàng của người dùng hiện tại
        $cart = Cart::where('user_id', auth()->id())
                    ->with('product')  // Liên kết với bảng sản phẩm
                    ->with('user')     // Liên kết với bảng người dùng (nếu cần)
                    ->get();

        return response()->json([
            'success' => true,
            'cart' => $cart
        ], 200);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (API).
     */
    public function addToCart(Request $request)
    {
        $this->validate($request, [
            'slug' => 'required|string',
            'ref' => 'nullable|string', // ✅ Thêm ref parameter
        ]);

        $product = Product::where('slug', $request->slug)->first();
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không hợp lệ'], 400);
        }

        // ✅ Xử lý affiliate
        $doctor_id = null;
        if ($request->filled('ref')) {
            $affiliate = AffiliateLink::where('hash_ref', $request->ref)->first();
            if ($affiliate) {
                $doctor_id = $affiliate->doctor_id;
            }
        }

        // ✅ Tính giá sau discount
        $final_price = $product->price - ($product->price * $product->discount / 100);

        // ✅ Kiểm tra cart CÙNG doctor_id
        $already_cart = Cart::where('user_id', auth()->user()->id)
            ->where('order_id', null)
            ->where('product_id', $product->id)
            ->where('doctor_id', $doctor_id) // ✅ THÊM điều kiện này
            ->first();

        if ($already_cart) {
            // ✅ Check stock trước
            if ($product->stock < ($already_cart->quantity + 1)) {
                return response()->json(['error' => 'Số lượng tồn kho không đủ'], 400);
            }

            // Tăng thêm số lượng
            $already_cart->quantity += 1;
            $already_cart->amount = $final_price * $already_cart->quantity; // ✅ Fix tính amount

            // ✅ Tính commission
            $already_cart->commission = $doctor_id 
                ? ($already_cart->amount * ($product->commission_percentage / 100))
                : 0;

            $already_cart->save();
        } else {
            // ✅ Check stock trước
            if ($product->stock < 1) {
                return response()->json(['error' => 'Sản phẩm đã hết hàng'], 400);
            }

            // Tạo mới giỏ hàng
            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = $final_price;
            $cart->quantity = 1;
            $cart->amount = $final_price;
            $cart->doctor_id = $doctor_id; // ✅ Set doctor_id
            $cart->commission = $doctor_id 
                ? ($final_price * ($product->commission_percentage / 100))
                : 0;

            $cart->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
            'doctor_id' => $doctor_id, // ✅ Trả về để debug
        ]);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng với số lượng xác định (API).
     */
    public function singleAddToCart(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'quant' => 'required|integer|min:1',
            'ref' => 'nullable|string', // ✅ Thêm ref parameter
        ]);

        $product = Product::where('slug', $request->slug)->first();
        
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không hợp lệ'], 400);
        }

        if ($product->stock < $request->quant) {
            return response()->json(['error' => 'Hết hàng, bạn có thể thêm sản phẩm khác'], 400);
        }

        // ✅ Xử lý affiliate
        $doctor_id = null;
        if ($request->filled('ref')) {
            $affiliate = AffiliateLink::where('hash_ref', $request->ref)->first();
            if ($affiliate) {
                $doctor_id = $affiliate->doctor_id;
            }
        }

        // ✅ Tính giá sau discount
        $final_price = $product->price - ($product->price * $product->discount / 100);

        // ✅ Kiểm tra cart CÙNG doctor_id
        $already_cart = Cart::where('user_id', auth()->user()->id)
            ->where('order_id', null)
            ->where('product_id', $product->id)
            ->where('doctor_id', $doctor_id) // ✅ THÊM điều kiện này
            ->first();

        if ($already_cart) {
            $newQuantity = $already_cart->quantity + $request->quant;
            
            // ✅ Check stock với new quantity
            if ($product->stock < $newQuantity) {
                return response()->json(['error' => 'Số lượng tồn kho không đủ'], 400);
            }

            // Update
            $already_cart->quantity = $newQuantity;
            $already_cart->amount = $final_price * $newQuantity; // ✅ Fix formula

            // ✅ Tính commission
            $already_cart->commission = $doctor_id 
                ? ($already_cart->amount * ($product->commission_percentage / 100))
                : 0;

            $already_cart->save();
        } else {
            // Tạo mới giỏ hàng
            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = $final_price;
            $cart->quantity = $request->quant;
            $cart->amount = $final_price * $request->quant; // ✅ Fix formula
            $cart->doctor_id = $doctor_id; // ✅ Set doctor_id
            $cart->commission = $doctor_id 
                ? ($cart->amount * ($product->commission_percentage / 100))
                : 0;

            $cart->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
            'doctor_id' => $doctor_id, // ✅ Debug info
        ]);
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng (API).
     */
    public function cartDelete(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Bạn chưa đăng nhập'], 401);
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $cart = Cart::find($request->id);
        if ($cart) {
            $cart->delete();
            return response()->json(['success' => 'Đã xóa sản phẩm khỏi giỏ hàng']);
        }

        return response()->json(['error' => 'Đã xảy ra lỗi, vui lòng thử lại'], 400);
    }

    /**
     * Cập nhật giỏ hàng (API).
     */
    public function cartUpdate(Request $request)
    {
        if ($request->quant) {
            $error = [];
            $success = '';

            foreach ($request->quant as $k => $quant) {
                $id = $request->qty_id[$k];
                $cart = Cart::find($id);

                if ($quant > 0 && $cart) {
                    // Kiểm tra tồn kho
                    if ($cart->product->stock < $quant) {
                        return response()->json(['error' => 'Hết hàng'], 400);
                    }

                    // Cập nhật số lượng (không vượt quá tồn kho)
                    $cart->quantity = ($cart->product->stock > $quant) ? $quant : $cart->product->stock;

                    // Tính lại giá tiền
                    $after_price = ($cart->product->price - ($cart->product->price * $cart->product->discount) / 100);
                    $cart->amount = $after_price * $cart->quantity;
                    $cart->save();

                    $success = 'Cập nhật giỏ hàng thành công!';
                } else {
                    $error[] = 'Giỏ hàng không hợp lệ!';
                }
            }

            return response()->json(['success' => $success, 'errors' => $error]);
        } else {
            return response()->json(['error' => 'Giỏ hàng không hợp lệ!'], 400);
        }
    }

    /**
     * Kiểm tra số lượng tồn kho của sản phẩm trước khi thanh toán (API).
     */
    public function checkoutNow(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không hợp lệ'], 400);
        }

        $userId = auth()->id();
        $price = $product->price - ($product->price * $product->discount / 100);

        $hash_ref = $request->query('ref');
        $doctor_id = null;
        $affiliate_hash_ref = null;

        if ($hash_ref) {
            $affiliate = AffiliateLink::where('hash_ref', $hash_ref)->first();
            if ($affiliate) {
                $doctor_id = $affiliate->doctor_id;
                $affiliate_hash_ref = $affiliate->hash_ref;
            }
        }

        // ✅ Kiểm tra cart CÙNG doctor_id
        $cart = Cart::where('user_id', $userId)
                    ->whereNull('order_id')
                    ->where('product_id', $product->id)
                    ->where('doctor_id', $doctor_id) // ✅ THÊM điều kiện này
                    ->first();

        if ($cart) {
            // Check stock
            if ($product->stock < ($cart->quantity + 1)) {
                return response()->json(['error' => 'Số lượng tồn kho không đủ'], 400);
            }
            
            $cart->quantity += 1;
            $cart->amount = $cart->quantity * $price;
            $cart->commission = $doctor_id 
                ? ($cart->amount * ($product->commission_percentage / 100))
                : 0;
        } else {
            if ($product->stock <= 0) {
                return response()->json(['error' => 'Sản phẩm đã hết hàng'], 400);
            }

            $cart = new Cart([
                'user_id' => $userId,
                'product_id' => $product->id,
                'price' => $price,
                'quantity' => 1,
                'amount' => $price,
                'doctor_id' => $doctor_id, // ✅ Set ngay khi tạo
                'commission' => $doctor_id 
                    ? ($price * ($product->commission_percentage / 100))
                    : 0,
            ]);
        }

        $cart->save();

        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
            'cart' => $cart,
            'hash_ref' => $affiliate_hash_ref,
        ]);
    }
    
}
