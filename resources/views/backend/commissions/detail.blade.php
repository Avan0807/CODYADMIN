@extends('backend.layouts.master')

@section('main-content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng liên kết</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            @if(count($orders) > 0)
            <table class="table table-bordered table-hover" id="order-detail-dataTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên Người Dùng</th>
                    <th>Số Điện Thoại </th>
                    <th>Hoa Hồng</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($orders as $key => $order)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $order->user_name }}</td> <!-- Hiển thị tên người dùng -->
                        <td>{{ $order->phone }}</td>
                        <td>{{ number_format($order->commission, 0, ',', '.') }} đ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <h6 class="text-center">Không có đơn hàng nào!</h6>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
<script>
    $('#order-detail-dataTable').DataTable({
        "ordering": true,
            "searching": true,
            "paging": false,
            "language": {
                  "sProcessing":   "Đang xử lý...",
                  "sLengthMenu":   "Hiển thị _MENU_ dòng",
                  "sZeroRecords":  "Không tìm thấy dữ liệu phù hợp",
                  "sInfo":         "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                  "sInfoEmpty":    "Không có dữ liệu",
                  "sInfoFiltered": "(được lọc từ tổng số _MAX_ mục)",
                  "sSearch":       "Tìm kiếm:",
                  "oPaginate": {
                      "sFirst":    "Đầu",
                      "sPrevious": "Trước",
                      "sNext":     "Tiếp",
                      "sLast":     "Cuối"
                  }
                }
      });
</script>
@endpush
