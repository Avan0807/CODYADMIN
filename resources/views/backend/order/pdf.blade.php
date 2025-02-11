<!DOCTYPE html>
<meta charset="UTF-8">
<html>
<head>
  <title>Đặt hàng @if($order)- {{$order->order_number}} @endif</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>

@if($order)
<style type="text/css">
  /* Đầu trang hóa đơn */
  .invoice-header {
    background: #f7f7f7;
    padding: 10px 20px;
    border-bottom: 1px solid gray;
  }

  /* Logo */
  .site-logo {
    margin-top: 20px;
  }

  /* Phần thông tin bên phải */
  .invoice-right-top h3 {
    padding-right: 20px;
    margin-top: 20px;
    color: green;
    font-size: 30px !important;
    font-family: 'DejaVu Sans', sans-serif;
  }

  /* Phần thông tin bên trái */
  .invoice-left-top {
    border-left: 4px solid green;
    padding-left: 20px;
    padding-top: 20px;
  }

  .invoice-left-top p {
    margin: 0;
    line-height: 1.5;
    font-size: 16px;
    margin-bottom: 5px;
    font-family: 'DejaVu Sans', sans-serif;
  }

  /* Phần tiêu đề bảng */
  thead {
    background: green;
    color: #FFF;
  }

  /* Phần chữ ký */
  .authority h5 {
    margin-top: -10px;
    color: green;
    font-family: 'DejaVu Sans', sans-serif;
  }

  /* Lời cảm ơn */
  .thanks h4 {
    color: green;
    font-size: 25px;
    font-weight: normal;
    font-family: 'DejaVu Sans', sans-serif;
    margin-top: 20px;
  }

  /* Địa chỉ trang web */
  .site-address p {
    line-height: 1.5;
    font-weight: 300;
    font-family: 'DejaVu Sans', sans-serif;
  }

  /* Bảng dữ liệu */
  .table tfoot .empty {
    border: none;
  }

  .table-bordered {
    border: none;
  }

  .table-header {
    padding: 0.75rem 1.25rem;
    margin-bottom: 0;
    background-color: rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
  }

  .table td, .table th {
    padding: 0.5rem;
    font-family: 'DejaVu Sans', sans-serif;
  }
</style>

<div class="invoice-header">
    <div class="float-left site-logo">
        <img src="{{ public_path('backend/img/avatar.png') }}" alt="">
    </div>
    <div class="float-right site-address">
        <h4>{{ env('APP_NAME') }}</h4>
        <p>{{ env('APP_ADDRESS') }}</p>
        <p>Số điện thoại: <a href="tel:{{ env('APP_PHONE') }}">{{ env('APP_PHONE') }}</a></p>
        <p>Email: <a href="mailto:{{ env('APP_EMAIL') }}">{{ env('APP_EMAIL') }}</a></p>
    </div>
    <div class="clearfix"></div>
</div>
<div class="invoice-description">
    <div class="invoice-left-top float-left">
        <h6>Hóa đơn cho</h6>
        <h3>{{ $order->first_name }} {{ $order->last_name }}</h3>
        <div class="address">
            <p>
                <strong>Country: </strong>
                {{ $order->country }}
            </p>
            <p>
                <strong>Địa chỉ: </strong>
                {{ $order->address1 ?? 'N/A' }} hoặc {{ $order->address2 }}
            </p>
            <p><strong>Số điện thoại:</strong> {{ $order->phone }}</p>
            <p><strong>Email:</strong> {{ $order->email ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="invoice-right-top float-right" class="text-right">
        <h3>Hóa đơn #{{ $order->order_number }}</h3>
        <p>{{ $order->created_at->format('D d m Y') }}</p>
    </div>
    <div class="clearfix"></div>
</div>
<section class="order_details pt-3">
    <div class="table-header">
        <h5>Chi tiết đơn hàng</h5>
    </div>
    <table class="table table-bordered table-stripe">
        <thead>
            <tr>
                <th scope="col" class="col-6">Sản phẩm</th>
                <th scope="col" class="col-3">Số lượng</th>
                <th scope="col" class="col-3">Tổng cộng</th>
            </tr>
        </thead>
        <tbody>
        @foreach($order->cart_info as $cart)
        @php
            $product = DB::table('products')->select('title')->where('id', $cart->product_id)->first();
        @endphp
            <tr>
                <td><span>{{ $product->title ?? 'N/A' }}</span></td>
                <td>x{{ $cart->quantity }}</td>
                <td><span>{{ number_format($cart->price, 0, ',', '.') }}đ</span></td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" class="empty"></th>
                <th scope="col" class="text-right">Tổng cộng:</th>
                <th scope="col"><span>{{ number_format($order->sub_total, 0, ',', '.') }}đ</span></th>
            </tr>
            <tr>
                <th scope="col" class="empty"></th>
                @php
                    $shipping_charge = DB::table('shippings')->where('id', $order->shipping_id)->pluck('price')->first();
                @endphp
                <th scope="col" class="text-right">Vận chuyển:</th>
                <th><span>{{ number_format($shipping_charge ?? 0, 0, ',', '.') }}đ</span></th>
            </tr>
            <tr>
                <th scope="col" class="empty"></th>
                <th scope="col" class="text-right">Tổng cộng:</th>
                <th><span>{{ number_format($order->total_amount, 0, ',', '.') }}đ</span></th>
            </tr>
        </tfoot>
    </table>
</section>
<div class="thanks mt-3">
    <h4>Cảm ơn bạn đã kinh doanh !!</h4>
</div>
<div class="authority float-right mt-5">
    <p>-----------------------------------</p>
    <h5>Chữ ký của cơ quan có thẩm quyền:</h5>
</div>
<div class="clearfix"></div>
@else
    <h5 class="text-danger">Không hợp lệ</h5>
@endif

</body>
</html>