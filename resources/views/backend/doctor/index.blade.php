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
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách bác sĩ</h6>
      <a href="{{route('doctor.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Thêm bác sĩ"><i class="fas fa-plus"></i> Thêm bác sĩ</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($doctors)>0)
        <table class="table table-bordered table-hover" id="doctor-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>#</th>
              <th>Tên</th>
              <th>Chuyên môn</th>
              <th>Kinh nghiệm</th>
              <th>Giờ làm việc</th>
              <th>Địa điểm</th>
              <th>Email</th>
              <th>Điện thoại</th>
              <th>Ảnh</th>
              <th>Trạng thái</th>
              <th>Chức năng</th>
            </tr>
          </thead>
          <tbody>
            @foreach($doctors as $doctor)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{$doctor->name}}</td>
                    <td>{{$doctor->specialization}}</td>
                    <td>{{$doctor->experience}} năm</td>
                    <td>{{$doctor->working_hours}}</td>
                    <td>{{$doctor->location}}</td>
                    <td>{{$doctor->email}}</td>
                    <td>{{$doctor->phone}}</td>
                    <td>
                        @if($doctor->photo)
                            <img src="{{$doctor->photo}}" class="img-fluid zoom" style="max-width:80px" alt="Ảnh bác sĩ">
                        @else
                            <img src="{{asset('backend/img/thumbnail-default.jpg')}}" class="img-fluid" style="max-width:80px" alt="avatar.png">
                        @endif
                    </td>
                    <td>
                        @if($doctor->status == 'active')
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Hoạt động</span>
                        @else
                            <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Không hoạt động</span>
                        @endif
                    </td>

                    <td>
                        <a href="{{route('doctor.edit',$doctor->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Chỉnh sửa" data-placement="bottom"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{route('doctor.destroy',[$doctor->id])}}">
                          @csrf
                          @method('delete')
                          <button class="btn btn-danger btn-sm dltBtn" data-id={{$doctor->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
        @else
          <h6 class="text-center">Không tìm thấy bác sĩ nào!!! Vui lòng thêm bác sĩ</h6>
        @endif
      </div>
    </div>
</div>
@endsection

@push('styles')
  <link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
  <style>

      div.dataTables_wrapper div.dataTables_paginate {
          display: block !important;
      } 
      .zoom {
        transition: transform .2s;
      }
      .zoom:hover {
        transform: scale(2);
      }
  </style>
@endpush

@push('scripts')
  <!-- DataTables scripts -->
  <script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>

  <script>
      $(document).ready(function(){
          $('#doctor-dataTable').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100], // Cho phép chọn mục hiển thị
            "ordering": true,
            "searching": true,
              "language": {
                  "decimal": "",
                  "emptyTable": "Không có dữ liệu",
                  "info": "Hiển thị _START_ đến _END_ của _TOTAL_ bác sĩ",
                  "infoEmpty": "Hiển thị 0 đến 0 của 0 bác sĩ",
                  "infoFiltered": "(lọc từ _MAX_ tổng số bác sĩ)",
                  "lengthMenu": "Hiển thị _MENU_ bác sĩ mỗi trang",
                  "loadingRecords": "Đang tải...",
                  "processing": "Đang xử lý...",
                  "search": "Tìm kiếm:",
                  "zeroRecords": "Không tìm thấy kết quả nào",
                  "paginate": {
                      "first": "Đầu",
                      "last": "Cuối",
                      "next": "Tiếp",
                      "previous": "Trước"
                  },
                  "aria": {
                      "sortAscending": ": sắp xếp tăng dần",
                      "sortDescending": ": sắp xếp giảm dần"
                  }
              },
              "columnDefs": [
                  {
                      "orderable": false,
                      "targets": [8,10]
                  }
              ]
          });

          // Xác nhận xóa bằng SweetAlert
          $('.dltBtn').click(function(e){
              var form=$(this).closest('form');
              e.preventDefault();
              swal({
                  title: "Bạn có chắc không?",
                  text: "Sau khi xóa, bạn sẽ không thể khôi phục dữ liệu này!",
                  icon: "warning",
                  buttons: true,
                  dangerMode: true,
              }).then((willDelete) => {
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

