@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm bài viết</h5>
    <div class="card-body">
      <form method="post" action="{{ route('post.store') }}">
        @csrf
        <div class="form-group">
          <label for="inputTitle">Tiêu đề <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Nhập tiêu đề">
          @error('title') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
          <label>Trích dẫn</label>
          <textarea name="quote" class="form-control" id="quote">{{ old('quote') }}</textarea>
          @error('quote') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
          <label>Tóm tắt <span class="text-danger">*</span></label>
          <textarea name="summary" class="form-control" id="summary">{{ old('summary') }}</textarea>
          @error('summary') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
          <label>Mô tả</label>
          <textarea name="description" class="form-control" id="description">{{ old('description') }}</textarea>
          @error('description') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>Danh mục <span class="text-danger">*</span></label>
            <select name="post_cat_id" class="form-control">
              <option value="">-- Chọn danh mục --</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}"
                  {{ old('post_cat_id', $post->post_cat_id ?? '') == $cat->id ? 'selected' : '' }}>
                  {{ $cat->name }}
                </option>
              @endforeach
            </select>
            @error('post_cat_id') <span class="text-danger">{{ $message }}</span> @enderror
          </div>
        <div class="form-group">
          <label>Thẻ</label>
          <select name="tags[]" class="form-control selectpicker" multiple data-live-search="true">
            <option value="">-- Chọn thẻ --</option>
            @foreach($tags as $tag)
              <option value="{{ $tag->title }}" {{ collect(old('tags'))->contains($tag->title) ? 'selected' : '' }}>
                {{ $tag->title }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Tác giả</label>
          <select name="added_by" class="form-control">
            <option value="">-- Chọn tác giả --</option>
            @foreach($users as $user)
              <option value="{{ $user->id }}" {{ old('added_by') == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Ảnh đại diện <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-btn">
              <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                <i class="fa fa-picture-o"></i> Chọn
              </a>
            </span>
            <input id="thumbnail" class="form-control" type="text" name="photo" value="{{ old('photo') }}">
          </div>
          <div id="holder" style="margin-top:15px;max-height:100px;"></div>
          @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
          <label>Trạng thái</label>
          <select name="status" class="form-control">
            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
          </select>
          @error('status') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group text-right">
          <button type="reset" class="btn btn-warning">Đặt lại</button>
          <button type="submit" class="btn btn-success">Thêm bài viết</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
@endpush

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

<script>
  $('#lfm').filemanager('image');

  $(document).ready(function () {
    $('#summary, #description, #quote').summernote({
      tabsize: 2,
      height: 120
    });
    $('.selectpicker').selectpicker();
  });
</script>
@endpush
