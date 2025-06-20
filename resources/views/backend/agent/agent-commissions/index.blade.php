@extends('backend.layouts.master')

@section('main-content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
            @include('backend.layouts.notification')
        </div>
    </div>
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Báo cáo Hoa hồng Đại lý</h6>
        <div>
            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#exportModal">
                <i class="fas fa-download"></i> Xuất báo cáo
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label for="agentFilter">Đại lý</label>
                <select id="agentFilter" class="form-control">
                    <option value="">Tất cả đại lý</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="statusFilter">Trạng thái</label>
                <select id="statusFilter" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="pending">Chờ thanh toán</option>
                    <option value="paid">Đã thanh toán</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="fromDate">Từ ngày</label>
                <input type="date" id="fromDate" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="toDate">Đến ngày</label>
                <input type="date" id="toDate" class="form-control">
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <div>
                    <button id="filterBtn" class="btn btn-primary">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    <button id="resetBtn" class="btn btn-secondary ml-1">
                        <i class="fas fa-refresh"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng hoa hồng</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCommission">{{ number_format($stats['total_commission'] ?? 0, 0, ',', '.') }}đ</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đã thanh toán</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="paidCommission">{{ number_format($stats['paid_commission'] ?? 0, 0, ',', '.') }}đ</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Chờ thanh toán</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="pendingCommission">{{ number_format($stats['pending_commission'] ?? 0, 0, ',', '.') }}đ</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng đơn hàng</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalOrders">{{ $stats['total_orders'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent Commission Summary Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="commissions-summary-table" width="100%" cellspacing="0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Đại lý</th>
                        <th width="15%">Tổng đơn hàng</th>
                        <th width="15%">Tổng hoa hồng</th>
                        <th width="15%">Đã thanh toán</th>
                        <th width="15%">Chờ thanh toán</th>
                        <th width="15%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissionSummary as $summary)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        
                        <!-- Đại lý -->
                        <td>
                            <div class="d-flex align-items-center">
                                @if($summary['agent']->photo)
                                    <img src="{{ asset('storage/' . $summary['agent']->photo) }}" class="rounded-circle mr-2" width="40" height="40">
                                @else
                                    <div class="bg-primary rounded-circle mr-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-weight-bold">{{ $summary['agent']->name }}</div>
                                    @if($summary['agent']->company)
                                        <small class="text-muted">{{ $summary['agent']->company }}</small>
                                    @endif
                                    <br><small class="text-info">{{ $summary['agent']->commission_rate }}% hoa hồng</small>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Tổng đơn hàng -->
                        <td>
                            <div class="font-weight-bold text-primary">{{ $summary['total_orders'] }}</div>
                            <small class="text-muted">đơn hàng</small>
                        </td>
                        
                        <!-- Tổng hoa hồng -->
                        <td>
                            <div class="font-weight-bold text-success">{{ number_format($summary['total_commission'], 0, ',', '.') }}đ</div>
                        </td>
                        
                        <!-- Đã thanh toán -->
                        <td>
                            <div class="font-weight-bold text-success">{{ number_format($summary['paid_commission'], 0, ',', '.') }}đ</div>
                            @if($summary['total_commission'] > 0)
                                <small class="text-muted">({{ number_format(($summary['paid_commission'] / $summary['total_commission']) * 100, 1) }}%)</small>
                            @endif
                        </td>
                        
                        <!-- Chờ thanh toán -->
                        <td>
                            <div class="font-weight-bold text-warning">{{ number_format($summary['pending_commission'], 0, ',', '.') }}đ</div>
                            @if($summary['pending_commission'] > 0)
                                <button class="btn btn-sm btn-success mt-1" onclick="payAgentCommission({{ $summary['agent']->id }}, {{ $summary['pending_commission'] }})">
                                    <i class="fas fa-money-bill"></i> Thanh toán
                                </button>
                            @endif
                        </td>
                        
                        <!-- Hành động -->
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('agent.commission.detail', $summary['agent']->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                                <a href="{{ route('agent.show', $summary['agent']->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-user"></i> Hồ sơ
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <div class="text-muted">Chưa có dữ liệu hoa hồng</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xuất báo cáo hoa hồng</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="form-group">
                        <label>Đại lý</label>
                        <select name="agent_id" class="form-control">
                            <option value="">Tất cả đại lý</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Từ ngày</label>
                        <input type="date" name="from_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Đến ngày</label>
                        <input type="date" name="to_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Định dạng</label>
                        <select name="format" class="form-control">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="exportReport()">
                    <i class="fas fa-download"></i> Xuất báo cáo
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<style>
    div.dataTables_wrapper div.dataTables_paginate {
        display: block !important;
    }
    .card.border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .card.border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .card.border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    .card.border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .text-xs {
        font-size: 0.7rem;
    }
</style>
@endpush

@push('scripts')
<!-- Page level plugins -->
<script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- Page level custom scripts -->
<script src="{{asset('backend/js/demo/datatables-demo.js')}}"></script>
<script>
let table = $('#commissions-summary-table').DataTable({
    "paging": true,
    "pageLength": 10,
    "lengthMenu": [10, 25, 50, 100],
    "ordering": true,
    "searching": true,
    "info": true,
    "responsive": true,
    "language": {
        "lengthMenu": "Hiển thị _MENU_ mục",
        "zeroRecords": "Không tìm thấy dữ liệu",
        "info": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
        "infoEmpty": "Hiển thị 0 đến 0 của 0 mục",
        "infoFiltered": "(lọc từ _MAX_ tổng số mục)",
        "search": "Tìm kiếm:",
        "paginate": {
            "first": "Đầu",
            "last": "Cuối", 
            "next": "Tiếp",
            "previous": "Trước"
        }
    },
    "columnDefs": [
        {
            "orderable": false,
            "targets": [6] // Cột hành động không sort được
        }
    ],
    "order": [[3, 'desc']] // Sort theo tổng hoa hồng
});

// Filter functionality
$('#filterBtn').on('click', function() {
    applyFilters();
});

$('#resetBtn').on('click', function() {
    $('#agentFilter').val('');
    $('#statusFilter').val('');
    $('#fromDate').val('');
    $('#toDate').val('');
    loadCommissionData();
});

function applyFilters() {
    const filters = {
        agent_id: $('#agentFilter').val(),
        status: $('#statusFilter').val(),
        from_date: $('#fromDate').val(),
        to_date: $('#toDate').val()
    };
    
    loadCommissionData(filters);
}

function loadCommissionData(filters = {}) {
    // Show loading state
    $('#commissions-summary-table tbody').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
    
    $.ajax({
        url: "{{ route('agent.commissions.index') }}",
        method: 'GET',
        data: filters,
        success: function(response) {
            // Update statistics
            updateStatistics(response.stats);
            
            // Update table
            updateTable(response.commissionSummary);
        },
        error: function() {
            swal("Lỗi!", "Có lỗi xảy ra khi tải dữ liệu", "error");
        }
    });
}

function updateStatistics(stats) {
    $('#totalCommission').text(new Intl.NumberFormat('vi-VN').format(stats.total_commission) + 'đ');
    $('#paidCommission').text(new Intl.NumberFormat('vi-VN').format(stats.paid_commission) + 'đ');
    $('#pendingCommission').text(new Intl.NumberFormat('vi-VN').format(stats.pending_commission) + 'đ');
    $('#totalOrders').text(stats.total_orders);
}

function updateTable(data) {
    table.clear();
    
    if (data && data.length > 0) {
        data.forEach((item, index) => {
            const photoHtml = item.agent.photo 
                ? `<img src="/storage/${item.agent.photo}" class="rounded-circle mr-2" width="40" height="40">`
                : `<div class="bg-primary rounded-circle mr-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-user text-white"></i></div>`;
            
            const agentInfo = `
                <div class="d-flex align-items-center">
                    ${photoHtml}
                    <div>
                        <div class="font-weight-bold">${item.agent.name}</div>
                        ${item.agent.company ? `<small class="text-muted">${item.agent.company}</small>` : ''}
                        <br><small class="text-info">${item.agent.commission_rate}% hoa hồng</small>
                    </div>
                </div>
            `;
            
            const payButton = item.pending_commission > 0 
                ? `<button class="btn btn-sm btn-success mt-1" onclick="payAgentCommission(${item.agent.id}, ${item.pending_commission})"><i class="fas fa-money-bill"></i> Thanh toán</button>`
                : '';
            
            const paidPercentage = item.total_commission > 0 
                ? `<small class="text-muted">(${((item.paid_commission / item.total_commission) * 100).toFixed(1)}%)</small>`
                : '';
            
            table.row.add([
                index + 1,
                agentInfo,
                `<div class="font-weight-bold text-primary">${item.total_orders}</div><small class="text-muted">đơn hàng</small>`,
                `<div class="font-weight-bold text-success">${new Intl.NumberFormat('vi-VN').format(item.total_commission)}đ</div>`,
                `<div class="font-weight-bold text-success">${new Intl.NumberFormat('vi-VN').format(item.paid_commission)}đ</div>${paidPercentage}`,
                `<div class="font-weight-bold text-warning">${new Intl.NumberFormat('vi-VN').format(item.pending_commission)}đ</div>${payButton}`,
                `<div class="btn-group">
                    <a href="/admin/agent-commissions/${item.agent.id}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Chi tiết</a>
                    <a href="/admin/agent/${item.agent.id}" class="btn btn-sm btn-outline-primary"><i class="fas fa-user"></i> Hồ sơ</a>
                </div>`
            ]);
        });
    } else {
        table.row.add([
            '', '', '', 
            '<div class="text-center py-4"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><div class="text-muted">Chưa có dữ liệu hoa hồng</div></div>',
            '', '', ''
        ]);
    }
    
    table.draw();
}

// Pay agent commission
function payAgentCommission(agentId, amount) {
    swal({
        title: "Xác nhận thanh toán",
        text: `Bạn có chắc chắn muốn thanh toán ${new Intl.NumberFormat('vi-VN').format(amount)}đ cho đại lý này?`,
        icon: "warning",
        buttons: ["Hủy", "Thanh toán"],
        dangerMode: false,
    }).then((willPay) => {
        if (willPay) {
            $.ajax({
                url: "{{ route('agent.orders.bulk_pay') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    agent_id: agentId
                },
                success: function(response) {
                    if (response.success) {
                        swal("Thành công!", response.message, "success")
                        .then(() => location.reload());
                    }
                },
                error: function() {
                    swal("Lỗi!", "Có lỗi xảy ra khi thanh toán", "error");
                }
            });
        }
    });
}

// Export report
function exportReport() {
    const formData = new FormData(document.getElementById('exportForm'));
    
    // Create and submit form
    const form = $('<form>', {
        method: 'POST',
        action: "{{ route('agent.orders.export') }}"
    });
    
    form.append($('<input>', {
        type: 'hidden',
        name: '_token',
        value: "{{ csrf_token() }}"
    }));
    
    // Add form data
    for (let [key, value] of formData.entries()) {
        form.append($('<input>', {
            type: 'hidden',
            name: key,
            value: value
        }));
    }
    
    $('body').append(form);
    form.submit();
    form.remove();
    
    $('#exportModal').modal('hide');
    
    swal({
        title: "Đang xuất báo cáo...",
        text: "Vui lòng chờ trong giây lát",
        icon: "info",
        buttons: false,
        timer: 2000
    });
}

// Auto-refresh every 5 minutes
setInterval(function() {
    applyFilters();
}, 300000);
</script>
@endpush