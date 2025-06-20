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
        <h6 class="m-0 font-weight-bold text-primary">Quản lý Link Affiliate Đại lý</h6>
        <div>
            <span class="badge badge-info">
                Tổng: {{ $stats['total_links'] ?? 0 }} links
            </span>
            <a href="{{ route('agent.links.create') }}" class="btn btn-primary btn-sm ml-2">
                <i class="fas fa-plus"></i> Tạo Link
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Row -->
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="agentFilter" class="form-control form-control-sm">
                    <option value="">Tất cả đại lý</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="productFilter" class="form-control form-control-sm">
                    <option value="">Tất cả sản phẩm</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button id="generateBulkLinks" class="btn btn-success btn-sm">
                    <i class="fas fa-magic"></i> Tạo links hàng loạt
                </button>
            </div>
        </div>

        <div class="table-responsive">
            @if(count($agentLinks) > 0)
            <table class="table table-bordered table-hover" id="agent-links-dataTable" width="100%" cellspacing="0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="5%">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th width="15%">Đại lý</th>
                        <th width="20%">Sản phẩm</th>
                        <th width="15%">Hash Reference</th>
                        <th width="10%">Hoa hồng</th>
                        <th width="15%">Link</th>
                        <th width="10%">Ngày tạo</th>
                        <th width="10%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($agentLinks as $agentLink)
                    <tr>
                        <td>
                            <input type="checkbox" class="link-checkbox" value="{{ $agentLink->id }}">
                        </td>
                        
                        <!-- Đại lý -->
                        <td>
                            <div class="d-flex align-items-center">
                                @if($agentLink->agent->photo)
                                    <img src="{{ asset('storage/' . $agentLink->agent->photo) }}" class="rounded-circle mr-2" width="30" height="30">
                                @else
                                    <div class="bg-primary rounded-circle mr-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                        <i class="fas fa-user text-white" style="font-size: 0.7rem;"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-weight-bold">{{ $agentLink->agent->name }}</div>
                                    @if($agentLink->agent->company)
                                        <small class="text-muted">{{ $agentLink->agent->company }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        
                        <!-- Sản phẩm -->
                        <td>
                            <div class="d-flex align-items-center">
                                @if($agentLink->product->photo)
                                    <img src="{{ $agentLink->product->photo }}" class="rounded mr-2" width="40" height="40" style="object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded mr-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-box text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-weight-bold">{{ Str::limit($agentLink->product->title, 30) }}</div>
                                    <small class="text-success">{{ number_format($agentLink->product->price, 0, ',', '.') }}đ</small>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Hash Reference -->
                        <td>
                            <div class="d-flex align-items-center">
                                <code class="bg-light p-1 rounded">{{ $agentLink->hash_ref }}</code>
                                <button class="btn btn-sm btn-outline-secondary ml-1" onclick="copyToClipboard('{{ $agentLink->hash_ref }}')" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        
                        <!-- Hoa hồng -->
                        <td>
                            <span class="badge badge-success badge-pill">{{ $agentLink->commission_percentage }}%</span>
                        </td>
                        
                        <!-- Link -->
                        <td>
                            <div class="d-flex align-items-center">
                                <button class="btn btn-sm btn-primary" onclick="copyToClipboard('{{ $agentLink->product_link }}')" title="Copy Link">
                                    <i class="fas fa-link"></i> Copy
                                </button>
                                <a href="{{ $agentLink->product_link }}" target="_blank" class="btn btn-sm btn-outline-primary ml-1" title="Mở link">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </td>
                        
                        <!-- Ngày tạo -->
                        <td>
                            <div>{{ $agentLink->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $agentLink->created_at->format('H:i') }}</small>
                        </td>
                        
                        <!-- Hành động -->
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="{{ route('agent.links.edit', $agentLink->id) }}" class="dropdown-item">
                                        <i class="fas fa-edit"></i> Chỉnh sửa
                                    </a>
                                    <button class="dropdown-item" onclick="copyToClipboard('{{ $agentLink->product_link }}')">
                                        <i class="fas fa-copy"></i> Copy Link
                                    </button>
                                    <button class="dropdown-item" onclick="generateQRCode('{{ $agentLink->product_link }}')">
                                        <i class="fas fa-qrcode"></i> Tạo QR Code
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('agent.links.destroy', $agentLink->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Bulk Actions -->
            <div class="mt-3" id="bulkActions" style="display: none;">
                <button class="btn btn-danger btn-sm" onclick="bulkDeleteLinks()">
                    <i class="fas fa-trash"></i> Xóa đã chọn
                </button>
                <button class="btn btn-info btn-sm ml-2" onclick="bulkExportLinks()">
                    <i class="fas fa-download"></i> Xuất đã chọn
                </button>
            </div>
            
            @else
            <div class="text-center py-5">
                <i class="fas fa-link fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">Chưa có link affiliate nào!</h6>
                <p class="text-muted">
                    <a href="{{ route('agent.links.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo link đầu tiên
                    </a>
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCodeContainer"></div>
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
    .dropdown-item.active {
        background-color: #4e73df;
        color: white;
    }
    .badge-pill {
        border-radius: 50rem;
    }
    code {
        font-size: 0.8rem;
    }
</style>
@endpush

@push('scripts')
<!-- Page level plugins -->
<script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

<!-- Page level custom scripts -->
<script src="{{asset('backend/js/demo/datatables-demo.js')}}"></script>
<script>
let table = $('#agent-links-dataTable').DataTable({
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
            "targets": [0, 7] // Checkbox và hành động không sort được
        }
    ],
    "order": [[6, 'desc']] // Sort theo ngày tạo mới nhất
});

// Filter by agent
$('#agentFilter').on('change', function() {
    table.column(1).search(this.value).draw();
});

// Filter by product
$('#productFilter').on('change', function() {
    table.column(2).search(this.value).draw();
});

// Select all checkboxes
$('#selectAll').on('change', function() {
    $('.link-checkbox').prop('checked', this.checked);
    toggleBulkActions();
});

// Individual checkbox
$(document).on('change', '.link-checkbox', function() {
    toggleBulkActions();
});

function toggleBulkActions() {
    const checkedCount = $('.link-checkbox:checked').length;
    if (checkedCount > 0) {
        $('#bulkActions').show();
    } else {
        $('#bulkActions').hide();
    }
    
    // Update select all state
    const totalCount = $('.link-checkbox').length;
    $('#selectAll').prop('indeterminate', checkedCount > 0 && checkedCount < totalCount);
    $('#selectAll').prop('checked', checkedCount === totalCount);
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        swal({
            title: "Đã copy!",
            text: "Đã sao chép vào clipboard",
            icon: "success",
            timer: 1500,
            buttons: false
        });
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        swal({
            title: "Đã copy!",
            text: "Đã sao chép vào clipboard",
            icon: "success",
            timer: 1500,
            buttons: false
        });
    });
}

// Generate QR Code
function generateQRCode(url) {
    $('#qrCodeContainer').empty();
    QRCode.toCanvas(url, { width: 256 }, function (err, canvas) {
        if (err) console.error(err);
        $('#qrCodeContainer').append(canvas);
        $('#qrCodeModal').modal('show');
    });
}

// Bulk delete
function bulkDeleteLinks() {
    const selectedIds = $('.link-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) return;
    
    swal({
        title: "Xác nhận xóa",
        text: `Bạn có chắc chắn muốn xóa ${selectedIds.length} links đã chọn?`,
        icon: "warning",
        buttons: ["Hủy", "Xóa"],
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: "{{ route('agent.links.bulk-delete') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    link_ids: selectedIds
                },
                success: function(response) {
                    if (response.success) {
                        swal("Thành công!", response.message, "success")
                        .then(() => location.reload());
                    }
                },
                error: function() {
                    swal("Lỗi!", "Có lỗi xảy ra khi xóa", "error");
                }
            });
        }
    });
}

// Bulk export
function bulkExportLinks() {
    const selectedIds = $('.link-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) return;
    
    // Create and submit form for export
    const form = $('<form>', {
        method: 'POST',
        action: "{{ route('agent.links.export') }}"
    });
    
    form.append($('<input>', {
        type: 'hidden',
        name: '_token',
        value: "{{ csrf_token() }}"
    }));
    
    selectedIds.forEach(id => {
        form.append($('<input>', {
            type: 'hidden',
            name: 'link_ids[]',
            value: id
        }));
    });
    
    $('body').append(form);
    form.submit();
    form.remove();
}
</script>
@endpush