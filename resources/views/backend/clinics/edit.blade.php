@extends('backend.layouts.master')

@section('title','CODY || Chỉnh sửa phòng khám')

@section('main-content')

<div class="card">
    <h5 class="card-header">Chỉnh Sửa Phòng Khám</h5>
    <div class="card-body">
      <form method="post" action="{{ route('clinics.update', $clinic->id) }}">
        @csrf 
        @method('PATCH')

        <div class="form-group">
          <label for="name" class="col-form-label">Tên phòng khám <span class="text-danger">*</span></label>
          <input id="name" type="text" name="name" value="{{ $clinic->name }}" class="form-control">
          @error('name')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="address" class="col-form-label">Địa chỉ <span class="text-danger">*</span></label>
          <textarea class="form-control" id="address" name="address">{{ $clinic->address }}</textarea>
          @error('address')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="phone" class="col-form-label">Số điện thoại <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="phone" value="{{ $clinic->phone }}">
          @error('phone')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="email" class="col-form-label">Email</label>
          <input type="email" class="form-control" name="email" value="{{ $clinic->email }}">
        </div>

        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">Đặt lại</button>
          <button class="btn btn-success" type="submit">Cập nhật</button>
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
        placeholder: "Nhập phòng khám.....",
          tabsize: 2,
          height: 150
      });
    });
</script>
@endpush

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