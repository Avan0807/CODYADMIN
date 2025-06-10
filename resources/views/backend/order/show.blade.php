@extends('backend.layouts.master')

@section('title','Chi tiết đơn hàng')

@section('main-content')
<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-primary">Chi tiết đơn hàng #{{$order->order_number}}</h6>
    <div>
      <a href="{{route('order.pdf',$order->id)}}" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-download fa-sm text-white-50"></i> Xuất PDF
      </a>
      <a href="{{route('order.index')}}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Quay lại
      </a>
    </div>
  </div>
  
  <div class="card-body">
    @if($order)
    
    <!-- Order Summary Card -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-left-primary">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <h6 class="font-weight-bold text-primary">Mã đơn hàng</h6>
                <p class="mb-0">{{$order->order_number}}</p>
              </div>
              <div class="col-md-3">
                <h6 class="font-weight-bold text-success">Tổng tiền</h6>
                <p class="mb-0 h5 text-success">{{number_format($order->total_amount,0,',','.')}}đ</p>
              </div>
              <div class="col-md-3">
                <h6 class="font-weight-bold">Trạng thái</h6>
                <p class="mb-0">
                  @if($order->status=='new')
                    <span class="badge badge-primary badge-pill">MỚI</span>
                  @elseif($order->status=='process')
                    <span class="badge badge-warning badge-pill">XỬ LÝ</span>
                  @elseif($order->status=='delivered')
                    <span class="badge badge-success badge-pill">ĐÃ GIAO</span>
                  @else
                    <span class="badge badge-danger badge-pill">{{strtoupper($order->status)}}</span>
                  @endif
                </p>
              </div>
              <div class="col-md-3">
                <h6 class="font-weight-bold">Ngày đặt</h6>
                <p class="mb-0">{{$order->created_at->format('d/m/Y H:i')}}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="row">
      <!-- Thông tin đặt hàng -->
      <div class="col-lg-6 mb-4">
        <div class="card h-100">
          <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-shopping-cart mr-2"></i>THÔNG TIN ĐẶT HÀNG</h6>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tr>
                <td class="font-weight-bold" width="40%">Số đơn hàng:</td>
                <td>{{$order->order_number}}</td>
              </tr>
              <tr>
                <td class="font-weight-bold">Ngày đặt hàng:</td>
                <td>{{$order->created_at->format('d/m/Y')}} lúc {{$order->created_at->format('H:i')}}</td>
              </tr>
              <tr>
                <td class="font-weight-bold">Số lượng:</td>
                <td><span class="badge badge-info">{{$order->quantity}} sản phẩm</span></td>
              </tr>
              <tr>
                <td class="font-weight-bold">Phương thức vận chuyển:</td>
                <td>
                  <span class="badge badge-secondary">{{$order->getShippingMethod()}}</span>
                  @if($order->canTrackGHN())
                    <br><small class="text-info">Mã vận đơn: {{$order->ghn_order_code}}</small>
                  @endif
                </td>
              </tr>
              <tr>
                <td class="font-weight-bold">Phí vận chuyển:</td>
                <td class="text-warning font-weight-bold">{{number_format($order->shipping_cost,0,',','.')}}đ</td>
              </tr>
              @if($order->coupon > 0)
              <tr>
                <td class="font-weight-bold">Phiếu giảm giá:</td>
                <td class="text-success">-{{number_format($order->coupon,0,',','.')}}đ</td>
              </tr>
              @endif
              <tr class="border-top">
                <td class="font-weight-bold h6">Tổng số tiền:</td>
                <td class="font-weight-bold h6 text-success">{{number_format($order->total_amount,0,',','.')}}đ</td>
              </tr>
              <tr>
                <td class="font-weight-bold">Phương thức thanh toán:</td>
                <td>
                  @if($order->payment_method == 'cod')
                    <span class="badge badge-warning">Thanh toán khi nhận hàng</span>
                  @elseif($order->payment_method == 'paypal')
                    <span class="badge badge-info">Paypal</span>
                  @elseif($order->payment_method == 'cardpay')
                    <span class="badge badge-primary">Thanh toán bằng thẻ</span>
                  @endif
                </td>
              </tr>
              <tr>
                <td class="font-weight-bold">Trạng thái thanh toán:</td>
                <td>
                  @if($order->payment_status == 'paid')
                    <span class="badge badge-success">Đã thanh toán</span>
                  @elseif($order->payment_status == 'unpaid')
                    <span class="badge badge-danger">Chưa thanh toán</span>
                  @else
                    <span class="badge badge-secondary">{{$order->payment_status}}</span>
                  @endif
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <!-- Thông tin vận chuyển -->
      <div class="col-lg-6 mb-4">
        <div class="card h-100">
          <div class="card-header bg-info text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-shipping-fast mr-2"></i>THÔNG TIN VẬN CHUYỂN</h6>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tr>
                <td class="font-weight-bold" width="40%">Họ và tên:</td>
                <td>{{$order->first_name}} {{$order->last_name}}</td>
              </tr>
              <tr>
                <td class="font-weight-bold">Email:</td>
                <td>{{$order->email}}</td>
              </tr>
              <tr>
                <td class="font-weight-bold">Số điện thoại:</td>
                <td>{{$order->phone}}</td>
              </tr>
              <tr>
                <td class="font-weight-bold">Địa chỉ:</td>
                <td>{{$order->getFullAddress()}}</td>
              </tr>
              <tr>
                <td class="font-weight-bold">Quốc gia:</td>
                <td>{{$order->country}}</td>
              </tr>
              @if($order->post_code)
              <tr>
                <td class="font-weight-bold">Mã bưu chính:</td>
                <td>{{$order->post_code}}</td>
              </tr>
              @endif
              
              {{-- Thông tin GHN địa chỉ --}}
              @if($order->ghn_to_district_id || $order->ghn_to_ward_code)
              <tr class="border-top">
                <td class="font-weight-bold">Địa chỉ GHN:</td>
                <td>
                  @if($order->ghn_to_district_id)
                    <div><strong>Quận/Huyện ID:</strong> {{$order->ghn_to_district_id}}</div>
                  @endif
                  @if($order->ghn_to_ward_code)
                    <div><strong>Phường/Xã Code:</strong> {{$order->ghn_to_ward_code}}</div>
                  @endif
                </td>
              </tr>
              @endif
              
              {{-- Thông tin dịch vụ GHN --}}
              @if($order->ghn_service_id)
              <tr>
                <td class="font-weight-bold">Dịch vụ GHN:</td>
                <td>
                  <span class="badge badge-secondary">{{$order->getGHNServiceName()}}</span>
                  <small class="text-muted d-block">Service ID: {{$order->ghn_service_id}}</small>
                </td>
              </tr>
              @endif
              
              @if($order->canTrackGHN())
              <tr>
                <td class="font-weight-bold">Tracking GHN:</td>
                <td>
                  <div class="mb-2">
                    <a href="{{$order->getTrackingUrl()}}" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-external-link-alt"></i> Theo dõi đơn hàng
                    </a>
                  </div>
                  <div>
                    <strong>Mã vận đơn:</strong> 
                    <span class="badge badge-info">{{$order->ghn_order_code}}</span>
                  </div>
                  @if($order->ghn_status)
                    <div class="mt-1">
                      <strong>Trạng thái GHN:</strong> 
                      <span class="badge badge-{{$order->ghn_status == 'delivered' ? 'success' : 'warning'}}">
                        {{ucfirst($order->ghn_status)}}
                      </span>
                    </div>
                  @endif
                </td>
              </tr>
              @endif
            </table>
            
            {{-- Box thông tin doctor --}}
            @if($order->doctor_id)
            <div class="alert alert-info mt-3">
              <i class="fas fa-user-md"></i> <strong>Bác sĩ giới thiệu:</strong> ID #{{$order->doctor_id}}
              @if($order->total_commission > 0)
                <br><small>Hoa hồng: {{number_format($order->total_commission,0,',','.')}}đ</small>
              @endif
            </div>
            @endif
            
            {{-- Box thông tin shipping cost --}}
            @if($order->shipping_cost > 0)
            <div class="alert alert-secondary mt-3">
              <i class="fas fa-truck"></i> <strong>Chi phí vận chuyển:</strong> 
              <span class="font-weight-bold">{{number_format($order->shipping_cost,0,',','.')}}đ</span>
              <br><small>Phương thức: {{$order->getShippingMethod()}}</small>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Sản phẩm trong đơn hàng -->
    @if($order->cartInfo && $order->cartInfo->count() > 0)
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header bg-success text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-box mr-2"></i>SẢN PHẨM TRONG ĐƠN HÀNG</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="bg-light">
                  <tr>
                    <th width="10%">#</th>
                    <th width="50%">Sản phẩm</th>
                    <th width="15%">Đơn giá</th>
                    <th width="10%">Số lượng</th>
                    <th width="15%">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($order->cartInfo as $cart)
                  <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>
                      <div class="font-weight-bold">{{$cart->product->title ?? 'Sản phẩm đã xóa'}}</div>
                      @if($cart->product && $cart->product->slug)
                        <small class="text-muted">SKU: {{$cart->product->slug}}</small>
                      @endif
                    </td>
                    <td class="text-right">{{number_format($cart->price,0,',','.')}}đ</td>
                    <td class="text-center">
                      <span class="badge badge-primary">{{$cart->quantity}}</span>
                    </td>
                    <td class="text-right font-weight-bold">{{number_format($cart->amount,0,',','.')}}đ</td>
                  </tr>
                  @endforeach
                </tbody>
                <tfoot class="bg-light">
                  <tr>
                    <td colspan="4" class="text-right font-weight-bold">Tổng cộng:</td>
                    <td class="text-right font-weight-bold text-success h6">{{number_format($order->total_amount,0,',','.')}}đ</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

    <!-- Action buttons -->
    <div class="row mt-4">
      <div class="col-12 text-center">
        <a href="{{route('order.edit',$order->id)}}" class="btn btn-primary">
          <i class="fas fa-edit"></i> Chỉnh sửa đơn hàng
        </a>
        @if($order->canTrackGHN())
        <a href="{{$order->getTrackingUrl()}}" target="_blank" class="btn btn-info">
          <i class="fas fa-truck"></i> Theo dõi vận chuyển
        </a>
        @endif
        <a href="{{route('order.pdf',$order->id)}}" class="btn btn-success">
          <i class="fas fa-file-pdf"></i> Tải PDF
        </a>
      </div>
    </div>

    @endif
  </div>
</div>
@endsection

@push('styles')
<style>
.card {
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.table td {
  padding: 0.5rem;
  vertical-align: middle;
}

.border-left-primary {
  border-left: 0.25rem solid #4e73df !important;
}

.card-header {
  background-color: #f8f9fc;
  border-bottom: 1px solid #e3e6f0;
}

.badge-pill {
  border-radius: 50rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush