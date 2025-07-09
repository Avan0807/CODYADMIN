@extends('backend.layouts.master')

@section('main-content')
<div class="card shadow mb-4">
  <div class="row">
    <div class="col-md-12">
      @include('backend.layouts.notification')
    </div>
  </div>
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách số điện thoại tư vấn</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      @if(count($supports) > 0)
        <table class="table table-bordered table-hover" id="phone-support-table" width="100%" cellspacing="0">
          <thead class="bg-primary text-white">
            <tr>
              <th>#</th>
              <th>Số điện thoại</th>
              <th>Loại hỗ trợ</th>
              <th>Thời gian gửi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($supports as $index => $support)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $support->phone_number }}</td>
                <td>{{ $support->support_type }}</td>
                <td>{{ \Carbon\Carbon::parse($support->created_at)->format('H:i d/m/Y H:i') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <h6 class="text-center">Chưa có ai đăng ký tư vấn!</h6>
      @endif
    </div>
  </div>
</div>
@endsection

@push('styles')
  <link href="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
  <style>
    div.dataTables_wrapper div.dataTables_paginate {
        display: block !important;
    }
  </style>
@endpush

@push('scripts')
  <script src="{{ asset('backend/vendor/datatables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('backend/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
  <script>
    $('#phone-support-table').DataTable({
      "paging": true,
      "pageLength": 10,
      "lengthMenu": [10, 25, 50, 100],
      "ordering": true,
      "searching": true,
      "info": true,
      "responsive": true,
      "language": {
        "lengthMenu": "Hiển thị _MENU_ mục",
        "zeroRecords": "Không tìm thấy dữ liệu",
        "info": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
        "infoEmpty": "Hiển thị 0 đến 0 của 0 mục",
        "infoFiltered": "(lọc từ _MAX_ tổng số mục)",
        "search": "Tìm kiếm:",
        "paginate": {
          "first": "Đầu",
          "last": "Cuối", 
          "next": "Tiếp",
          "previous": "Trước"
        }
      },
      "order": [[3, 'desc']] // Sắp xếp theo thời gian gửi mới nhất
    });
  </script>
@endpush
