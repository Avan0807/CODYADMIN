@extends('agent.layouts.master')

@section('title', 'Thống kê hoa hồng')

@section('main-content')
<div class="dashboard-container">
    <div class="container-fluid">
        
        <!-- Page Heading -->
        <div class="welcome-card animate__animated animate__fadeInDown mb-4">
            <div class="d-sm-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 mb-0">💰 Thống kê hoa hồng</h1>
                    <p class="mb-0 opacity-75">Theo dõi chi tiết thu nhập và hoa hồng của bạn</p>
                </div>
                <div class="d-flex">
                    <a href="{{ route('agentcommissions.export') }}" class="quick-action-btn mr-2">
                        <i class="fas fa-download mr-2"></i>Xuất báo cáo
                    </a>
                    <a href="{{ route('agent.dashboard') }}" class="quick-action-btn">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Overview Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stats-card success animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                    <div class="stats-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">₫{{ number_format($stats['total_received']) }}</span>
                    </div>
                    <div class="stats-label">Tổng Tiền Đã Nhận</div>
                    <div class="stats-change">
                        <i class="fas fa-check-circle mr-1"></i>Đã thanh toán
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stats-card primary animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-month"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">₫{{ number_format($stats['this_month']) }}</span>
                    </div>
                    <div class="stats-label">Hoa Hồng Tháng Này</div>
                    <div class="stats-change">
                        @if($stats['growth_rate'] > 0)
                            <i class="fas fa-arrow-up mr-1 text-success"></i>+{{ number_format($stats['growth_rate'], 1) }}% so với tháng trước
                        @elseif($stats['growth_rate'] < 0)
                            <i class="fas fa-arrow-down mr-1 text-danger"></i>{{ number_format($stats['growth_rate'], 1) }}% so với tháng trước
                        @else
                            <i class="fas fa-minus mr-1"></i>Không thay đổi
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stats-card warning animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                    <div class="stats-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">₫{{ number_format($stats['total_pending']) }}</span>
                    </div>
                    <div class="stats-label">Chờ Thanh Toán</div>
                    <div class="stats-change">
                        <i class="fas fa-clock mr-1"></i>Đang xử lý
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stats-card info animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                    <div class="stats-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">₫{{ number_format($stats['total_all_time']) }}</span>
                    </div>
                    <div class="stats-label">Tổng Hoa Hồng</div>
                    <div class="stats-change">
                        <i class="fas fa-infinity mr-1"></i>Từ trước đến nay
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="chart-card animate__animated animate__fadeInUp">
                    <div class="p-3">
                        <h6 class="text-white mb-2">
                            <i class="fas fa-calendar-day mr-2"></i>Hôm nay
                        </h6>
                        <div class="h4 text-white mb-1">₫{{ number_format($stats['today']) }}</div>
                        <small class="text-white-50">Hoa hồng hôm nay</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="chart-card animate__animated animate__fadeInUp">
                    <div class="p-3">
                        <h6 class="text-white mb-2">
                            <i class="fas fa-calendar-week mr-2"></i>Tuần này
                        </h6>
                        <div class="h4 text-white mb-1">₫{{ number_format($stats['this_week']) }}</div>
                        <small class="text-white-50">Hoa hồng tuần này</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="chart-card animate__animated animate__fadeInUp">
                    <div class="p-3">
                        <h6 class="text-white mb-2">
                            <i class="fas fa-calculator mr-2"></i>Trung bình
                        </h6>
                        <div class="h4 text-white mb-1">₫{{ number_format($stats['avg_per_order']) }}</div>
                        <small class="text-white-50">Hoa hồng/đơn hàng</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="chart-card animate__animated animate__fadeInUp">
                    <div class="p-3">
                        <h6 class="text-white mb-2">
                            <i class="fas fa-shopping-cart mr-2"></i>Đơn hàng
                        </h6>
                        <div class="h4 text-white mb-1">{{ number_format($stats['orders_with_commission']) }}</div>
                        <small class="text-white-50">Đơn có hoa hồng</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Monthly Chart -->
            <div class="col-xl-8">
                <div class="recent-orders-card animate__animated animate__fadeInLeft">
                    <div class="card-header-custom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-area mr-2"></i>Biểu đồ hoa hồng 12 tháng
                            </h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-light btn-sm active" onclick="loadChart('monthly')">Tháng</button>
                                <button type="button" class="btn btn-outline-light btn-sm" onclick="loadChart('weekly')">Tuần</button>
                                <button type="button" class="btn btn-outline-light btn-sm" onclick="loadChart('daily')">Ngày</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="commissionChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="col-xl-4">
                <div class="recent-orders-card animate__animated animate__fadeInRight">
                    <div class="card-header-custom">
                        <h6 class="mb-0">
                            <i class="fas fa-trophy mr-2"></i>Top sản phẩm
                        </h6>
                    </div>
                    <div class="card-body">
                        @forelse($topProducts as $index => $product)
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <span class="badge badge-{{ $index < 3 ? ['warning', 'secondary', 'light'][$index] : 'light' }} font-weight-bold">
                                        #{{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ Str::limit($product->product->title ?? 'N/A', 20) }}</div>
                                    <small class="text-muted">{{ $product->orders_count }} đơn hàng</small>
                                </div>
                                <div class="text-right">
                                    <div class="font-weight-bold text-success">₫{{ number_format($product->total_commission) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-box fa-2x mb-2"></i>
                                <p>Chưa có sản phẩm nào</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Commissions -->
        <div class="recent-orders-card animate__animated animate__fadeInUp">
            <div class="card-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-history mr-2"></i>Lịch sử hoa hồng gần đây
                        <span class="badge badge-light ml-2">{{ $recentCommissions->total() }} giao dịch</span>
                    </h6>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar mr-2"></i>Ngày</th>
                                <th><i class="fas fa-hashtag mr-2"></i>Mã Đơn</th>
                                <th><i class="fas fa-box mr-2"></i>Sản Phẩm</th>
                                <th><i class="fas fa-user mr-2"></i>Khách Hàng</th>
                                <th><i class="fas fa-coins mr-2"></i>Hoa Hồng</th>
                                <th><i class="fas fa-info-circle mr-2"></i>Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCommissions as $commission)
                            <tr>
                                <td>
                                    <div>{{ $commission->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $commission->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <strong class="text-primary">#{{ $commission->order->order_number ?? 'ORD' . str_pad($commission->order_id, 4, '0', STR_PAD_LEFT) }}</strong>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ Str::limit($commission->product->title ?? 'Sản phẩm', 30) }}</div>
                                    <small class="text-muted">₫{{ number_format($commission->product->price ?? 0) }}</small>
                                </td>
                                <td>
                                    <div>{{ $commission->order->first_name ?? '' }} {{ $commission->order->last_name ?? '' }}</div>
                                    <small class="text-muted">{{ $commission->order->email ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-success">
                                        ₫{{ number_format($commission->commission) }}
                                    </div>
                                    <small class="text-muted">{{ $commission->commission_percentage }}%</small>
                                </td>
                                <td>
                                    @if($commission->status == 'paid')
                                        <span class="badge badge-modern badge-success">
                                            <i class="fas fa-check mr-1"></i>Đã thanh toán
                                        </span>
                                        @if($commission->paid_at)
                                            <br><small class="text-muted">{{ $commission->paid_at->format('d/m/Y') }}</small>
                                        @endif
                                    @elseif($commission->status == 'cancelled')
                                        <span class="badge badge-modern badge-danger">
                                            <i class="fas fa-times mr-1"></i>Đã hủy
                                        </span>
                                    @else
                                        <span class="badge badge-modern badge-warning">
                                            <i class="fas fa-clock mr-1"></i>Chờ thanh toán
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-coins fa-3x text-muted mb-3"></i>
                                    <div class="text-muted">
                                        <h6>Chưa có hoa hồng nào</h6>
                                        <p>Hãy tạo link affiliate để bắt đầu kiếm hoa hồng!</p>
                                        <a href="{{ route('agent.links.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus mr-2"></i>Tạo Link Ngay
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($recentCommissions->hasPages())
                <div class="px-3 py-3 border-top">
                    {{ $recentCommissions->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
.animate-counter {
    display: inline-block;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let commissionChart;

document.addEventListener('DOMContentLoaded', function() {
    const defaultBtn = document.querySelector('.btn-group .btn.active');
    loadChart(defaultBtn, 'monthly');
});


function loadChart(button, type) {
    // Reset class active
    document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');

    fetch(`{{ route('agentcommissions.chart') }}?type=${type}`)
        .then(response => response.json())
        .then(data => {
            renderChart(data.data, type);
        })
        .catch(error => console.error('Error:', error));
}


function renderChart(data, type) {
    const ctx = document.getElementById('commissionChart').getContext('2d');
    
    if (commissionChart) {
        commissionChart.destroy();
    }

    const labels = data.map(item => {
        if (type === 'monthly') return item.month_name;
        if (type === 'weekly') return item.week_name;
        return item.date_name;
    });

    const values = data.map(item => item.total);

    commissionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Hoa hồng (₫)',
                data: values,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₫' + value.toLocaleString();
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6
                }
            }
        }
    });
}

// Counter animation
function animateCounters() {
    const counters = document.querySelectorAll('.animate-counter');
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/[₫,]/g, ''));
        const increment = target / 100;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            counter.textContent = '₫' + Math.floor(current).toLocaleString();
        }, 20);
    });
}

// Start counter animation when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(animateCounters, 500);
});
</script>
@endpush