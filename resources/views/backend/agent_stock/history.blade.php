@extends('backend.layouts.master')

@section('main-content')
<div class="card">
  <h5 class="card-header">Lịch sử nhập hàng đại lý</h5>
  <div class="card-body table-responsive">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Thời gian</th>
          <th>Đại lý</th>
          <th>Sản phẩm</th>
          <th>Số lượng</th>
          <th>Ghi chú</th>

        </tr>
      </thead>
      <tbody>
        @foreach($histories as $history)
        <tr>
          <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
          <td>{{ $history->agent->name ?? 'N/A' }}</td>
          <td>{{ $history->product->title ?? 'N/A' }}</td>
          <td>{{ $history->quantity }}</td>
          <td>{{ $history->note }}</td>
          
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
