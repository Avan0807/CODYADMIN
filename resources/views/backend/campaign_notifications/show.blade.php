@extends('backend.layouts.master')

@section('title','CODY || Chi tiết thông báo chiến dịch')

@section('main-content')
<div class="card">
  <h5 class="card-header">Chi tiết Thông báo Chiến Dịch</h5>
  <div class="card-body">
    @if($campaign_notification)
        <div class="py-4">
            <strong>Tiêu đề:</strong> {{$campaign_notification->title}}<br>
            <strong>Đối tượng nhận:</strong> {{ ucfirst($campaign_notification->target_audience) }}<br>
            <strong>Ngày tạo:</strong> {{$campaign_notification->created_at->format('F d, Y h:i A')}}
        </div>
        <hr/>
        <h5 class="text-center" style="text-decoration:underline"><strong>Nội dung Thông báo</strong></h5>
        <p class="py-5">{{$campaign_notification->message}}</p>
    @else
        <h5 class="text-center">Không tìm thấy thông báo chiến dịch!</h5>
    @endif
  </div>
</div>
@endsection
