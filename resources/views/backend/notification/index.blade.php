@extends('backend.layouts.master')

@section('title','CODY || Tất cả thông báo')

@section('main-content')
<div class="card">
    <div class="row">
        <div class="col-md-12">
           @include('backend.layouts.notification')
        </div>
    </div>
  <h5 class="card-header">Thông báo</h5>
  <div class="card-body">
    @if(Auth::check() && Auth::user()->notifications->count() > 0)
    <table class="table table-hover admin-table" id="notification-dataTable">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Thời gian</th>
          <th scope="col">Tiêu đề</th>
          <th scope="col">Hoạt động</th>
        </tr>
      </thead>
      <tbody>
        @foreach (Auth::user()->notifications as $notification)
        <tr class="@if($notification->unread()) bg-light border-left-light @else border-left-success @endif">
          <td scope="row">{{ $loop->iteration }}</td>
          <td>{{ $notification->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('F d, Y h:i A') }}</td>
          <td>{{ $notification->data['title'] ?? 'Không có tiêu đề' }}</td>
          <td>
            <a href="{{ route('admin.notification', $notification->id) }}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Xem" data-placement="bottom">
                <i class="fas fa-eye"></i>
            </a>
            <form method="POST" action="{{ route('notification.delete', $notification->id) }}" class="delete-form">
              @csrf
              @method('delete')
              <button type="submit" class="btn btn-danger btn-sm dltBtn" data-id="{{ $notification->id }}" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Xóa">
                  <i class="fas fa-trash-alt"></i>
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
      <h2>Thông báo trống!</h2>
    @endif
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

  <!-- Page level custom scripts -->
  <script src="{{ asset('backend/js/demo/datatables-demo.js') }}"></script>

  <script>
      $(document).ready(function(){
          $('#notification-dataTable').DataTable({
            "searching": true,
            "paging": false,
              "columnDefs": [
                  {
                      "orderable": false,
                      "targets": [3]
                  }
              ]
          });

          // Sweet alert for delete confirmation
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
                      swal("Dữ liệu của bạn an toàn!");
                  }
              });
          });
      });
  </script>
@endpush
