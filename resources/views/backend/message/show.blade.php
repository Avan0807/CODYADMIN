@extends('backend.layouts.master')
@section('main-content')
<div class="card">
  <h5 class="card-header">Tin nhắn</h5>
  <div class="card-body">
    @if($message)
        @if($message->photo)
        <img src="{{$message->photo}}" class="rounded-circle img-fluid" style="max-width: 150px; height: auto;">
        @else
        <img src="{{asset('backend/img/avatar.png')}}" class="rounded-circle " style="max-width: 150px; height: auto;">
        @endif
        <div class="py-4">Từ: <br>
           Tên :{{$message->name}}<br>
           Email :{{$message->email}}<br>
           Số điện thoại :{{$message->phone}}
        </div>
        <hr/>
  <h5 class="text-center" style="text-decoration:underline"><strong>Chủ đề :</strong> {{$message->subject}}</h5>
        <p class="py-5">{{$message->message}}</p>

    @endif

  </div>
</div>
@endsection
