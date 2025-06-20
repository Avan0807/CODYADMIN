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
        <h6 class="m-0 font-weight-bold text-primary">Danh sách Đại lý</h6>
        <div>
            <a href="{{ route('agent.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm Đại lý
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Row -->
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="statusFilter" class="form-control form-control-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Không hoạt động</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="suspended">Bị khóa</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="ratingFilter" class="form-control form-control-sm">
                    <option value="">Tất cả đánh giá</option>
                    <option value="5">5 sao</option>
                    <option value="4">4+ sao</option>
                    <option value="3">3+ sao</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            @if(count($agents) > 0)
            <table class="table table-bordered table-hover" id="agents-dataTable" width="100%" cellspacing="0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">Thông tin</th>
                        <th width="15%">Liên hệ</th>
                        <th width="15%">Công ty</th>
                        <th width="10%">Đánh giá</th>
                        <th width="10%">Hoa hồng</th>
                        <th width="10%">Trạng thái</th>
                        <th width="10%">Ngày tạo</th>
                        <th width="10%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($agents as $agent)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        
                        <!-- Thông tin -->
                        <td>
                            <div class="d-flex align-items-center">
                                @if($agent->photo)
                                    <img src="{{ asset('storage/' . $agent->photo) }}" class="rounded-circle mr-2" width="40" height="40">
                                @else
                                    <div class="bg-primary rounded-circle mr-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-weight-bold">{{ $agent->name }}</div>
                                    <small class="text-muted">ID: #{{ $agent->id }}</small>
                                    @if($agent->referral_code)
                                        <br><small class="text-info">{{ $agent->referral_code }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        
                        <!-- Liên hệ -->
                        <td>
                            <div>
                                <i class="fas fa-envelope text-muted"></i> {{ $agent->email }}
                            </div>
                            <div>
                                <i class="fas fa-phone text-muted"></i> {{ $agent->phone }}
                            </div>
                            @if($agent->location)
                                <div>
                                    <i class="fas fa-map-marker-alt text-muted"></i> {{ $agent->location }}
                                </div>
                            @endif
                        </td>
                        
                        <!-- Công ty -->
                        <td>
                            @if($agent->company)
                                <div class="font-weight-bold">{{ $agent->company }}</div>
                                @if($agent->business_type)
                                    <small class="text-muted">{{ $agent->business_type }}</small>
                                @endif
                            @else
                                <span class="text-muted">Cá nhân</span>
                            @endif
                            @if($agent->experience)
                                <br><small class="text-success">{{ $agent->experience }} năm KN</small>
                            @endif
                        </td>
                        
                        <!-- Đánh giá -->
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold text-warning">{{ number_format($agent->rating, 1) }}</span>
                                <div class="ml-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $agent->rating ? 'text-warning' : 'text-muted' }}" style="font-size: 0.8rem;"></i>
                                    @endfor
                                </div>
                            </div>
                        </td>
                        
                        <!-- Hoa hồng -->
                        <td>
                            <div class="font-weight-bold text-success">{{ $agent->commission_rate }}%</div>
                            <small class="text-muted">Tổng: {{ number_format($agent->total_commission, 0, ',', '.') }}đ</small>
                        </td>
                        
                        <!-- Trạng thái -->
                        <td>
                            @php
                                $statusConfig = [
                                    'active' => ['class' => 'success', 'text' => 'Hoạt động'],
                                    'inactive' => ['class' => 'secondary', 'text' => 'Không hoạt động'],
                                    'pending' => ['class' => 'warning', 'text' => 'Chờ duyệt'],
                                    'suspended' => ['class' => 'danger', 'text' => 'Bị khóa'],
                                ];
                                $config = $statusConfig[$agent->status] ?? ['class' => 'secondary', 'text' => 'Không xác định'];
                            @endphp
                            <span class="badge badge-{{ $config['class'] }} badge-pill">{{ $config['text'] }}</span>
                        </td>
                        
                        <!-- Ngày tạo -->
                        <td>
                            <div>{{ $agent->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $agent->created_at->format('H:i') }}</small>
                        </td>
                        
                        <!-- Hành động -->
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="{{ route('agent.show', $agent->id) }}" class="dropdown-item">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
                                    <a href="{{ route('agent.edit', $agent->id) }}" class="dropdown-item">
                                        <i class="fas fa-edit"></i> Chỉnh sửa
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    
                                    <!-- Status Actions -->
                                    @if($agent->status == 'pending')
                                        <form method="POST" action="{{ route('agent.update-status', $agent->id) }}">
                                            @csrf
                                            <button type="submit" name="status" value="active" class="dropdown-item text-success">
                                                <i class="fas fa-check"></i> Duyệt kích hoạt
                                            </button>
                                        </form>
                                    @elseif($agent->status == 'active')
                                        <form method="POST" action="{{ route('agent.update-status', $agent->id) }}">
                                            @csrf
                                            <button type="submit" name="status" value="inactive" class="dropdown-item text-warning">
                                                <i class="fas fa-pause"></i> Tạm ngưng
                                            </button>
                                        </form>
                                    @elseif($agent->status == 'inactive')
                                        <form method="POST" action="{{ route('agent.update-status', $agent->id) }}">
                                            @csrf
                                            <button type="submit" name="status" value="active" class="dropdown-item text-success">
                                                <i class="fas fa-play"></i> Kích hoạt
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($agent->status != 'suspended')
                                        <form method="POST" action="{{ route('agent.update-status', $agent->id) }}">
                                            @csrf
                                            <button type="submit" name="status" value="suspended" class="dropdown-item text-danger">
                                                <i class="fas fa-ban"></i> Khóa tài khoản
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('agent.destroy', $agent->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
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
            @else
            <div class="text-center py-5">
                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">Chưa có đại lý nào!</h6>
                <p class="text-muted">
                    <a href="{{ route('agent.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm đại lý đầu tiên
                    </a>
                </p>
            </div>
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
    .badge-pill {
        border-radius: 50rem;
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
let table = $('#agents-dataTable').DataTable({
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
            "targets": [8] // Cột hành động không sort được
        }
    ],
    "order": [[7, 'desc']] // Sort theo ngày tạo mới nhất
});

// Filter by status
$('#statusFilter').on('change', function() {
    table.column(6).search(this.value).draw();
});

// Filter by rating
$('#ratingFilter').on('change', function() {
    if (this.value) {
        table.column(4).search(this.value + '+', true, false).draw();
    } else {
        table.column(4).search('').draw();
    }
});

// Confirm trước khi update status
$('form[action*="agent.update-status"]').on('submit', function(e) {
    e.preventDefault();
    
    let form = $(this);
    let status = form.find('button[type="submit"]:focus').val();
    let statusText = form.find('button[type="submit"]:focus').text().trim();
    
    swal({
        title: "Xác nhận",
        text: `Bạn có chắc chắn muốn ${statusText.toLowerCase()}?`,
        icon: "warning",
        buttons: ["Hủy", "Xác nhận"],
        dangerMode: status === 'suspended',
    })
    .then((willUpdate) => {
        if (willUpdate) {
            form.off('submit').submit();
        }
    });
});
</script>
@endpush