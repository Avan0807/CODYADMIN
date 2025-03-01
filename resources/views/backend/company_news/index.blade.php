@extends('backend.layouts.master')

@section('title','CODY || Danh sách Tin Tức Công Ty')

@section('main-content')

<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
           @include('backend.layouts.notification')
        </div>
    </div>

    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách Tin Tức Công Ty</h6>
      <a href="{{ route('company_news.create') }}" class="btn btn-primary btn-sm float-right">
          <i class="fas fa-plus"></i> Thêm Tin Tức
      </a>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        @if($news->count() > 0)
        <table class="table table-bordered table-hover" id="news-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>#</th>
              <th>Tiêu đề</th>
              <th>Ảnh</th>
              <th>Ngày xuất bản</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            @foreach($news as $item)   
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->title }}</td>
                    <td>
                        @if($item->image)
                            <img src="{{ asset('storage/'.$item->image) }}" class="img-fluid" style="max-width:80px">
                        @else
                            <img src="{{ asset('backend/img/no-image.png') }}" class="img-fluid" style="max-width:80px">
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($item->published_at)->format('d-m-Y') }}</td>
                    <td>
                        <a href="{{ route('company_news.edit', $item->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('company_news.destroy', $item->id) }}" class="d-inline">
                          @csrf 
                          @method('delete')
                          <button type="submit" class="btn btn-danger btn-sm dltBtn">
                              <i class="fas fa-trash-alt"></i>
                          </button>
                        </form>
                    </td>
                </tr>  
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{ $news->links() }}</span>
        @else
          <h6 class="text-center">Không có tin tức nào! Vui lòng thêm mới.</h6>
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