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
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách quy tắc tính phí vận chuyển</h6>
      <a href="{{route('shipping.location.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Thêm quy tắc"><i class="fas fa-plus"></i> Thêm quy tắc vận chuyển</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($locations)>0)
        <table class="table table-bordered table-hover" id="location-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>#</th>
              <th>Phương thức</th>
              <th>Từ tỉnh/thành phố</th>
              <th>Đến tỉnh/thành phố</th>
              <th>Giá cơ bản</th>
              <th>Giá theo kg</th>
              <th>Hoạt động</th>
            </tr>
          </thead>
          <tbody>
            @foreach($locations as $location)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{$location->shipping->type}}</td>
                    <td>{{$location->fromProvince->name}}</td>
                    <td>{{$location->toProvince->name}}</td>
                    <td>{{number_format($location->price, 0, ',', '.')}}đ</td>
                    <td>{{number_format($location->weight_price, 0, ',', '.')}}đ</td>
                    <td>
                        <a href="{{route('shipping.location.edit',$location->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Sửa" data-placement="bottom"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{route('shipping.location.destroy',[$location->id])}}">
                          @csrf
                          @method('delete')
                              <button class="btn btn-danger btn-sm dltBtn" data-id={{$location->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{$locations->links()}}</span>
        @else
          <h6 class="text-center">Không tìm thấy quy tắc vận chuyển nào! Vui lòng thêm quy tắc mới.</h6>
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
      $('#location-dataTable').DataTable( {
            "paging": false,
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[6]
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
