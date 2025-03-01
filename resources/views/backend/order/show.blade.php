@extends('backend.layouts.master')

@section('title','Order Detail')

@section('main-content')
<div class="card">
<h5 class="card-header">Đơn hàng<a href="{{route('order.pdf',$order->id)}}" class=" btn btn-sm btn-primary shadow-sm float-right"><i class="fas fa-download fa-sm text-white-50"></i> Xuất PDF</a>
  </h5>
  <div class="card-body">
    @if($order)
    <table class="table table-striped table-hover">
      <thead>
        <tr>
                <th>#</th>
                <th>Mã đơn hàng</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Số lượng</th>
                <th>Phí</th>
                <th>Tổng cộng</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr>
            <td>{{$order->id}}</td>
            <td>{{$order->order_number}}</td>
            <td>{{$order->first_name}} {{$order->last_name}}</td>
            <td>{{$order->email}}</td>
            <td>{{$order->quantity}}</td>
            <td>{{number_format($order->shipping ? $order->shipping->price : 'N/A',0,',','.')}}đ</td>
            <td>{{number_format($order->total_amount,0,',','.')}}đ</td>
            <td>
                @if($order->status=='new')
                  <span class="badge badge-primary">MỚI</span>
                @elseif($order->status=='process')
                  <span class="badge badge-warning">XỬ LÝ</span>
                @elseif($order->status=='delivered')
                  <span class="badge badge-success">ĐÃ GIAO HÀNG</span>
                @else
                  <span class="badge badge-danger">{{$order->status}}</span>
                @endif
            </td>
            <td>
                <a href="{{route('order.edit',$order->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="edit" data-placement="bottom"><i class="fas fa-edit"></i></a>
                <form method="POST" action="{{route('order.destroy',[$order->id])}}">
                  @csrf
                  @method('delete')
                      <button class="btn btn-danger btn-sm dltBtn" data-id={{$order->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-trash-alt"></i></button>
                </form>
            </td>

        </tr>
      </tbody>
    </table>

    <section class="confirmation_part section_padding">
      <div class="order_boxes">
        <div class="row">
          <div class="col-lg-6 col-lx-4">
            <div class="order-info">
              <h4 class="text-center pb-4">THÔNG TIN ĐẶT HÀNG</h4>
              <table class="table">
                    <tr class="">
                        <td>Số đơn hàng</td>
                        <td> : {{$order->order_number}}</td>
                    </tr>
                    <tr>
                        <td>Ngày đặt hàng</td>
                        <td> : {{$order->created_at->format('D d M, Y')}} at {{$order->created_at->format('g : i a')}} </td>
                    </tr>
                    <tr>
                        <td>Số lượng</td>
                        <td> : {{$order->quantity}}</td>
                    </tr>
                    <tr>
                        <td>Trạng thái đơn hàng</td>
                        <td> : {{$order->status}}</td>
                    </tr>
                    <tr>
                      @php
                          $shipping_charge=DB::table('shippings')->where('id',$order->shipping_id)->pluck('price');
                      @endphp
                        <td>Phí vận chuyển</td>
                        <td> : {{$order->shipping ? $order->shipping->price : 'N/A'}}đ</td>
                    </tr>
                    <tr>
                      <td>Phiếu giảm giá</td>
                      <td> : {{number_format($order->coupon,0,',','.')}}đ</td>
                    </tr>
                    <tr>
                        <td>Tổng số tiền</td>
                        <td> : {{number_format($order->total_amount,0,',','.')}}đ</td>
                    </tr>
                    <tr>
                        <!-- <td>Payment Method</td>
                        <td> : @if($order->payment_method=='cod') Cash on Delivery @else Paypal @endif</td> -->
                        <td>Phương thức thanh toán</td>
                        <td> :
                            @if($order->payment_method == 'cod')
                                Thanh toán khi nhận hàng
                            @elseif($order->payment_method == 'paypal')
                                Paypal
                            @elseif($order->payment_method == 'cardpay')
                                Thanh toán bằng thẻ
                            @endif
                        </td>

                    </tr>
                    <tr>
                      <td>Trạng thái thanh toán</td>
                      <td> :
                          @if($order->payment_status == 'paid')
                              <span class="badge badge-success">Trả</span>
                          @elseif($order->payment_status == 'unpaid')
                              <span class="badge badge-danger">Chưa thanh toán</span>
                          @else
                              {{$order->payment_status}}
                          @endif
                      </td>
                  </tr>

              </table>
            </div>
          </div>

          <div class="col-lg-6 col-lx-4">
            <div class="shipping-info">
              <h4 class="text-center pb-4">THÔNG TIN VẬN CHUYỂN</h4>
              <table class="table">
                    <tr class="">
                        <td>Họ và tên đầy đủ</td>
                        <td> : {{$order->first_name}} {{$order->last_name}}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td> : {{$order->email}}</td>
                    </tr>
                    <tr>
                        <td>Số điện thoại</td>
                        <td> : {{$order->phone}}</td>
                    </tr>
                    <tr>
                        <td>Địa chỉ</td>
                        <td> : {{$order->address1}}, {{$order->address2}}</td>
                    </tr>
                    <tr>
                        <td>Quốc gia</td>
                        <td> : {{$order->country}}</td>
                    </tr>
                    <tr>
                        <td>Mã bưu chính</td>
                        <td> : {{$order->post_code}}</td>
                    </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif

  </div>
</div>
@endsection

@push('styles')
<style>
    .order-info,.shipping-info{
        background:#ECECEC;
        padding:20px;
    }
    .order-info h4,.shipping-info h4{
        text-decoration: underline;
    }

</style>
@endpush
