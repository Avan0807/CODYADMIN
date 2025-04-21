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
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách tỉnh/thành phố</h6>
      <a href="{{route('shipping.province.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Thêm tỉnh/thành phố"><i class="fas fa-plus"></i> Thêm tỉnh/thành phố</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($provinces)>0)
        <table class="table table-bordered table-hover" id="province-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>#</th>
              <th>Tên tỉnh/thành phố</th>
              <th>Vùng</th>
              <th>Trạng thái</th>
              <th>Hoạt động</th>
            </tr>
          </thead>
          <tbody>
            @foreach($provinces as $province)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{$province->name}}</td>
                    <td>
                        @if($province->region_id == 1)
                            <span class="badge badge-primary">Miền Bắc</span>
                        @elseif($province->region_id == 2)
                            <span class="badge badge-success">Miền Trung</span>
                        @else
                            <span class="badge badge-warning">Miền Nam</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-success">Hoạt động</span>
                    </td>
                    <td>
                        <a href="{{route('shipping.province.edit',$province->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Sửa" data-placement="bottom"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{route('shipping.province.destroy',[$province->id])}}">
                          @csrf
                          @method('delete')
                              <button class="btn btn-danger btn-sm dltBtn" data-id={{$province->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{$provinces->links()}}</span>
        @else
          <h6 class="text-center">Không tìm thấy tỉnh/thành phố nào! Vui lòng thêm tỉnh/thành phố.</h6>
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
          display: none;
      }
  </style>
@endpush

@push('scripts')
  <script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <script>
      $('#province-dataTable').DataTable( {
            "paging": false,
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[3,4]
                }
            ]
        } );

      $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
          $('.dltBtn').click(function(e){
            var form=$(this).closest('form');
              var dataID=$(this).data('id');
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
          })
      })
  </script>
@endpush
