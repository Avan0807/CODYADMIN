@extends('backend.layouts.master')

@section('title','CODY || Chỉnh sửa thông báo chiến dịch')

@section('main-content')

<div class="card">
    <h5 class="card-header">Chỉnh sửa Thông báo Chiến dịch</h5>
    <div class="card-body">
      <form method="post" action="{{ route('campaign_notifications.update', $campaign_notification->id) }}">
        @csrf 
        @method('PATCH')

        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Tiêu đề <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="title" placeholder="Nhập tiêu đề" value="{{ $campaign_notification->title }}" class="form-control">
          @error('title')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="message" class="col-form-label">Nội dung <span class="text-danger">*</span></label>
          <textarea class="form-control" id="message" name="message">{{ $campaign_notification->message }}</textarea>
          @error('message')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="target_audience" class="col-form-label">Đối tượng nhận <span class="text-danger">*</span></label>
          <select name="target_audience" class="form-control">
              <option value="all" {{ $campaign_notification->target_audience == 'all' ? 'selected' : '' }}>Tất cả</option>
              <option value="doctors" {{ $campaign_notification->target_audience == 'doctors' ? 'selected' : '' }}>Bác sĩ</option>
              <option value="patients" {{ $campaign_notification->target_audience == 'patients' ? 'selected' : '' }}>Bệnh nhân</option>
          </select>
          @error('target_audience')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group mb-3">
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
        placeholder: "Nhập nội dung thông báo.....",
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