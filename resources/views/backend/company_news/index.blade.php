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
                        <img src="{{ $item->image ? asset('storage/'.$item->image) : asset('backend/img/no-image.png') }}"
                             class="img-fluid"
                             style="width:80px; height:auto;">
                    </td>
                    <td>{{ \Carbon\Carbon::parse($item->published_at)->translatedFormat('d-m-Y') }}</td>
                    <td>
                        <a href="{{ route('company_news.edit', $item->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('company_news.destroy', $item->id) }}" class="d-inline delete-form">
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
@endpush

@push('scripts')
  <script src="{{ asset('backend/vendor/datatables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <script>
      $(document).ready(function(){
          $('#news-dataTable').DataTable({
              "ordering": true,
              "searching": true,
              "paging": false,
              "lengthMenu": [10, 25, 50, 100],
              "columnDefs": [ { "orderable": false, "targets": [4] } ],
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
