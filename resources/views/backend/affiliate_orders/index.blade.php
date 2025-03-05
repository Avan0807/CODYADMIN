@extends('backend.layouts.master')

@section('title','Quản lý Đơn Hàng Affiliate')

@section('main-content')
<div class="container-fluid">  <!-- Đổi từ container sang container-fluid -->
    <div class="card">
        <div class="row">
            <div class="col-md-12">
                @include('backend.layouts.notification')
            </div>
        </div>
        <h5 class="card-header">Danh sách đơn hàng Affiliate</h5>
        <div class="card-body">
            @if($affiliateOrders->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover nowrap" id="affiliate-orders-dataTable" style="width:100%">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Bác sĩ</th>
                            <th>Mã đơn hàng</th>
                            <th>Hoa hồng</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($affiliateOrders as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $order->doctor->name }}</td>
                            <td>{{ $order->order->order_number }}</td>
                            <td>{{ number_format($order->commission, 0, ',', '.') }} VNĐ</td>
                            <td>
                                @php
                                    $statusClass = [
                                        'new' => 'primary',
                                        'process' => 'warning',
                                        'delivered' => 'success',
                                        'cancel' => 'danger'
                                    ];

                                    $statusText = [
                                        'new' => 'Mới',
                                        'process' => 'Đang xử lý',
                                        'delivered' => 'Đã giao',
                                        'cancel' => 'Đã hủy'
                                    ];
                                @endphp
                                <span class="badge badge-{{ $statusClass[$order->status] ?? 'secondary' }}">
                                    {{ $statusText[$order->status] ?? 'Không xác định' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="float-right">
                    {{ $affiliateOrders->links() }}
                </div>
            </div>
            @else
                <h2 class="text-center">Không có đơn hàng nào!</h2>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<style>
    /* Đảm bảo bảng mở rộng full chiều ngang */
    .table-responsive {
        overflow-x: auto;
    }
    .table {
        width: 100% !important;
    }
    .badge {
        font-size: 14px;
        padding: 6px 10px;
        border-radius: 5px;
    }
    .btn-sm {
        width: 32px;
        height: 32px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('backend/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
    $(document).ready(function(){
        $('#affiliate-orders-dataTable').DataTable({
            "paging": false,
            "searching": true,
            "info": false,
            "ordering": true,
            "scrollX": true, // Kích hoạt cuộn ngang
            "autoWidth": false, // Ngăn DataTables tự điều chỉnh width sai
            "language": {
                "search": "Tìm kiếm:",
                "zeroRecords": "Không tìm thấy dữ liệu phù hợp",
                "paginate": {
                    "next": "Trang sau",
                    "previous": "Trang trước"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [4] },
                { "className": "text-center", "targets": [4] }
            ]
        });

        // Chỉnh badge trạng thái nhỏ hơn
        $('.badge').css({
            'font-size': '12px',
            'padding': '5px 8px',
            'border-radius': '4px'
        });
    });
</script>
@endpush
