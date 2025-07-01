@extends('backend.layouts.master')

@section('title', 'Tồn kho đại lý')

@section('main-content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">📦 Tồn kho đại lý</h4>
            <small class="text-muted">Quản lý số lượng hàng đã chia cho từng đại lý</small>
        </div>
        <a href="{{ route('admin.agent.stocks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nhập hàng cho đại lý
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            @if($stocks->count())
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Đại lý</th>
                            <th>Sản phẩm</th>
                            <th>Tồn kho</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $index => $stock)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $stock->agent->name }}</strong><br>
                                <small class="text-muted">{{ $stock->agent->email }}</small>
                            </td>
                            <td>
                                {{ $stock->product->title ?? 'Không rõ' }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $stock->quantity > 10 ? 'success' : 'warning' }}">
                                    {{ $stock->quantity }} cái
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-4 text-center text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>Chưa có dữ liệu tồn kho nào</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
