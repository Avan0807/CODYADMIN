@extends('backend.layouts.master')

@section('title', 'Nhập hàng cho đại lý')

@section('main-content')
<div class="container-fluid">
    <h4 class="mb-4">➕ Nhập hàng cho đại lý</h4>

    <form action="{{ route('agent.stocks.store') }}" method="POST">
        @csrf
        <div class="card shadow">
            <div class="card-body">
                <div class="form-group">
                    <label for="agent_id">Đại lý</label>
                    <select name="agent_id" id="agent_id" class="form-control" required>
                        <option value="">-- Chọn đại lý --</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="product_id">Sản phẩm</label>
                    <select name="product_id" id="product_id" class="form-control" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Số lượng</label>
                    <input type="number" name="quantity" class="form-control" required min="1">
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check mr-1"></i> Nhập hàng
                </button>
                <a href="{{ route('agent.stocks.index', 1) }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>
    </form>
</div>
@endsection
