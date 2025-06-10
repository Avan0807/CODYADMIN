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
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách đơn hàng</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($orders)>0)
        <table class="table table-bordered table-hover" id="order-dataTable" width="100%" cellspacing="0">
          <thead class="bg-primary text-white">
    <tr>
      <th width="5%">#</th>
      <th width="15%">Đơn hàng</th>
      <th width="25%">Khách hàng</th>
      <th width="20%">Sản phẩm</th>
      <th width="15%">Thanh toán</th>
      <th width="10%">Trạng thái</th>
      <th width="10%">Hành động</th>
    </tr>
          </thead>
          <tbody>
            @foreach($orders as $order)
            <tr>
              <td>{{ $loop->iteration }}</td>
              
              <!-- Cột Đơn hàng -->
              <td>
                <div class="font-weight-bold text-primary">{{ $order->order_number }}</div>
                <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                @if($order->canTrackGHN())
                  <br><a href="{{ $order->getTrackingUrl() }}" target="_blank" class="badge badge-info">
                    <i class="fas fa-truck"></i> Track
                  </a>
                @endif
              </td>
              
              <!-- Cột Khách hàng -->
              <td>
                <div class="font-weight-bold">{{ $order->first_name }} {{ $order->last_name }}</div>
                <small class="text-muted d-block">{{ $order->email }}</small>
                <small class="text-muted">{{ $order->phone }}</small>
              </td>
              
              <!-- Cột Sản phẩm -->
              <td>
                @foreach($order->cartInfo->take(2) as $cart)
                  <div class="mb-1">
                    <span class="badge badge-light">{{ $cart->quantity }}x</span>
                    {{ Str::limit($cart->product->title ?? 'N/A', 30) }}
                  </div>
                @endforeach
                @if($order->cartInfo->count() > 2)
                  <small class="text-info">+{{ $order->cartInfo->count() - 2 }} sản phẩm khác</small>
                @endif
              </td>
              
              <!-- Cột Thanh toán -->
              <td>
                <div class="font-weight-bold text-success">{{ number_format($order->total_amount, 0, ',', '.') }}đ</div>
                @if($order->shipping_cost > 0)
                  <small class="text-muted">Ship: {{ number_format($order->shipping_cost, 0, ',', '.') }}đ</small>
                @endif
              </td>
              
              <!-- Cột Trạng thái -->
              <td>
                @if($order->status=='new')
                  <span class="badge badge-primary badge-pill">MỚI</span>
                @elseif($order->status=='process')
                  <span class="badge badge-warning badge-pill">XỬ LÝ</span>
                @elseif($order->status=='delivered')
                  <span class="badge badge-success badge-pill">ĐÃ GIAO</span>
                @else
                  <span class="badge badge-danger badge-pill">{{ strtoupper($order->status) }}</span>
                @endif
              </td>
              
              <!-- Cột Hành động -->
                    <td>
                        <a href="{{route('order.show',$order->id)}}" class="btn btn-warning btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="view" data-placement="bottom"><i class="fas fa-eye"></i></a>
                        <a href="{{route('order.edit',$order->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="edit" data-placement="bottom"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{route('order.destroy',[$order->id])}}">
                          @csrf
                          @method('delete')
                              <button class="btn btn-danger btn-sm dltBtn" data-id={{$order->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @else
          <h6 class="text-center">Không tìm thấy đơn hàng nào!!! Vui lòng đặt hàng một số sản phẩm</h6>
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
$('#order-dataTable').DataTable({
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
    "columnDefs": [
        {
            "orderable": false,
            "targets": [6] // Cột "Hành động" (index 6, không phải 8)
        },
        {
            "searchable": false,
            "targets": [0, 6] // Không tìm kiếm cột # và Hành động
        }
    ],
    "order": [[1, 'desc']] // Sắp xếp theo cột "Đơn hàng" mới nhất
});
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
              // alert(dataID);
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
@endpush
