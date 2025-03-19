@extends('backend.layouts.master')

@section('title', 'CODY || Thêm Tin Tức Công Ty')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm Tin Tức</h5>
    <div class="card-body">
      <form method="post" action="{{ route('company_news.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
          <label for="title" class="col-form-label">Tiêu đề <span class="text-danger">*</span></label>
          <input id="title" type="text" name="title" placeholder="Nhập tiêu đề" value="{{ old('title') }}" class="form-control">
          @error('title')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="contenten" class="col-form-label">Nội dung <span class="text-danger">*</span></label>
          <textarea class="form-control" id="content" name="content">{{ old('content') }}</textarea>
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
            <input id="thumbnail" class="form-control" type="text" name="photo" value="{{old('photo')}}">
          </div>
          <div id="holder" style="margin-top:15px;max-height:100px;"></div>

            @error('photo')
            <span class="text-danger">{{$message}}</span>
            @enderror
          </div>

        <div class="form-group">
          <label for="published_at" class="col-form-label">Ngày xuất bản</label>
          <input type="date" class="form-control" name="published_at" value="{{ old('published_at') }}">
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
<link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/lfm.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('vendor/laravel-filemanager/js/stand-alone-button.js') }}"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>

<script>
    $('#lfm').filemanager('image');

    $(document).ready(function() {
      $('#contenten').summernote({
        placeholder: "Nhập tin tức...",
        tabsize: 2,
        height: 150
      });
    });
</script>
@endpush
