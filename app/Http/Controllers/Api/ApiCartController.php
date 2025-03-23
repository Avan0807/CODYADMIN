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
        ]);


        $product = Product::where('slug', $request->slug)->first();
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không hợp lệ'], 400);
        }

        $already_cart = Cart::where('user_id', auth()->user()->id)
            ->where('order_id', null)
            ->where('product_id', $product->id)
            ->first();

        if ($already_cart) {
            // Tăng thêm số lượng
            $already_cart->quantity += 1;
            $already_cart->amount = $product->price + $already_cart->amount;

            // Kiểm tra tồn kho
            if ($already_cart->product->stock < $already_cart->quantity || $already_cart->product->stock <= 0) {
                return response()->json(['error' => 'Số lượng tồn kho không đủ'], 400);
            }

            $already_cart->save();
        } else {
            // Tạo mới giỏ hàng
            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = ($product->price - ($product->price * $product->discount) / 100);
            $cart->quantity = 1;
            $cart->amount = $cart->price * $cart->quantity;

            // Kiểm tra tồn kho
            if ($cart->product->stock < $cart->quantity || $cart->product->stock <= 0) {
                return response()->json(['error' => 'Số lượng tồn kho không đủ'], 400);
            }

            $cart->save();
        }

        return response()->json(['success' => 'Sản phẩm đã được thêm vào giỏ hàng']);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng với số lượng xác định (API).
     */
    public function singleAddToCart(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'quant' => 'required|integer|min:1',
        ]);

        $product = Product::where('slug', $request->slug)->first();
        if ($product->stock < $request->quant) {
            return response()->json(['error' => 'Hết hàng, bạn có thể thêm sản phẩm khác'], 400);
        }

        if ($request->quant < 1 || !$product) {
            return response()->json(['error' => 'Sản phẩm không hợp lệ'], 400);
        }

        $already_cart = Cart::where('user_id', auth()->user()->id)
            ->where('order_id', null)
            ->where('product_id', $product->id)
            ->first();

        if ($already_cart) {
            // Tăng thêm số lượng
            $already_cart->quantity += $request->quant;
            $already_cart->amount = ($product->price * $request->quant) + $already_cart->amount;

            // Kiểm tra tồn kho
            if ($already_cart->product->stock < $already_cart->quantity || $already_cart->product->stock <= 0) {
                return response()->json(['error' => 'Số lượng tồn kho không đủ'], 400);
            }

            $already_cart->save();
        } else {
            // Tạo mới giỏ hàng
            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = ($product->price - ($product->price * $product->discount) / 100);
            $cart->quantity = $request->quant;
            $cart->amount = ($product->price * $request->quant);

            // Kiểm tra tồn kho
            if ($cart->product->stock < $cart->quantity || $cart->product->stock <= 0) {
                return response()->json(['error' => 'Số lượng tồn kho không đủ'], 400);
            }

            $cart->save();
        }

        return response()->json(['success' => 'Sản phẩm đã được thêm vào giỏ hàng']);
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

        $cart = Cart::where('user_id', $userId)
                    ->whereNull('order_id')
                    ->where('product_id', $product->id)
                    ->first();

        if ($cart) {
            $cart->quantity += 1;
            $cart->amount = $cart->quantity * $price;
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
            ]);
        }

        if ($doctor_id && !$cart->doctor_id) {
            $cart->doctor_id = $doctor_id;
            $cart->commission = $cart->amount * ($product->commission_percentage / 100);
        }

        $cart->save();

        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
            'cart' => $cart,
            'hash_ref' => $affiliate_hash_ref, // trả ra luôn ở đây
        ]);
    }

}
