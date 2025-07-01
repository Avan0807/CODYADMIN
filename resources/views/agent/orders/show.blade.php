@extends('agent.layouts.master')

@section('title', 'Chi tiết đơn hàng')

@section('main-content')
<div class="dashboard-container">
    <div class="container-fluid">
        
        <!-- Page Heading -->
        <div class="welcome-card animate__animated animate__fadeInDown mb-4">
            <div class="d-sm-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 mb-0">📄 Chi tiết đơn hàng #{{ $agentOrder->order->order_number ?? 'ORD' . str_pad($agentOrder->order_id, 4, '0', STR_PAD_LEFT) }}</h1>
                    <p class="mb-0 opacity-75">Thông tin chi tiết về đơn hàng và hoa hồng</p>
                </div>
                <div class="d-flex">
                    <a href="{{ route('agentorder.index') }}" class="quick-action-btn mr-2">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="row">
            <div class="col-12">
                <div class="recent-orders-card animate__animated animate__fadeInUp">
                    <div class="card-header-custom">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle mr-2"></i>Thông tin đơn hàng
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Thông tin sản phẩm</h6>
                                <p><strong>Tên:</strong> {{ $agentOrder->product->title ?? 'N/A' }}</p>
                                <p><strong>Giá:</strong>{{ number_format($agentOrder->product->price ?? 0) }} ₫</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Thông tin khách hàng</h6>
                                <p><strong>Tên:</strong> {{ $agentOrder->order->first_name ?? '' }} {{ $agentOrder->order->last_name ?? '' }}</p>
                                <p><strong>Phone:</strong> {{ $agentOrder->order->phone ?? 'N/A' }}</p>
                                <p><strong>Email:</strong> {{ $agentOrder->order->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Hoa hồng</h6>
                                <p class="h4 text-success">{{ number_format($agentOrder->commission) }} ₫</p>
                                <small>{{ $agentOrder->commission_percentage }}% commission</small>
                            </div>
                            <div class="col-md-4">
                                <h6>Trạng thái</h6>
                                @if($agentOrder->status == 'paid')
                                    <span class="badge badge-success">Đã thanh toán</span>
                                @elseif($agentOrder->status == 'cancelled')
                                    <span class="badge badge-danger">Đã hủy</span>
                                @else
                                    <span class="badge badge-warning">Chờ thanh toán</span>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <h6>Ngày tạo</h6>
                                <p>{{ $agentOrder->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection