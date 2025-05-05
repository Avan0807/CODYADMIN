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
        <h6 class="m-0 font-weight-bold text-primary float-left">Danh sách sản phẩm</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            @if(count($products)>0)
            <table class="table table-bordered table-hover" id="product-dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Nổi bật</th>
                        <th>Giá</th>
                        <th>Tình trạng</th>
                        <th>Thương hiệu</th>
                        <th>Kho</th>
                        <th>Ảnh</th>
                        <th>Trạng thái</th>
                        <th>Hoa hồng</th>
                        <th>Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $product->title }}</td>
                        <td>
                            {{ $product->category->name ?? '---' }}
                            <br>
                            <small class="text-muted">{{ $product->subCategory->name ?? '' }}</small>
                        </td>
                        <td>{{ $product->is_featured ? 'Có' : 'Không' }}</td>
                        <td>{{ number_format($product->price, 0, ',', '.') }}đ</td>
                        <td>
                            @php
                                $conditionMap = [
                                    'new' => 'Mới',
                                    'default' => 'Mặc định',
                                    'hot' => 'Nổi bật'
                                ];
                            @endphp
                            {{ $conditionMap[$product->condition] ?? $product->condition }}
                        </td>
                        <td>{{ ucfirst($product->brand->title ?? 'Không rõ') }}</td>
                        <td>
                            <span class="badge badge-{{ $product->stock > 0 ? 'primary' : 'danger' }}">
                                {{ $product->stock }}
                            </span>
                        </td>
                        <td>
                            @php $photo = explode(',', $product->photo); @endphp
                            <img src="{{ $photo[0] ?? asset('backend/img/thumbnail-default.jpg') }}"
                                 class="img-fluid zoom"
                                 style="max-width:80px" alt="product image">
                        </td>
                        <td>
                            <span class="badge badge-{{ $product->status == 'active' ? 'success' : 'warning' }}">
                                {{ $product->status == 'active' ? 'Hoạt Động' : 'Không Hoạt Động' }}
                            </span>
                        </td>
                        <td>
                            <div class="input-group commission-group">
                                <input type="number" class="form-control commission-input"
                                       data-id="{{ $product->id }}"
                                       value="{{ number_format($product->commission_percentage, 2) }}"
                                       min="0" max="100" step="0.01">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="text-success commission-status d-none">✔ Đã lưu</small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('product.edit', $product->id) }}"
                               class="btn btn-primary btn-sm"
                               style="height:30px; width:30px; border-radius:50%"
                               title="Chỉnh sửa">
                               <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('product.destroy', $product->id) }}" class="d-inline-block">
                                @csrf
                                @method('delete')
                                <button class="btn btn-danger btn-sm dltBtn"
                                        style="height:30px; width:30px; border-radius:50%"
                                        title="Xoá">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @else
            <h6 class="text-center">Không tìm thấy sản phẩm nào!!! Vui lòng thêm sản phẩm mới</h6>
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
        transition: transform .2s; /* Animation */
      }

      .zoom:hover {
        transform: scale(5);
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
      $('#product-dataTable').DataTable( {
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
