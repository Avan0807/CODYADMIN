@extends('backend.layouts.master')

@section('title','CODY || Thêm thông báo chiến dịch')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm Thông báo Chiến Dịch</h5>
    <div class="card-body">
      <form method="post" action="{{ route('campaign_notifications.store') }}">
        @csrf

        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Tiêu đề <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="title" placeholder="Nhập tiêu đề" value="{{ old('title') }}" class="form-control">
          @error('title')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="message" class="col-form-label">Nội dung <span class="text-danger">*</span></label>
          <textarea class="form-control" id="message" name="message">{{ old('message') }}</textarea>
          @error('message')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="target_audience" class="col-form-label">Đối tượng nhận <span class="text-danger">*</span></label>
          <select name="target_audience" class="form-control">
            <option value="doctor" {{ old('target_audience') == 'doctor' ? 'selected' : '' }}>Bác sĩ</option>
            <option value="user" {{ old('target_audience') == 'user' ? 'selected' : '' }}>Người dùng</option>
            <option value="both" {{ old('target_audience') == 'both' ? 'selected' : '' }}>Cả hai</option>
          </select>
          @error('target_audience')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group mb-3">
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
        placeholder: "Nhập nội dung thông báo...",
          tabsize: 2,
          height: 150
      });
    });
</script>
@endpush
