@extends('backend.layouts.master')

@section('main-content')

<div class="card">
  <h5 class="card-header">Thêm Sản Phẩm</h5>
  <div class="card-body">
    <form method="post" action="{{ route('product.store') }}">
      @csrf

      <div class="form-group">
        <label>Tiêu Đề <span class="text-danger">*</span></label>
        <input type="text" name="title" value="{{ old('title') }}" class="form-control">
        @error('title')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Tóm Tắt <span class="text-danger">*</span></label>
        <textarea class="form-control" name="summary">{{ old('summary') }}</textarea>
        @error('summary')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Mô Tả</label>
        <textarea class="form-control" name="description">{{ old('description') }}</textarea>
      </div>

      <div class="form-group">
        <label>Sản Phẩm Nổi Bật</label><br>
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}> Có
      </div>

    <div class="form-group">
        <label>Danh Mục <span class="text-danger">*</span></label>
        <select name="categories[]" class="form-control select2" multiple="multiple">
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ (is_array(old('categories')) && in_array($cat->id, old('categories'))) ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('categories')<span class="text-danger">{{ $message }}</span>@enderror
        <small class="text-info">Bạn có thể chọn nhiều danh mục bằng cách giữ phím Ctrl và click chuột</small>
    </div>

      <div class="form-group">
        <label>Giá (VNĐ)</label>
        <input type="number" name="price" value="{{ old('price') }}" class="form-control">
        @error('price')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Số Lượng</label>
        <input type="number" name="stock" value="{{ old('stock', 0) }}" class="form-control">
        @error('stock')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Thương Hiệu</label>
        <select name="brand_id" class="form-control">
          <option value="">-- Chọn thương hiệu --</option>
          @foreach($brands as $brand)
            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->title }}</option>
          @endforeach
        </select>
        @error('brand_id')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Tình Trạng</label>
        <select name="condition" class="form-control">
          <option value="default" {{ old('condition') == 'default' ? 'selected' : '' }}>Mặc định</option>
          <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>Mới</option>
          <option value="hot" {{ old('condition') == 'hot' ? 'selected' : '' }}>Nổi bật</option>
        </select>
        @error('condition')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Hình Ảnh</label>
        <div class="input-group">
          <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-secondary">Chọn</a>
          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{ old('photo') }}">
        </div>
        <div id="holder" style="margin-top:15px;max-height:100px;"></div>
        @error('photo')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Trạng Thái</label>
        <select name="status" class="form-control">
          <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
          <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
        </select>
        @error('status')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group mb-3">
        <button type="reset" class="btn btn-warning">Đặt Lại</button>
        <button class="btn btn-success" type="submit">Thêm</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Chọn một hoặc nhiều danh mục",
            allowClear: true
        });
    });
</script>
<script>
  $('#lfm').filemanager('image');
  $('textarea[name="summary"]').summernote({ height: 100 });
  $('textarea[name="description"]').summernote({ height: 150 });

  const oldChildCat = '{{ old('child_cat_id') }}';

  $('#cat_id').change(function() {
    const catId = $(this).val();
    if (!catId) return;

    $.post('/admin/category/' + catId + '/child', {_token: '{{ csrf_token() }}'}, function(res) {
      let html = '<option value="">-- Chọn danh mục con --</option>';
      if (res.status) {
        $('#child_cat_div').removeClass('d-none');
        $.each(res.data, function(id, name) {
          html += `<option value="${id}" ${oldChildCat == id ? 'selected' : ''}>${name}</option>`;
        });
      } else {
        $('#child_cat_div').addClass('d-none');
      }
      $('#child_cat_id').html(html);
    });
  });

  if (oldChildCat) $('#cat_id').change();
</script>
@endpush
