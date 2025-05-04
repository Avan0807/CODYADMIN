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
        <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách đơn hàng Affiliate</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            @if(count($affiliateOrders) > 0)
            <table class="table table-bordered table-hover" id="affiliate-orders-dataTable" style="width:100%">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Bác sĩ</th>
                        <th>Mã đơn hàng</th>
                        <th>Hoa hồng</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($affiliateOrders as $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->doctor->name }}</td>
                        <td>{{ $order->order->order_number }}</td>
                        <td>{{ number_format($order->commission, 0, ',', '.') }} VNĐ</td>
                        <td>
                            @php
                                $statusClass = [
                                    'new' => 'primary',
                                    'process' => 'warning',
                                    'delivered' => 'success',
                                    'cancel' => 'danger'
                                ];

                                $statusText = [
                                    'new' => 'Mới',
                                    'process' => 'Đang xử lý',
                                    'delivered' => 'Đã giao',
                                    'cancel' => 'Đã hủy'
                                ];
                            @endphp
                            <span class="badge badge-{{ $statusClass[$order->status] ?? 'secondary' }}">
                                {{ $statusText[$order->status] ?? 'Không xác định' }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton-{{$order->id}}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Cập nhật trạng thái
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{$order->id}}">
                                    <form method="POST" action="{{ route('admin.affiliate.orders.update', $order->id) }}">
                                        @csrf
                                        <button type="submit" name="status" value="new" class="dropdown-item {{ $order->status == 'new' ? 'active' : '' }}">Mới</button>
                                        <button type="submit" name="status" value="process" class="dropdown-item {{ $order->status == 'process' ? 'active' : '' }}">Đang xử lý</button>
                                        <button type="submit" name="status" value="delivered" class="dropdown-item {{ $order->status == 'delivered' ? 'active' : '' }}">Đã giao</button>
                                        <button type="submit" name="status" value="cancel" class="dropdown-item {{ $order->status == 'cancel' ? 'active' : '' }}">Đã hủy</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <h6 class="text-center">Không tìm thấy đơn hàng Affiliate nào!!!</h6>
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
        .dropdown-item.active {
            background-color: #4e73df;
            color: white;
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
      $('affiliate-orders-dataTable').DataTable( {
            "paging": true,
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100], // Cho phép chọn mục hiển thị
            "ordering": true,
            "searching": true,
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[10]
                }
            ]
        } );

        // Sweet alert
        function deleteData(id){
        }
  </script>
  <script>
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
                icon: "cảnh báo",
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

  <script>
      $(document).ready(function () {
          $('.commission-input').on('change blur keypress', function (event) {
              if (event.type === "keypress" && event.which !== 13) return;

              let inputField = $(this);
              let commissionValue = inputField.val();
              let productId = inputField.data('id');
              let statusMessage = inputField.closest('td').find('.commission-status');
              let url = "{{ route('products-affiliate.update-commission', ':id') }}".replace(':id', productId);

              // Hiện loading khi đang cập nhật
              inputField.prop('disabled', true);
              statusMessage.text('⏳ Đang cập nhật...').removeClass('d-none text-success').addClass('text-warning');

              $.ajax({
                  url: url,
                  type: 'POST',
                  data: {
                      _token: "{{ csrf_token() }}",
                      commission_percentage: commissionValue
                  },
                  success: function (response) {
                      inputField.prop('disabled', false);
                      statusMessage.text('✔ Đã lưu').removeClass('text-warning').addClass('text-success').fadeIn().delay(1000).fadeOut();
                  },
                  error: function (xhr) {
                      inputField.prop('disabled', false);
                      swal("Lỗi!", "Không thể cập nhật hoa hồng, thử lại sau!", "error");
                  }
              });
          });
      });
  </script>
@endpush
