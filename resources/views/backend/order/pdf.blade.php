<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng @if ($order)
            - {{ $order->order_number }}
        @endif
    </title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .invoice-header {
            background: #f7f7f7;
            padding: 20px;
            border-bottom: 1px solid #ddd;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .site-logo {
            float: left;
            width: 30%;
        }

        .site-logo img {
            max-width: 100px;
            height: auto;
        }

        .site-address {
            float: right;
            width: 65%;
            text-align: right;
        }

        .site-address h4 {
            color: #2c5aa0;
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .site-address p {
            margin-bottom: 5px;
            font-size: 14px;
        }

        .site-address a {
            color: #2c5aa0;
            text-decoration: none;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Invoice Description */
        .invoice-description {
            margin-bottom: 30px;
            overflow: hidden;
        }

        .invoice-left-top {
            float: left;
            width: 50%;
            border-left: 4px solid #28a745;
            padding-left: 20px;
            padding-top: 10px;
        }

        .invoice-left-top h6 {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .invoice-left-top h3 {
            color: #28a745;
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .invoice-left-top p {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .invoice-right-top {
            float: right;
            width: 45%;
            text-align: right;
            padding-top: 10px;
        }

        .invoice-right-top h3 {
            color: #28a745;
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .invoice-right-top p {
            font-size: 14px;
            color: #666;
        }

        /* Table */
        .order_details {
            margin-bottom: 30px;
        }

        .table-header {
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-bottom: none;
        }

        .table-header h5 {
            margin: 0;
            color: #333;
            font-size: 16px;
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .table thead th {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tfoot th,
        .table tfoot td {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .table tfoot .empty {
            border: none;
            background: none;
        }

        .text-right {
            text-align: right;
        }

        /* Footer */
        .thanks {
            margin: 30px 0;
        }

        .thanks h4 {
            color: #28a745;
            font-size: 22px;
            font-weight: normal;
            text-align: center;
        }

        .authority {
            float: right;
            margin-top: 40px;
            text-align: center;
        }

        .authority p {
            margin-bottom: 10px;
            color: #666;
        }

        .authority h5 {
            color: #28a745;
            font-size: 14px;
            margin: 0;
        }

        /* Responsive */
        @media print {
            body {
                font-size: 12px;
            }

            .container {
                padding: 10px;
            }
        }
    </style>
</head>

<body>

    @if ($order)
        <div class="container">
            <div class="invoice-header clearfix">
                <div class="site-logo">
                    <img src="{{ public_path('backend/img/avatar.png') }}" alt="Logo">
                </div>
                <div class="site-address">
                    <h4>{{ env('APP_NAME') }}</h4>
                    <p>{{ env('APP_ADDRESS') }}</p>
                    <p>Số điện thoại:0983 691 895 <a href="tel:{{ env('APP_PHONE') }}">{{ env('APP_PHONE') }}</a></p>
                    <p>Email:codyhealth2023@gmail.com <a href="mailto:{{ env('APP_EMAIL') }}">{{ env('APP_EMAIL') }}</a>
                    </p>
                </div>
            </div>

            <div class="invoice-description clearfix">
                <div class="invoice-left-top">
                    <h6>Hóa đơn cho</h6>
                    <h3>{{ $order->first_name }} {{ $order->last_name }}</h3>
                    <div class="address">
                        <p><strong>Country:</strong> {{ $order->country }}</p>
                        <p><strong>Địa chỉ:</strong>
                            {{ $order->address1 ?? 'N/A' }}{{ $order->address2 ? ' hoặc ' . $order->address2 : '' }}
                        </p>
                        <p><strong>Số điện thoại:</strong> {{ $order->phone }}</p>
                        <p><strong>Email:</strong> {{ $order->email ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="invoice-right-top">
                    <h3>Hóa đơn #{{ $order->order_number }}</h3>
                    <p>{{ $order->created_at->format('D d M Y') }}</p>
                </div>
            </div>

            <section class="order_details">
                <div class="table-header">
                    <h5>Chi tiết đơn hàng</h5>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Sản phẩm</th>
                            <th style="width: 20%;">Số lượng</th>
                            <th style="width: 30%;">Tổng cộng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->cart_info as $cart)
                            @php
                                $product = DB::table('products')
                                    ->select('title')
                                    ->where('id', $cart->product_id)
                                    ->first();
                            @endphp
                            <tr>
                                <td>{{ $product->title ?? 'N/A' }}</td>
                                <td class="text-right">x{{ $cart->quantity }}</td>
                                <td class="text-right">{{ number_format($cart->price, 0, ',', '.') }}đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="empty"></td>
                            <td class="text-right"><strong>Tổng cộng:</strong></td>
                            <td class="text-right">
                                <strong>{{ number_format($order->sub_total, 0, ',', '.') }}đ</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="empty"></td>
                            @php
                                $shipping_charge = DB::table('shippings')
                                    ->where('id', $order->shipping_id)
                                    ->pluck('price')
                                    ->first();
                            @endphp
                            <td class="text-right"><strong>Vận chuyển:</strong></td>
                            <td class="text-right">
                                <strong>{{ number_format($shipping_charge ?? 0, 0, ',', '.') }}đ</strong>
                            </td>
                        </tr>
                        <tr style="background-color: #e9ecef;">
                            <td class="empty"></td>
                            <td class="text-right"><strong>Tổng cộng:</strong></td>
                            <td class="text-right"><strong
                                    style="color: #28a745; font-size: 16px;">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            <div class="thanks">
                <h4>Cảm ơn bạn đã kinh doanh !!</h4>
            </div>

            <div class="authority">
                <p>-----------------------------------</p>
                <h5>Chữ ký của cơ quan có thẩm quyền:</h5>
            </div>

            <div class="clearfix"></div>
        </div>
    @else
        <div class="container">
            <h5 style="color: #dc3545; text-align: center; margin-top: 50px;">Đơn hàng không hợp lệ</h5>
        </div>
    @endif

</body>

</html>
