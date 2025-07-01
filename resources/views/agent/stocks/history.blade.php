@extends('agent.layouts.master')

@section('title', 'Lịch sử kho')

@section('main-content')
<div class="dashboard-container">
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="welcome-card animate__animated animate__fadeInDown mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">📜 Lịch sử nhập / xuất kho</h1>
                    <p class="mb-0 opacity-75">Xem lại lịch sử điều chỉnh kho của bạn</p>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="recent-orders-card animate__animated animate__fadeInUp">
            <div class="card-header-custom">
                <h6 class="mb-0">
                    <i class="fas fa-history mr-2"></i>Lịch sử kho
                    <span class="badge badge-light ml-2">{{ $histories->count() }} lần</span>
                </h6>
            </div>

            <div class="card-body p-0">
                @if($histories->count())
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag mr-2"></i>#</th>
                                <th><i class="fas fa-box mr-2"></i>Sản phẩm</th>
                                <th><i class="fas fa-random mr-2"></i>Loại</th>
                                <th><i class="fas fa-layer-group mr-2"></i>Số lượng</th>
                                <th><i class="fas fa-sticky-note mr-2"></i>Ghi chú</th>
                                <th><i class="fas fa-clock mr-2"></i>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($histories as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->product->title ?? 'Không rõ' }}</strong><br>
                                    <small class="text-muted">ID: {{ $item->product_id }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-modern badge-{{ $item->action == 'import' ? 'success' : 'danger' }}">
                                        {{ $item->action == 'import' ? 'Nhập hàng' : 'Xuất kho' }}
                                    </span>
                                </td>
                                <td><strong>{{ $item->quantity }}</strong></td>
                                <td>{{ $item->note ?? '-' }}</td>
                                <td>
                                    <div>{{ $item->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $item->created_at->format('H:i') }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <h6>Chưa có lịch sử kho</h6>
                    <p>Hệ thống chưa ghi nhận hoạt động nhập / xuất hàng nào</p>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
