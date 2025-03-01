@extends('backend.layouts.master')

@section('title','CODY || Tất cả thông báo chiến dịch')

@section('main-content')

<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
           @include('backend.layouts.notification')
        </div>
    </div>

    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách Thông Báo Chiến Dịch</h6>
      <a href="{{ route('campaign_notifications.create') }}" class="btn btn-primary btn-sm float-right">
          <i class="fas fa-plus"></i> Thêm Thông Báo
      </a>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        @if($campaign_notifications->count() > 0)
        <table class="table table-bordered table-hover" id="notification-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>#</th>
              <th>Tiêu đề</th>
              <th>Đối tượng</th>
              <th>Ngày tạo</th>
              <th>Hoạt động</th>
            </tr>
          </thead>
          <tbody>
            @foreach($campaign_notifications as $notification)   
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $notification->title }}</td>
                    <td>{{ ucfirst($notification->target_audience) }}</td>
                    <td>{{ $notification->created_at->format('F d, Y h:i A') }}</td>
                    <td>
                        <a href="{{ route('campaign_notifications.show', $notification->id) }}" 
                           class="btn btn-primary btn-sm float-left mr-1" 
                           style="height:30px; width:30px; border-radius:50%" 
                           data-toggle="tooltip" title="Xem">
                            <i class="fas fa-eye"></i>
                        </a>

                        <a href="{{ route('campaign_notifications.edit', $notification->id) }}" 
                           class="btn btn-warning btn-sm float-left mr-1" 
                           style="height:30px; width:30px; border-radius:50%" 
                           data-toggle="tooltip" title="Chỉnh sửa">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form method="POST" action="{{ route('campaign_notifications.destroy', $notification->id) }}" class="delete-form">
                          @csrf 
                          @method('delete')
                          <button type="submit" 
                                  class="btn btn-danger btn-sm dltBtn" 
                                  style="height:30px; width:30px; border-radius:50%" 
                                  data-toggle="tooltip" title="Xóa">
                              <i class="fas fa-trash-alt"></i>
                          </button>
                        </form>
                    </td>
                </tr>  
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{ $campaign_notifications->links() }}</span>
        @else
          <h6 class="text-center">Không có thông báo chiến dịch nào! Vui lòng tạo mới.</h6>
        @endif
      </div>
    </div>
</div>
@endsection

@push('styles')
  <link href="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
@endpush

@push('scripts')
  <script src="{{ asset('backend/vendor/datatables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <script>
      $(document).ready(function(){
          $('#notification-dataTable').DataTable({
              "columnDefs": [
                  {
                      "orderable": false,
                      "targets": [4]
                  }
              ]
          });

          $('.dltBtn').click(function(e){
              e.preventDefault();
              var form = $(this).closest('form');

              swal({
                  title: "Bạn có chắc không?",
                  text: "Sau khi xóa, bạn sẽ không thể khôi phục dữ liệu này!",
                  icon: "warning",
                  buttons: true,
                  dangerMode: true,
              })
              .then((willDelete) => {
                  if (willDelete) {
                      form.submit();
                  } else {
                      swal("Dữ liệu của bạn vẫn an toàn!");
                  }
              });
          });
      });
  </script>
@endpush
