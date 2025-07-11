<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Cart;
use App\Models\AffiliateLink;
use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Log;

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
    $userId = auth()->id();

    // ✅ Tìm sản phẩm theo slug
    $product = Product::where('slug', $slug)->first();
    if (!$product) {
        return response()->json([
            'success' => false,
            'error' => 'Sản phẩm không tồn tại!'
        ], 404);
    }

    // ✅ Lấy ref từ query string
    $hash_ref = $request->query('ref');
    $doctor_id = null;
    $commissionPercentage = $product->commission_percentage;

    if ($hash_ref) {
        $affiliate = AffiliateLink::where('hash_ref', $hash_ref)->first();

        Log::info('API Checkout - Hash ref:', ['hash_ref' => $hash_ref]);
        Log::info('API Checkout - Affiliate record:', $affiliate ? $affiliate->toArray() : ['affiliate' => null]);

        if ($affiliate && $affiliate->doctor_id) {
            $doctor_id = $affiliate->doctor_id;
            $commissionPercentage = $affiliate->commission_percentage ?? $product->commission_percentage;
        }
    }

    // ✅ Tính giá cuối cùng (có discount)
    $final_price = $product->price - ($product->price * $product->discount / 100);

    // ✅ Tìm cart có sản phẩm + doctor_id tương ứng
    $cart = Cart::where('user_id', $userId)
                ->whereNull('order_id')
                ->where('product_id', $product->id)
                ->where('doctor_id', $doctor_id)
                ->first();

    if ($cart) {
        $cart->quantity += 1;
        $cart->amount = $cart->quantity * $final_price;
    } else {
        $cart = new Cart([
            'user_id'    => $userId,
            'product_id' => $product->id,
            'price'      => $final_price,
            'quantity'   => 1,
            'amount'     => $final_price,
            'doctor_id'  => $doctor_id,
        ]);
    }

    // ✅ Gán commission theo doctor_id nếu có
    $cart->commission = $doctor_id
        ? ($cart->amount * ($commissionPercentage / 100))
        : 0;

    // ✅ Check tồn kho
    if ($product->stock < $cart->quantity) {
        return response()->json([
            'success' => false,
            'error' => 'Số lượng tồn kho không đủ!'
        ], 400);
    }

    $cart->save();
    Log::info('✅ Cart đã lưu:', $cart->toArray());

    // ✅ Trả về JSON API
    return response()->json([
        'success'   => true,
        'message'   => 'Sản phẩm đã được thêm vào giỏ hàng',
        'cart'      => [
            'id'          => $cart->id,
            'user_id'     => $cart->user_id,
            'product_id'  => $cart->product_id,
            'price'       => $cart->price,
            'quantity'    => $cart->quantity,
            'amount'      => $cart->amount,
            'commission'  => $cart->commission,
            'doctor_id'   => $cart->doctor_id,
            'created_at'  => $cart->created_at,
            'updated_at'  => $cart->updated_at,
        ],
        'hash_ref'  => $hash_ref,
    ]);
}

}
