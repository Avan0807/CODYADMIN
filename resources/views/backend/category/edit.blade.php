@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Chỉnh sửa danh mục</h5>
    <div class="card-body">
      <form method="post" action="{{route('category.update', $category->id)}}">
        @csrf
        @method('PATCH')

        <!-- Tiêu đề -->
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Tiêu đề <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="name" placeholder="Nhập tiêu đề" value="{{$category->name}}" class="form-control">
          @error('name')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <!-- Tóm tắt -->
        <div class="form-group">
          <label for="summary" class="col-form-label">Tóm tắt</label>
          <textarea class="form-control" id="summary" name="summary">{{$category->summary}}</textarea>
          @error('summary')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <!-- Chọn danh mục cha -->
        <div class="form-group">
          <label for="parent_id">Danh mục cha (nếu có)</label>
          <select name="parent_id" class="form-control">
              <option value="">-- Không chọn --</option>
              @foreach($parent_cats as $parent)
                  <option value="{{$parent->id}}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>
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
              <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$category->photo}}">
          </div>
          <div id="holder" style="margin-top:15px;max-height:100px;">
            @if($category->photo)
              <img src="{{$category->photo}}" alt="ảnh" class="img-fluid" style="max-height:100px;">
            @endif
          </div>
          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <!-- Trạng thái -->
        <div class="form-group">
          <label for="status" class="col-form-label">Trạng thái <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active" {{ $category->status == 'active' ? 'selected' : '' }}>Hoạt động</option>
              <option value="inactive" {{ $category->status == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
          </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <!-- Nút -->
        <div class="form-group mb-3">
           <button class="btn btn-success" type="submit">Cập nhật</button>
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
        height: 150
      });
    });
</script>
@endpush
