<?php

namespace App\Http\Controllers;

use Srmklive\PayPal\Services\ExpressCheckout;
use Illuminate\Http\Request;
use NunoMaduro\Collision\Provider;
use App\Models\Cart;
use App\Models\Product;
use DB;
use Illuminate\Contracts\View\View as ViewContract;

class PaypalController extends Controller
{
    /**
     * Xử lý thanh toán qua PayPal
     */
    public function payment()
    {
        $cart = Cart::where('user_id', auth()->user()->id)
                    ->where('order_id', null)
                    ->get()
                    ->toArray();
        
        $data = [];
        
        // Chuẩn bị dữ liệu cho PayPal
        $data['items'] = array_map(function ($item) {
            $name = Product::where('id', $item['product_id'])->pluck('title');
            return [
                'name'  => $name,
                'price' => $item['price'],
                'desc'  => 'Cảm ơn bạn đã sử dụng PayPal',
                'qty'   => $item['quantity']
            ];
        }, $cart);

        $data['invoice_id']          = 'ORD-' . strtoupper(uniqid());
        $data['invoice_description'] = "Order #{$data['invoice_id']} Invoice";
        $data['return_url']          = route('payment.success');
        $data['cancel_url']          = route('payment.cancel');

        // Tính tổng tiền
        $total = 0;
        foreach($data['items'] as $item) {
            $total += $item['price'] * $item['qty'];
        }
        $data['total'] = $total;

        // Nếu có mã giảm giá
        if(session('coupon')){
            $data['shipping_discount'] = session('coupon')['value'];
        }

        // Gắn order_id cho cart (để liên kết với đơn hàng)
        Cart::where('user_id', auth()->user()->id)
            ->where('order_id', null)
            ->update(['order_id' => session()->get('id')]);
        
        $provider = new ExpressCheckout();
        $response = $provider->setExpressCheckout($data);

        return redirect($response['paypal_link']);
    }

    /**
     * Xử lý khi người dùng hủy thanh toán
     */
    public function cancel()
    {
        dd('Thanh toán của bạn đã bị hủy. Bạn có thể tạo trang hủy thanh toán tại đây.');
    }

    /**
     * Xử lý khi thanh toán thành công
     */
    public function success(Request $request)
    {
        $provider = new ExpressCheckout();
        $response = $provider->getExpressCheckoutDetails($request->token);

        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            request()->session()->flash('success','Bạn đã thanh toán qua PayPal thành công! Cảm ơn bạn.');
            session()->forget('cart');
            session()->forget('coupon');
            return redirect()->route('home');
        }

        request()->session()->flash('error','Đã xảy ra lỗi, vui lòng thử lại!!!');
        return redirect()->back();
    }
}
