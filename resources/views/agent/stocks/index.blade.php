@extends('agent.layouts.master')

@section('title', 'Tồn kho của tôi')

@section('main-content')
<div class="dashboard-container">
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="welcome-card animate__animated animate__fadeInDown mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">📦 Tồn kho sản phẩm</h1>
                    <p class="mb-0 opacity-75">Kiểm tra số lượng sản phẩm còn trong kho của bạn</p>
                </div>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="recent-orders-card animate__animated animate__fadeInUp">
            <div class="card-header-custom">
                <h6 class="mb-0">
                    <i class="fas fa-boxes mr-2"></i>Danh sách tồn kho
                    <span class="badge badge-light ml-2">{{ $stocks->count() }} sản phẩm</span>
                </h6>
            </div>

            <div class="card-body p-0">
                @if($stocks->count())
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag mr-2"></i></th>
                                <th><i class="fas fa-box-open mr-2"></i>Sản phẩm</th>
                                <th><i class="fas fa-image mr-2"></i>Ảnh</th>
                                <th><i class="fas fa-layer-group mr-2"></i>Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stocks as $index => $stock)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $stock->product->title ?? 'Không rõ' }}</strong><br>
                                </td>
<td>
    @php
        $photos = explode(',', $stock->product->photo);
        $firstPhoto = $photos[0] ?? null;
        $photoUrl = $firstPhoto
            ? (Str::startsWith($firstPhoto, 'http') 
                ? $firstPhoto 
                : Storage::disk('s3')->url($firstPhoto))
            : asset('backend/img/thumbnail-default.jpg');
    @endphp

    <img src="{{ $photoUrl }}"
         alt="Ảnh sản phẩm"
         class="rounded"
         style="width: 60px; height: 60px; object-fit: cover;">
</td>


                                <td>
                                    <span class="badge badge-modern badge-{{ $stock->quantity > 10 ? 'success' : ($stock->quantity > 0 ? 'warning' : 'danger') }}">
                                        {{ $stock->quantity }} cái
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <h6>Không có sản phẩm tồn kho</h6>
                    <p>Liên hệ quản trị viên để được cấp hàng</p>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
