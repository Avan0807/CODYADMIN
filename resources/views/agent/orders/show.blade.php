@extends('agent.layouts.master')

@section('title', 'Quản lý đơn hàng')

@section('main-content')
<div class="dashboard-container">
    <div class="container-fluid">
        
        <!-- Page Heading -->
        <div class="welcome-card animate__animated animate__fadeInDown mb-4">
            <div class="d-sm-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 mb-0">🛒 Quản lý đơn hàng</h1>
                    <p class="mb-0 opacity-75">Theo dõi và quản lý tất cả đơn hàng của bạn</p>
                </div>
                <div class="d-flex">
                    <a href="{{ route('agent.orders.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                       class="quick-action-btn mr-2">
                        <i class="fas fa-download mr-2"></i>Xuất Excel
                    </a>
                    <a href="{{ route('agent.links.create') }}" class="quick-action-btn">
                        <i class="fas fa-plus mr-2"></i>Tạo Link Mới
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stats-card primary animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                    <div class="stats-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">{{ $stats['total_orders'] ?? 0 }}</span>
                    </div>
                    <div class="stats-label">Tổng Đơn Hàng</div>
                    <div class="stats-change">
                        <i class="fas fa-calendar mr-1"></i>{{ $stats['orders_this_month'] ?? 0 }} đơn tháng này
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stats-card success animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                    <div class="stats-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">₫{{ number_format($stats['total_commission'] ?? 0) }}</span>
                    </div>
                    <div class="stats-label">Tổng Hoa Hồng</div>
                    <div class="stats-change">
                        <i class="fas fa-arrow-up mr-1"></i>₫{{ number_format($stats['commission_this_month'] ?? 0) }} tháng này
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stats-card info animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">₫{{ number_format($stats['paid_commission'] ?? 0) }}</span>
                    </div>
                    <div class="stats-label">Đã Thanh Toán</div>
                    <div class="stats-change">
                        <i class="fas fa-check mr-1"></i>{{ $stats['paid_orders'] ?? 0 }} đơn đã thanh toán
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stats-card warning animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                    <div class="stats-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">₫{{ number_format($stats['pending_commission'] ?? 0) }}</span>
                    </div>
                    <div class="stats-label">Chờ Thanh Toán</div>
                    <div class="stats-change">
                        <i class="fas fa-hourglass-half mr-1"></i>{{ $stats['pending_orders'] ?? 0 }} đơn chờ thanh toán
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="chart-card animate__animated animate__fadeInUp mb-4">
            <div class="p-3">
                <form method="GET" action="{{ route('agent.orders.index') }}" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label for="search" class="form-label text-white">Tìm kiếm</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Mã đơn, tên KH, sản phẩm...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="status" class="form-label text-white">Trạng thái</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Tất cả</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="date_from" class="form-label text-white">Từ ngày</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="date_to" class="form-label text-white">Đến ngày</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-light btn-sm">
                                    <i class="fas fa-filter mr-1"></i>Lọc
                                </button>
                                <a href="{{ route('agent.orders.index') }}" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-times mr-1"></i>Xóa lọc
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="recent-orders-card animate__animated animate__fadeInUp">
            <div class="card-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-list-alt mr-2"></i>Danh sách đơn hàng
                        <span class="badge badge-light ml-2">{{ $orders->total() }} đơn</span>
                    </h6>
                    <div class="d-flex align-items-center">
                        <span class="text-white-50 mr-3">Hiển thị {{ $orders->firstItem() }}-{{ $orders->lastItem() }} trong {{ $orders->total() }} đơn</span>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag mr-2"></i>Mã Đơn</th>
                                <th><i class="fas fa-box mr-2"></i>Sản Phẩm</th>
                                <th><i class="fas fa-user mr-2"></i>Khách Hàng</th>
                                <th><i class="fas fa-calendar mr-2"></i>Ngày Tạo</th>
                                <th><i class="fas fa-coins mr-2"></i>Hoa Hồng</th>
                                <th><i class="fas fa-info-circle mr-2"></i>Trạng Thái</th>
                                <th><i class="fas fa-cog mr-2"></i>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $agentOrder)
                            <tr>
                                <td>
                                    <strong class="text-primary">#{{ $agentOrder->order->order_number ?? 'ORD' . str_pad($agentOrder->order_id, 4, '0', STR_PAD_LEFT) }}</strong>
                                    <br><small class="text-muted">ID: {{ $agentOrder->order_id }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            @if($agentOrder->product->photo ?? false)
                                                <img src="{{ $agentOrder->product->photo }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-primary rounded" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-box text-white"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-weight-bold">{{ Str::limit($agentOrder->product->title ?? 'Sản phẩm', 25) }}</div>
                                            <small class="text-muted">₫{{ number_format($agentOrder->product->price ?? 0, 0, ',', '.') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $agentOrder->order->first_name ?? '' }} {{ $agentOrder->order->last_name ?? '' }}</div>
                                    <small class="text-muted">{{ $agentOrder->order->email ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <div>{{ $agentOrder->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $agentOrder->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-success">
                                        ₫{{ number_format($agentOrder->commission, 0, ',', '.') }}
                                    </div>
                                    <small class="text-muted">{{ $agentOrder->commission_percentage }}%</small>
                                </td>
                                <td>
                                    @if($agentOrder->status == 'paid')
                                        <span class="badge badge-modern badge-success">
                                            <i class="fas fa-check mr-1"></i>Đã thanh toán
                                        </span>
                                        @if($agentOrder->paid_at)
                                            <br><small class="text-muted">{{ $agentOrder->paid_at->format('d/m/Y') }}</small>
                                        @endif
                                    @elseif($agentOrder->status == 'cancelled')
                                        <span class="badge badge-modern badge-danger">
                                            <i class="fas fa-times mr-1"></i>Đã hủy
                                        </span>
                                    @else
                                        <span class="badge badge-modern badge-warning">
                                            <i class="fas fa-clock mr-1"></i>Chờ thanh toán
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('agent.orders.show', $agentOrder->id) }}" 
                                           class="btn btn-outline-primary btn-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($agentOrder->status == 'paid')
                                            <button class="btn btn-outline-success btn-sm" title="Đã thanh toán" disabled>
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <div class="text-muted">
                                        <h6>Không tìm thấy đơn hàng nào</h6>
                                        <p>{{ request()->hasAny(['search', 'status', 'date_from', 'date_to']) ? 'Thử thay đổi bộ lọc hoặc ' : '' }}Hãy tạo link affiliate để bắt đầu kiếm hoa hồng!</p>
                                        @if(!request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                                            <a href="{{ route('agent.links.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus mr-2"></i>Tạo Link Ngay
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($orders->hasPages())
                <div class="px-3 py-3 border-top">
                    {{ $orders->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto submit form khi thay đổi filter
document.getElementById('status').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

// Quick date filters
function setDateFilter(days) {
    const today = new Date();
    const fromDate = new Date(today);
    fromDate.setDate(today.getDate() - days);
    
    document.getElementById('date_from').value = fromDate.toISOString().split('T')[0];
    document.getElementById('date_to').value = today.toISOString().split('T')[0];
    document.getElementById('filterForm').submit();
}

// Clear search on ESC
document.getElementById('search').addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        this.value = '';
        document.getElementById('filterForm').submit();
    }
});
</script>
@endpush