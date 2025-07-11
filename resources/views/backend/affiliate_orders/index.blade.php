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
        <h6 class="m-0 font-weight-bold text-primary">Danh sách hoa hồng Affiliate</h6>
        <div>
            <span class="badge badge-info">
                Tổng: {{ count($affiliateOrders) }} đơn
            </span>
            <span class="badge badge-success">
                Tổng hoa hồng: {{ number_format($affiliateOrders->sum('commission'), 0, ',', '.') }}đ
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            @if(count($affiliateOrders) > 0)
            <table class="table table-bordered table-hover" id="affiliate-orders-dataTable" width="100%" cellspacing="0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">Bác sĩ</th>
                        <th width="20%">Đơn hàng</th>
                        <th width="15%">Sản phẩm</th>
                        <th width="15%">Hoa hồng</th>
                        <th width="10%">Trạng thái</th>
                        <th width="10%">Ngày tạo</th>
                        <th width="10%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($affiliateOrders as $affiliateOrder)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        
                        <!-- Bác sĩ -->
                        <td>
                            <div class="font-weight-bold">{{ $affiliateOrder->doctor->name ?? 'N/A' }}</div>
                            <small class="text-muted">ID: #{{ $affiliateOrder->doctor_id }}</small>
                        </td>
                        
                        <!-- Đơn hàng -->
                        <td>
                            <div class="font-weight-bold text-primary">{{ $affiliateOrder->order->order_number ?? 'N/A' }}</div>
                            <small class="text-muted">
                                Khách: {{ $affiliateOrder->order->first_name ?? '' }} {{ $affiliateOrder->order->last_name ?? '' }}
                            </small>
                        </td>
                        
                        <!-- Sản phẩm -->
                        <td>
                            <div>{{ $affiliateOrder->product->title ?? 'Sản phẩm đã xóa' }}</div>
                            @if($affiliateOrder->commission_percentage)
                                <small class="text-info">{{ $affiliateOrder->commission_percentage }}% hoa hồng</small>
                            @endif
                        </td>
                        
                        <!-- Hoa hồng -->
                        <td>
                            <div class="font-weight-bold text-success">{{ number_format($affiliateOrder->commission, 0, ',', '.') }}đ</div>
                        </td>
                        
                        <!-- Trạng thái -->
                        <td>
                            @php
                                $statusConfig = [
                                    'pending' => ['class' => 'warning', 'text' => 'Chờ thanh toán'],
                                    'paid' => ['class' => 'success', 'text' => 'Đã thanh toán'],
                                    'cancelled' => ['class' => 'danger', 'text' => 'Đã hủy'],
                                ];
                                $config = $statusConfig[$affiliateOrder->status] ?? ['class' => 'secondary', 'text' => 'Không xác định'];
                            @endphp
                            <span class="badge badge-{{ $config['class'] }} badge-pill">{{ $config['text'] }}</span>
                        </td>
                        
                        <!-- Ngày tạo -->
                        <td>
                            <div>{{ $affiliateOrder->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $affiliateOrder->created_at->format('H:i') }}</small>
                        </td>
                        
                        <!-- Hành động -->
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu">
                                    @if($affiliateOrder->status == 'pending')
                                    <form method="POST" action="{{ route('admin.affiliate.orders.update', $affiliateOrder->id) }}">
                                        @csrf
                                        <button type="submit" name="status" value="paid" class="dropdown-item text-success">
                                            <i class="fas fa-check"></i> Đánh dấu đã trả
                                        </button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('admin.affiliate.orders.update', $affiliateOrder->id) }}">
                                        @csrf
                                        <button type="submit" name="status" value="cancelled" class="dropdown-item text-danger">
                                            <i class="fas fa-times"></i> Hủy hoa hồng
                                        </button>
                                    </form>
                                    @elseif($affiliateOrder->status == 'paid')
                                    <span class="dropdown-item-text text-success">
                                        <i class="fas fa-check-circle"></i> Đã thanh toán
                                        @if($affiliateOrder->paid_at)
                                            <br><small>{{ $affiliateOrder->paid_at->format('d/m/Y H:i') }}</small>
                                        @endif
                                    </span>
                                    @else
                                    <span class="dropdown-item-text text-muted">
                                        <i class="fas fa-ban"></i> Đã hủy
                                    </span>
                                    @endif
                                    
                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route('order.show', $affiliateOrder->order_id) }}" class="dropdown-item">
                                        <i class="fas fa-eye"></i> Xem đơn hàng
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="4" class="text-right font-weight-bold">Tổng hoa hồng:</td>
                        <td class="font-weight-bold text-success">
                            {{ number_format($affiliateOrders->sum('commission'), 0, ',', '.') }}đ
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
            @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">Chưa có đơn hàng affiliate nào!</h6>
                <p class="text-muted">Hoa hồng sẽ được tạo tự động khi đơn hàng chuyển sang trạng thái "Đã giao".</p>
            </div>
            @endif
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
    .dropdown-item.active {
        background-color: #4e73df;
        color: white;
    }
    .badge-pill {
        border-radius: 50rem;
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
$('#affiliate-orders-dataTable').DataTable({
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
            "targets": [7] // Cột hành động không sort được
        }
    ],
    "order": [[6, 'desc']] // Sort theo ngày tạo mới nhất
});

// Confirm trước khi update status
$('form[action*="affiliate.orders.update"]').on('submit', function(e) {
    e.preventDefault();
    
    let form = $(this);
    let status = form.find('button[type="submit"]:focus').val();
    let statusText = form.find('button[type="submit"]:focus').text().trim();
    
    swal({
        title: "Xác nhận",
        text: `Bạn có chắc chắn muốn ${statusText.toLowerCase()}?`,
        icon: "warning",
        buttons: ["Hủy", "Xác nhận"],
        dangerMode: status === 'cancelled',
    })
    .then((willUpdate) => {
        if (willUpdate) {
            form.off('submit').submit();
        }
    });
});
</script>
@endpush