@extends('backend.layouts.master')

@section('title','CODY || Thêm phòng khám')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm Phòng Khám</h5>
    <div class="card-body">
      <form method="post" action="{{ route('clinics.store') }}">
        @csrf

        <div class="form-group">
          <label for="name" class="col-form-label">Tên phòng khám <span class="text-danger">*</span></label>
          <input id="name" type="text" name="name" placeholder="Nhập tên phòng khám" value="{{ old('name') }}" class="form-control">
          @error('name')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="address" class="col-form-label">Địa chỉ <span class="text-danger">*</span></label>
          <textarea class="form-control" id="address" name="address">{{ old('address') }}</textarea>
          @error('address')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="phone" class="col-form-label">Số điện thoại <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
          @error('phone')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="email" class="col-form-label">Email</label>
          <input type="email" class="form-control" name="email" value="{{ old('email') }}">
        </div>

        <div class="form-group">
          <button type="reset" class="btn btn-warning">Đặt lại</button>
          <button class="btn btn-success" type="submit">Thêm</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
<script>
    $(document).ready(function() {
      $('#message').summernote({
        placeholder: "Nhập phòng khám...",
          tabsize: 2,
          height: 150
      });
    });
</script>
@endpush