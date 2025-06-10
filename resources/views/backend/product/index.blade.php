@extends('backend.layouts.master')

@section('main-content')
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
            @include('backend.layouts.notification')
        </div>
    </div>
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách sản phẩm</h6>
        <div>
            <a href="{{ route('product.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm sản phẩm
            </a>
            <button class="btn btn-success btn-sm" id="bulk-commission-btn">
                <i class="fas fa-percent"></i> Cập nhật hoa hồng hàng loạt
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            @if(count($products)>0)
            <table class="table table-bordered table-hover" id="product-dataTable" width="100%" cellspacing="0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="3%">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th width="3%">#</th>
                        <th width="15%">Sản phẩm</th>
                        <th width="12%">Danh mục</th>
                        <th width="8%">Giá</th>
                        <th width="5%">Kho</th>
                        <th width="8%">Trạng thái</th>
                        <th width="10%">Hoa hồng</th>
                        <th width="8%">Thống kê</th>
                        <th width="8%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
                        </td>
                        <td>{{ $loop->iteration }}</td>
                        
                        <!-- Cột Sản phẩm -->
                        <td>
                            <div class="d-flex align-items-center">
                                @php $photo = explode(',', $product->photo); @endphp
                                <img src="{{ $photo[0] ?? asset('backend/img/thumbnail-default.jpg') }}"
                                     class="img-thumbnail me-3"
                                     style="width:50px; height:50px; object-fit:cover;" alt="product image">
                                <div>
                                    <div class="font-weight-bold">{{ Str::limit($product->title, 30) }}</div>
                                    <small class="text-muted">{{ $product->slug }}</small>
                                    @if($product->is_featured)
                                        <br><span class="badge badge-warning badge-sm">Nổi bật</span>
                                    @endif
                                    <span class="badge badge-{{$product->condition == 'new' ? 'success' : ($product->condition == 'hot' ? 'danger' : 'secondary')}} badge-sm">
                                        {{ $product->condition == 'new' ? 'Mới' : ($product->condition == 'hot' ? 'Hot' : 'Mặc định') }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Cột Danh mục -->
                        <td>
                            @if($product->categories->isNotEmpty())
                                @foreach($product->categories->take(2) as $category)
                                    <span class="badge badge-info badge-sm">{{ $category->name }}</span>
                                    @if(!$loop->last)<br>@endif
                                @endforeach
                                @if($product->categories->count() > 2)
                                    <br><small class="text-muted">+{{ $product->categories->count() - 2 }} khác</small>
                                @endif
                            @else
                                <span class="text-muted">Chưa phân loại</span>
                            @endif
                        </td>
                        
                        <!-- Cột Giá -->
                        <td>
                            <div class="font-weight-bold text-success">{{ number_format($product->price, 0, ',', '.') }}đ</div>
                            @if($product->discount > 0)
                                <small class="text-danger">-{{ $product->discount }}%</small>
                                <br><small class="text-muted">{{ number_format($product->discount_price, 0, ',', '.') }}đ</small>
                            @endif
                            @if($product->brand)
                                <br><small class="text-info">{{ $product->brand->title }}</small>
                            @endif
                        </td>
                        
                        <!-- Cột Kho -->
                        <td class="text-center">
                            <span class="badge badge-{{ $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger') }} badge-lg">
                                {{ $product->stock }}
                            </span>
                            <br><small class="text-muted">
                                {{ $product->stock > 10 ? 'Còn hàng' : ($product->stock > 0 ? 'Sắp hết' : 'Hết hàng') }}
                            </small>
                        </td>
                        
                        <!-- Cột Trạng thái -->
                        <td class="text-center">
                            <span class="badge badge-{{ $product->status == 'active' ? 'success' : 'secondary' }} badge-pill">
                                {{ $product->status == 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                            </span>
                            @if($product->reviews_count > 0)
                                <br><small class="text-warning">
                                    ⭐ {{ number_format($product->reviews_avg_rate, 1) }} ({{ $product->reviews_count }})
                                </small>
                            @endif
                        </td>
                        
                        <!-- Cột Hoa hồng -->
                        <td>
                            <div class="input-group commission-group">
                                <input type="number" class="form-control commission-input form-control-sm"
                                       data-id="{{ $product->id }}"
                                       value="{{ number_format($product->commission_percentage, 2) }}"
                                       min="0" max="100" step="0.01"
                                       placeholder="0.00">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="commission-status d-none"></small>
                            @if($product->hasCommission())
                                <small class="text-success">💰 Có hoa hồng</small>
                            @endif
                        </td>
                        
                        <!-- Cột Thống kê -->
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm">
                                <span class="badge badge-info">Đã bán: {{ $product->getTotalSold() }}</span>
                                @if($product->hasCommission())
                                    <span class="badge badge-success">HH: {{ number_format($product->getTotalCommissionGenerated(), 0, ',', '.') }}đ</span>
                                @endif
                            </div>
                        </td>
                        
                        <!-- Cột Hành động -->
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('product.show', $product->id) }}" class="btn btn-info btn-sm" title="Xem">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('product.edit', $product->id) }}" class="btn btn-primary btn-sm" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('product.destroy', $product->id) }}" class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-danger btn-sm dltBtn" data-id="{{ $product->id }}" title="Xóa">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @else
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">Chưa có sản phẩm nào!</h6>
                <p class="text-muted">Vui lòng thêm sản phẩm mới để bắt đầu.</p>
                <a href="{{ route('product.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm sản phẩm đầu tiên
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Commission Modal -->
<div class="modal fade" id="bulkCommissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật hoa hồng hàng loạt</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bulk-commission-form">
                    <div class="form-group">
                        <label>Phần trăm hoa hồng mới:</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="bulk-commission-value" 
                                   min="0" max="100" step="0.01" placeholder="0.00">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Sẽ cập nhật cho <span id="selected-count">0</span> sản phẩm đã chọn.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="apply-bulk-commission">Áp dụng</button>
            </div>
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
    
    .commission-input {
        font-size: 12px;
    }
    
    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    
    .img-thumbnail {
        border-radius: 8px;
    }
    
    .btn-group-sm > .btn {
        margin: 1px;
    }
</style>
@endpush

@push('scripts')
<!-- Page level plugins -->
<script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
$(document).ready(function() {
    // DataTable configuration
    $('#product-dataTable').DataTable({
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
                "targets": [0, 9] // Checkbox và Actions columns
            }
        ],
        "order": [[1, 'desc']] // Sort by ID desc
    });

    // CSRF setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Select All functionality
    $('#select-all').change(function() {
        $('.product-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });

    $('.product-checkbox').change(function() {
        updateSelectedCount();
        $('#select-all').prop('checked', $('.product-checkbox:checked').length === $('.product-checkbox').length);
    });

    function updateSelectedCount() {
        const count = $('.product-checkbox:checked').length;
        $('#selected-count').text(count);
        $('#bulk-commission-btn').prop('disabled', count === 0);
    }

    // Bulk commission modal
    $('#bulk-commission-btn').click(function() {
        const selectedCount = $('.product-checkbox:checked').length;
        if (selectedCount === 0) {
            swal("Thông báo!", "Vui lòng chọn ít nhất một sản phẩm!", "warning");
            return;
        }
        $('#bulkCommissionModal').modal('show');
    });

    // Apply bulk commission
    $('#apply-bulk-commission').click(function() {
        const commissionValue = $('#bulk-commission-value').val();
        const selectedIds = $('.product-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!commissionValue || selectedIds.length === 0) {
            swal("Lỗi!", "Vui lòng nhập giá trị hoa hồng và chọn sản phẩm!", "error");
            return;
        }

        $.ajax({
            url: "{{ route('products.bulk-commission') }}",
            type: 'POST',
            data: {
                product_ids: selectedIds,
                commission_percentage: commissionValue
            },
            beforeSend: function() {
                $('#apply-bulk-commission').prop('disabled', true).text('Đang xử lý...');
            },
            success: function(response) {
                $('#bulkCommissionModal').modal('hide');
                swal("Thành công!", response.message, "success").then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                swal("Lỗi!", "Không thể cập nhật hoa hồng!", "error");
            },
            complete: function() {
                $('#apply-bulk-commission').prop('disabled', false).text('Áp dụng');
            }
        });
    });

    // Individual commission update
    $('.commission-input').on('change blur keypress', function(event) {
        if (event.type === "keypress" && event.which !== 13) return;

        let inputField = $(this);
        let commissionValue = inputField.val();
        let productId = inputField.data('id');
        let statusMessage = inputField.closest('td').find('.commission-status');
        let url = "{{ route('products-affiliate.update-commission', ':id') }}".replace(':id', productId);

        inputField.prop('disabled', true);
        statusMessage.text('⏳ Đang cập nhật...').removeClass('d-none text-success text-danger').addClass('text-warning').show();

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                commission_percentage: commissionValue
            },
            success: function(response) {
                statusMessage.text('✔ Đã lưu').removeClass('text-warning').addClass('text-success');
                setTimeout(() => statusMessage.fadeOut(), 2000);
            },
            error: function(xhr) {
                statusMessage.text('✘ Lỗi').removeClass('text-warning').addClass('text-danger');
                swal("Lỗi!", "Không thể cập nhật hoa hồng!", "error");
            },
            complete: function() {
                inputField.prop('disabled', false);
            }
        });
    });

    // Delete confirmation
    $('.dltBtn').click(function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        
        swal({
            title: "Bạn có chắc không?",
            text: "Sau khi xóa, bạn sẽ không thể khôi phục dữ liệu này!",
            icon: "warning",
            buttons: ["Hủy", "Xóa"],
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                form.submit();
            }
        });
    });
});
</script>
@endpush