@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm danh mục</h5>
    <div class="card-body">
      <form method="post" action="{{route('category.store')}}">
        @csrf

        <!-- Tiêu đề -->
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Tiêu đề <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="name" placeholder="Nhập tiêu đề" value="{{old('name')}}" class="form-control">
          @error('name')
            <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <!-- Tóm tắt -->
        <div class="form-group">
          <label for="summary" class="col-form-label">Tóm tắt</label>
          <textarea class="form-control" id="summary" name="summary">{{old('summary')}}</textarea>
          @error('summary')
            <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <!-- Danh mục cha (nếu có) -->
        <div class="form-group">
          <label for="parent_id">Danh mục cha (nếu có)</label>
          <select name="parent_id" class="form-control">
              <option value="">-- Không chọn --</option>
              @foreach($parent_cats as $parent)
                  <option value="{{$parent->id}}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                      {{$parent->name}}
                  </option>
              @endforeach
          </select>
          @error('parent_id')
            <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <!-- Ảnh -->
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

        <!-- Trạng thái -->
        <div class="form-group">
          <label for="status" class="col-form-label">Trạng thái <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
              <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
          </select>
          @error('status')
            <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <!-- Submit -->
        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">Đặt lại</button>
          <button class="btn btn-success" type="submit">Thêm</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{asset('backend/summernote/summernote.min.css')}}">
@endpush

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>
<script>
    $('#lfm').filemanager('image');
    $(document).ready(function() {
      $('#summary').summernote({
        placeholder: "Nhập tóm tắt ngắn...",
        tabsize: 2,
        height: 120
      });
    });
</script>
@endpush
