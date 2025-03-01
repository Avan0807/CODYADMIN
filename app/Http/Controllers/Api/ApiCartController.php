<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class ApiCartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())->get();

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
    public function checkoutNow($product_id)
    {
        // Lấy thông tin sản phẩm từ DB
        $product = Product::findOrFail($product_id);

        // Kiểm tra nếu sản phẩm không tồn tại
        if (!$product) {
            return response()->json(['error' => 'Sản phẩm không hợp lệ'], 400);
        }

        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $already_cart = Cart::where('user_id', auth()->user()->id)
            ->where('order_id', null)
            ->where('product_id', $product->id)
            ->first();

        if ($already_cart) {
            // Tăng thêm số lượng
            $already_cart->quantity += 1;
            $already_cart->amount = $product->price * $already_cart->quantity;

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
            $cart->price = $product->price - ($product->price * $product->discount) / 100;
            $cart->quantity = 1; // Mặc định là 1 sản phẩm
            $cart->amount = $cart->price * $cart->quantity;

            // Kiểm tra tồn kho
            if ($cart->product->stock < $cart->quantity || $cart->product->stock <= 0) {
                return response()->json(['error' => 'Số lượng tồn kho không đủ'], 400);
            }
            $cart->save();
        }

        return response()->json(['success' => 'Sản phẩm đã được thêm vào giỏ hàng']);
    }
}
