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
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách hoa hồng bác sĩ</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($commissions)>0)
        <table class="table table-bordered table-hover" id="commission-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
                <th>#</th>
                <th>Tên Bác Sĩ</th>
                <th>Tổng Tiền Hoa Hồng</th>
                <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            @foreach($commissions as $key => $commission)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $commission->doctor_name }}</td>
                    <td>{{ number_format($commission->total_commission, 0, ',', '.') }} đ</td>
                    <td>
                        <a href="{{ route('commission.detail', $commission->doctor_id) }}" 
                        class="btn btn-info btn-sm" 
                        data-toggle="tooltip" 
                        title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
        @else
          <h6 class="text-center">Không có dữ liệu hoa hồng!!!</h6>
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
      $('#commission-dataTable').DataTable({
            "ordering": true,
            "searching": true,
            "paging": false,
            "columnDefs": [
                {
                    "orderable": false,
                    "targets": [3]
                }
            ],
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
