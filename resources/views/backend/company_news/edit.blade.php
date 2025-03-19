@extends('backend.layouts.master')

@section('title', 'CODY || Chỉnh sửa Tin Tức Công Ty')

@section('main-content')

<div class="card">
    <h5 class="card-header">Chỉnh Sửa Tin Tức</h5>
    <div class="card-body">
      <form method="post" action="{{ route('company_news.update', $news->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="title" class="col-form-label">Tiêu đề <span class="text-danger">*</span></label>
            <input id="title" type="text" name="title" value="{{ $news->title }}" class="form-control">
            @error('title')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="content" class="col-form-label">Nội dung <span class="text-danger">*</span></label>
            <textarea class="form-control" id="message" name="content">{{ $news->content }}</textarea>
            @error('content')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="inputPhoto" class="col-form-label">Ảnh</label>
            <div class="input-group">
                <span class="input-group-btn">
                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                        <i class="fa fa-picture-o"></i> Chọn
                    </a>
                </span>
                <input id="thumbnail" class="form-control" type="text" name="image" value="{{ old('image', $news->image) }}">
            </div>
            <div id="holder" style="margin-top:15px;max-height:100px;">
                @if($news->image)
                    <img src="{{ asset('storage/'.$news->image) }}" class="img-fluid mt-2" style="max-width:80px">
                @endif
            </div>
            @error('image')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="published_at" class="col-form-label">Ngày xuất bản</label>
            <input type="date" class="form-control" name="published_at" value="{{ old('published_at', \Carbon\Carbon::parse($news->published_at)->format('Y-m-d')) }}">
        </div>



        <div class="form-group">
          <button type="reset" class="btn btn-warning">Đặt lại</button>
          <button class="btn btn-success" type="submit">Cập nhật</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/laravel-filemanager/css/lfm.css">
@endpush

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
<script>
    // Khởi tạo filemanager cho ảnh
    $('#lfm').filemanager('image');

    // Khởi tạo Summernote cho content
    $(document).ready(function() {
      $('#message').summernote({
        placeholder: "Nhập tin tức.....",
        tabsize: 2,
        height: 150
      });
    });
</script>
@endpush
