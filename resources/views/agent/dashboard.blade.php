@extends('agent.layouts.master')
@section('title', 'Dashboard - Đại lý')

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
    .dashboard-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 30px;
        color: white;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stats-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1), transparent);
        pointer-events: none;
    }
    
    .stats-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stats-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stats-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stats-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stats-card.danger { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    
    .stats-icon {
        font-size: 3rem;
        opacity: 0.8;
        position: absolute;
        right: 20px;
        top: 20px;
    }
    
    .stats-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }
    
    .stats-label {
        font-size: 1rem;
        opacity: 0.9;
        font-weight: 500;
    }
    
    .stats-change {
        font-size: 0.9rem;
        margin-top: 10px;
        padding: 5px 10px;
        background: rgba(255,255,255,0.2);
        border-radius: 15px;
        display: inline-block;
    }
    
    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .welcome-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    
    .chart-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: none;
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }
    
    .chart-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    }
    
    .recent-orders-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: none;
    }
    
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px 30px;
        border: none;
        position: relative;
    }
    
    .card-header-custom h6 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .table-modern {
        margin: 0;
    }
    
    .table-modern th {
        background: #f8f9fc;
        border: none;
        padding: 20px;
        font-weight: 600;
        color: #5a5c69;
        font-size: 0.9rem;
    }
    
    .table-modern td {
        padding: 20px;
        border: none;
        border-bottom: 1px solid #e3e6f0;
        vertical-align: middle;
    }
    
    .table-modern tbody tr:hover {
        background: #f8f9fc;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }
    
    .badge-modern {
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.8rem;
    }
    
    .quick-action-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 15px;
        padding: 15px 25px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        margin: 5px;
    }
    
    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        color: white;
        text-decoration: none;
    }
    
    .progress-modern {
        height: 8px;
        border-radius: 10px;
        background: #e3e6f0;
        overflow: hidden;
    }
    
    .progress-bar-modern {
        height: 100%;
        border-radius: 10px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 0.6s ease;
    }
    
    .notification-bell {
        position: relative;
        font-size: 1.5rem;
        color: #5a5c69;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    
    .notification-bell:hover {
        color: #667eea;
    }
    
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: bold;
    }
    
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 10px;
        }
        
        .stats-card {
            margin-bottom: 20px;
        }
        
        .stats-value {
            font-size: 2rem;
        }
        
        .stats-icon {
            font-size: 2rem;
        }
    }
    
    .animate-counter {
        display: inline-block;
    }
    
    .loading-shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endpush

@section('main-content')
<div class="dashboard-container">
    <div class="container-fluid">
        
        <!-- Welcome Section -->
        <div class="welcome-card animate__animated animate__fadeInDown">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="h2 mb-2">👋 Chào mừng trở lại, {{ Auth::guard('agent')->user()->name }}!</h1>
                    <p class="mb-3 opacity-75">Hôm nay là ngày tuyệt vời để tăng doanh thu. Hãy xem thống kê mới nhất của bạn!</p>
                    <div class="d-flex flex-wrap">
                        <a href="{{ route('agent.links.create') }}" class="quick-action-btn">
                            <i class="fas fa-plus mr-2"></i>Tạo Link Mới
                        </a>
                        <a href="{{ route('agent.links.index') }}" class="quick-action-btn">
                            <i class="fas fa-link mr-2"></i>Quản Lý Links
                        </a>
                        <a href="#" class="quick-action-btn">
                            <i class="fas fa-chart-bar mr-2"></i>Xem Báo Cáo
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 text-right">
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">{{ $pendingNotifications ?? 3 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards Row -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card success animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                    <div class="stats-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">₫{{ number_format($totalRevenue ?? 0) }}</span>
                    </div>
                    <div class="stats-label">Tổng Doanh Thu</div>
                    <div class="stats-change">
                        <i class="fas fa-arrow-up mr-1"></i>+12.5% tuần này
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card primary animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                    <div class="stats-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">{{ $totalOrders ?? 0 }}</span>
                    </div>
                    <div class="stats-label">Tổng Đơn Hàng</div>
                    <div class="stats-change">
                        <i class="fas fa-arrow-up mr-1"></i>+8.2% so với tháng trước
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card info animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                    <div class="stats-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">₫{{ number_format($totalCommission ?? 0) }}</span>
                    </div>
                    <div class="stats-label">Tổng Hoa Hồng</div>
                    <div class="stats-change">
                        <i class="fas fa-arrow-up mr-1"></i>+15.7% tháng này
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card warning animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                    <div class="stats-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="stats-value">
                        <span class="animate-counter">{{ $totalLinks ?? 0 }}</span>
                    </div>
                    <div class="stats-label">Link Affiliate</div>
                    <div class="stats-change">
                        <i class="fas fa-plus mr-1"></i>{{ $newLinksThisWeek ?? 5 }} link mới tuần này
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats Row -->
        <div class="row">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="chart-card animate__animated animate__fadeInLeft">
                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-check-circle mr-2"></i>Đã Thanh Toán
                    </h6>
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="text-success mb-1">₫{{ number_format($paidCommission ?? 0) }}</h3>
                            <div class="progress-modern">
                                <div class="progress-bar-modern" style="width: {{ $paidCommission > 0 ? ($paidCommission / ($totalCommission ?: 1)) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="ml-3">
                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="chart-card animate__animated animate__fadeInUp">
                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-clock mr-2"></i>Chờ Thanh Toán
                    </h6>
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="text-warning mb-1">₫{{ number_format($pendingCommission ?? 0) }}</h3>
                            <div class="progress-modern">
                                <div class="progress-bar-modern" style="width: {{ $pendingCommission > 0 ? ($pendingCommission / ($totalCommission ?: 1)) * 100 : 0 }}%; background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);"></div>
                            </div>
                        </div>
                        <div class="ml-3">
                            <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="chart-card animate__animated animate__fadeInRight">
                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-calendar-day mr-2"></i>Hôm Nay
                    </h6>
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="text-info mb-1">{{ $ordersToday ?? 0 }}</h3>
                            <small class="text-muted">đơn hàng mới</small>
                        </div>
                        <div class="ml-3">
                            <i class="fas fa-shopping-bag fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->


        <!-- Recent Orders Table -->
        <div class="row">
            <div class="col-12">
                <div class="recent-orders-card animate__animated animate__fadeInUp">
                    <div class="card-header-custom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-list-alt mr-2"></i>Đơn Hàng Gần Đây
                            </h6>
                            <a href="#" class="text-white">
                                <i class="fas fa-external-link-alt mr-1"></i>Xem tất cả
                            </a>
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
            @forelse($recentOrders ?? [] as $agentOrder)
            <tr>
                <td>
                    <strong class="text-primary">#{{ $agentOrder->order->order_number ?? 'ORD001' }}</strong>
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
                            <div class="font-weight-bold">{{ Str::limit($agentOrder->product->title ?? 'Sản phẩm mẫu', 30) }}</div>
                            <small class="text-muted">{{ number_format($agentOrder->product->price ?? 0, 0, ',', '.') }}đ</small>
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
                        <button class="btn btn-outline-primary btn-sm" title="Xem chi tiết đơn hàng" onclick="viewOrder({{ $agentOrder->order_id }})">
                            <i class="fas fa-eye"></i>
                        </button>
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
                        <h6>Chưa có đơn hàng nào</h6>
                        <p>Hãy tạo link affiliate đầu tiên để bắt đầu kiếm hoa hồng!</p>
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
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Counter Animation
    function animateCounter(element, target) {
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current).toLocaleString('vi-VN');
        }, 20);
    }
    
    // Animate all counters
    setTimeout(() => {
        document.querySelectorAll('.animate-counter').forEach(counter => {
            const text = counter.textContent.replace(/[^\d]/g, '');
            if (text) {
                const target = parseInt(text);
                const prefix = counter.textContent.replace(text, '');
                counter.textContent = prefix + '0';
                animateCounter(counter, target);
            }
        });
    }, 500);

    // Revenue Chart
    const revenueCtx = document.getElementById('agentRevenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueChart->keys()->toArray() ?? ['T1', 'T2', 'T3', 'T4', 'T5', 'T6']) !!},
            datasets: [{
                label: 'Doanh thu',
                data: {!! json_encode($revenueChart->values()->toArray() ?? [1000000, 1500000, 1200000, 1800000, 2200000, 2500000]) !!},
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: false 
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#667eea',
                    borderWidth: 1,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            return 'Doanh thu: ₫' + context.raw.toLocaleString('vi-VN');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#858796' }
                },
                y: {
                    grid: { 
                        color: 'rgba(0,0,0,0.05)',
                        drawBorder: false 
                    },
                    ticks: {
                        color: '#858796',
                        callback: function(value) {
                            return '₫' + value.toLocaleString('vi-VN');
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Order Status Pie Chart
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Đã thanh toán', 'Chờ xử lý', 'Đã hủy'],
            datasets: [{
                data: [{{ $paidOrders ?? 65 }}, {{ $pendingOrders ?? 25 }}, {{ $cancelledOrders ?? 10 }}],
                backgroundColor: [
                    'rgba(17, 153, 142, 0.8)',
                    'rgba(240, 147, 251, 0.8)',
                    'rgba(231, 76, 60, 0.8)'
                ],
                borderColor: [
                    'rgba(17, 153, 142, 1)',
                    'rgba(240, 147, 251, 1)',
                    'rgba(231, 76, 60, 1)'
                ],
                borderWidth: 2,
                cutout: '60%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        }
    });

    // Notification click handler
    document.querySelector('.notification-bell').addEventListener('click', function() {
        // Add notification dropdown logic here
        alert('Bạn có ' + ({{ $pendingNotifications ?? 3 }}) + ' thông báo mới!');
    });

});
</script>
@endpush