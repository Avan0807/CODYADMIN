@extends('backend.layouts.master')

@section('title','Hồ sơ quản trị')

@section('main-content')

<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
           @include('backend.layouts.notification')
        </div>
    </div>
    <div class="card-header py-3">
        <h4 class="font-weight-bold">Hồ sơ</h4>
        <ul class="breadcrumbs">
            <li><a href="{{ route('admin') }}" style="color:#999">Trang tổng quan</a></li>
            <li><a href="" class="active text-primary">Trang hồ sơ</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Profile Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="image">
                        <img class="card-img-top img-fluid rounded-circle mt-4"
                            style="border-radius:50%;height:80px;width:80px;margin:auto; object-fit: cover;"
                            src="{{ $profile->photo ?? asset('backend/img/avatar.png') }}"
                            alt="profile picture">
                    </div>
                    <div class="card-body mt-4 ml-2">
                        <h5 class="card-title text-left"><small><i class="fas fa-user"></i> {{ $profile->name }}</small></h5>
                        <p class="card-text text-left"><small><i class="fas fa-envelope"></i> {{ $profile->email }}</small></p>
                        <p class="card-text text-left"><small class="text-muted"><i class="fas fa-hammer"></i> {{ $profile->role }}</small></p>
                    </div>
                </div>
            </div>

            <!-- Profile Update Form -->
            <div class="col-md-8">
                <form class="border px-4 pt-2 pb-3" method="POST" action="{{ route('profile-update', $profile->id) }}">
                    @csrf
                    <div class="form-group">
                        <label for="inputTitle" class="col-form-label">Tên</label>
                        <input id="inputTitle" type="text" name="name" placeholder="Nhập tên" value="{{ $profile->name }}" class="form-control">
                        @error('name')
                            <div class="alert alert-danger mt-2 p-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="inputEmail" class="col-form-label">Email</label>
                        <input id="inputEmail" type="email" name="email" value="{{ $profile->email }}" class="form-control">
                        @error('email')
                            <div class="alert alert-danger mt-2 p-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="inputPhone" class="col-form-label">Số điện thoại</label>
                        <input id="inputPhone" type="text" name="phone" placeholder="Nhập số điện thoại" value="{{ $profile->phone ?? '' }}" class="form-control">
                        @error('phone')
                            <div class="alert alert-danger mt-2 p-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="inputPhoto" class="col-form-label">Ảnh</label>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary text-white">
                                    <i class="fa fa-picture-o"></i> Chọn
                                </a>
                            </span>
                            <input id="thumbnail" class="form-control" type="text" name="photo" value="{{ $profile->photo }}">
                        </div>
                        @error('photo')
                            <div class="alert alert-danger mt-2 p-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="role" class="col-form-label">Vai trò</label>
                        @php
                            $roles = ['admin' => 'Quản trị viên', 'user' => 'Người dùng', 'doctor' => 'Bác sĩ'];
                        @endphp
                        <select name="role" class="form-control" disabled>
                            <option value="">-----Chọn vai trò-----</option>
                            @foreach ($roles as $key => $value)
                                <option value="{{ $key }}" {{ $profile->role == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Bạn có chắc chắn muốn cập nhật hồ sơ không?')">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Custom CSS -->
<style>
    .breadcrumbs {
        list-style: none;
    }
    .breadcrumbs li {
        float: left;
        margin-right: 10px;
    }
    .breadcrumbs li a:hover {
        text-decoration: none;
    }
    .breadcrumbs li .active {
        color: red;
    }
    .breadcrumbs li + li:before {
        content: "/\00a0";
    }
    .image {
        background: url('{{ asset('backend/img/background.jpg') }}') center/cover no-repeat;
        height: 150px;
        position: relative;
    }
    .image img {
        position: absolute;
        top: 55%;
        left: 50%;
        transform: translate(-50%, -50%);
        object-fit: cover;
    }
    i {
        font-size: 14px;
        padding-right: 8px;
    }
</style>

<!-- Scripts -->
@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script>
    $('#lfm').filemanager('image');
</script>
@endpush
