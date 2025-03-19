@extends('backend.layouts.master')
@section('title','Ecommerce Laravel || Trang quản lý thương hiệu')
@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách thương hiệu</h6>
      <a href="{{route('brand.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Thêm thương hiệu"><i class="fas fa-plus"></i> Thêm thương hiệu</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($brands)>0)
        <table class="table table-bordered table-hover" id="banner-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>#</th>
              <th>Tiêu đề</th>
              <th>Slug</th>
              <th>Trạng thái</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            @foreach($brands as $brand)   
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{$brand->title}}</td>
                    <td>{{$brand->slug}}</td>
                    <td>
                        @if($brand->status=='active')
                            <span class="badge badge-success">Hoạt động</span>
                        @else
                            <span class="badge badge-warning">Không hoạt động</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{route('brand.edit', $brand->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px; border-radius:50%" data-toggle="tooltip" title="Chỉnh sửa" data-placement="bottom"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{route('brand.destroy', [$brand->id])}}">
                          @csrf 
                          @method('delete')
                              <button class="btn btn-danger btn-sm dltBtn" data-id={{$brand->id}} style="height:30px; width:30px; border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>  
            @endforeach
          </tbody>
        </table>
        @else
          <h6 class="text-center">Không tìm thấy thương hiệu nào! Vui lòng thêm thương hiệu mới.</h6>
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
      
      .zoom {
        transition: transform .2s;
      }

      .zoom:hover {
        transform: scale(3.2);
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
      $('#banner-dataTable').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100], // Cho phép chọn mục hiển thị
            "ordering": true,
            "searching": true,
            "columnDefs": [
                {
                    "orderable": false,
                    "targets": [4]
                }
            ]
      });

      $(document).ready(function () {
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          $('.dltBtn').click(function (e) {
              var form = $(this).closest('form');
              var dataID = $(this).data('id');
              e.preventDefault();
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
