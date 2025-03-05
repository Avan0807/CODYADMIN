@extends('backend.layouts.master')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách đơn hàng Affiliate</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($affiliateOrders) > 0)
        <table class="table table-bordered table-hover" id="affiliateOrders-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>#</th>
              <th>Bác sĩ</th>
              <th>Mã đơn hàng</th>
              <th>Hoa hồng</th>
              <th>Trạng thái</th>
              <th>Chức năng</th>
            </tr>
          </thead>
          <tbody>
            @foreach($affiliateOrders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->doctor->name ?? 'Không có bác sĩ' }}</td>
                    <td>{{ $order->order_code }}</td>
                    <td>{{ number_format($order->commission, 0, ',', '.') }} VNĐ</td>
                    <td>
                        @if($order->status == 'Đã giao')
                            <span class="badge badge-success">Đã giao</span>
                        @else
                            <span class="badge badge-primary">Mới</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('affiliate.show', $order->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form method="POST" action="{{ route('affiliate.destroy', $order->id) }}" style="display:inline;">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-danger btn-sm dltBtn" data-id={{$order->id}} data-toggle="tooltip" title="Xóa">
                              <i class="fas fa-trash-alt"></i>
                          </button>
                        </form>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
        <div class="d-flex justify-content-end mt-3">
            {{ $affiliateOrders->links('pagination::bootstrap-4') }}
        </div>
        @else
          <h6 class="text-center">Không tìm thấy đơn hàng nào! Vui lòng kiểm tra lại dữ liệu.</h6>
        @endif
      </div>
    </div>
</div>
@endsection

@push('styles')
  <link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
@endpush

@push('scripts')

  <!-- Page level plugins -->
  <script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <!-- Custom scripts -->
  <script>
      $(document).ready(function(){
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });

          $('#affiliateOrders-dataTable').DataTable({
              "ordering": true,
              "searching": true,
              "paging": false,  // Tắt phân trang của DataTables để giữ Laravel Pagination
              "info": false,
              "lengthChange": false,
              "columnDefs": [{ "orderable": false, "targets": [5] }],
              "language": {
                  "sProcessing": "Đang xử lý...",
                  "sZeroRecords": "Không tìm thấy dữ liệu phù hợp",
                  "sSearch": "Tìm kiếm:",
                  "oPaginate": {
                      "sFirst": "Đầu",
                      "sPrevious": "Trước",
                      "sNext": "Tiếp",
                      "sLast": "Cuối"
                  }
              }
          });

          $('.dltBtn').click(function(e){
              var form = $(this).closest('form');
              e.preventDefault();
              swal({
                  title: "Bạn có chắc không?",
                  text: "Sau khi xóa, bạn sẽ không thể khôi phục đơn hàng này!",
                  icon: "warning",
                  buttons: true,
                  dangerMode: true,
              }).then((willDelete) => {
                  if (willDelete) {
                      form.submit();
                  } else {
                      swal("Đơn hàng của bạn vẫn an toàn!");
                  }
              });
          });
      });
  </script>
@endpush
