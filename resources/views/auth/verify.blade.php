@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Xác minh số điện thoại của bạn') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('Một mã xác minh mới đã được gửi tới số điện thoại của bạn.') }}
                        </div>
                    @endif

                    {{ __('Trước khi tiếp tục, vui lòng kiểm tra tin nhắn SMS để biết mã xác minh.') }}
                    {{ __('Nếu bạn không nhận được tin nhắn') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('nhấp vào đây để yêu cầu mã khác') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
