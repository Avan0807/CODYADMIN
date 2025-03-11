@extends('backend.layouts.master')

@section('main-content')
 <!-- Danh sách đơn hàng tiếp thị liên kết -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách đơn hàng tiếp thị liên kết</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if($affiliateLinks->count() > 0)
        <table class="table table-bordered table-hover" id="affiliateLinks-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
                <th>#</th>
                <th>Bác sĩ</th>
                <th>Sản phẩm</th>
                <th>Hoa hồng</th>
            </tr>
          </thead>
          <tbody>
            @foreach($affiliateLinks as $affiliate)
                <tr>
                    <td>{{ $affiliate->id }}</td>

                    <!-- Hiển thị bác sĩ -->
                    <td>{{ optional($affiliate->doctor)->name ?? 'Chưa có bác sĩ' }}</td>

                    <!-- Hiển thị sản phẩm -->
                    <td>
                        @if($affiliate->product)
                            <a href="{{ $affiliate->product_link }}" target="_blank">
                                {{ $affiliate->product->title }}
                            </a>
                        @else
                            <span class="text-danger">Chưa có sản phẩm</span>
                        @endif
                    </td>
                    <td>
                        <span>{{ number_format($affiliate->commission_percentage, 2) }}%</span>
                    </td>

                </tr>
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{ $affiliateLinks->links() }}</span>
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

          // Cấu hình DataTables với ngôn ngữ Tiếng Việt
          $('#affiliateLinks-dataTable').DataTable({
              "ordering": true,
              "searching": true,
              "paging": false,
              "lengthMenu": [10, 25, 50, 100],
              "columnDefs": [ { "orderable": false, "targets": [3] } ],
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
      });
  </script>



@endpush
