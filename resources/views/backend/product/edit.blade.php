@extends('backend.layouts.master')

@section('main-content')

<div class="card">
  <h5 class="card-header">Chỉnh Sửa Sản Phẩm</h5>
  <div class="card-body">
    <form method="post" action="{{ route('product.update', $product->id) }}">
      @csrf
      @method('PATCH')

      <div class="form-group">
        <label>Tiêu Đề <span class="text-danger">*</span></label>
        <input type="text" name="title" value="{{ old('title', $product->title) }}" class="form-control">
        @error('title')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Tóm Tắt <span class="text-danger">*</span></label>
        <textarea class="form-control" name="summary">{{ old('summary', $product->summary) }}</textarea>
        @error('summary')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Mô Tả</label>
        <textarea class="form-control" name="description">{{ old('description', $product->description) }}</textarea>
      </div>

      <div class="form-group">
        <label>Sản Phẩm Nổi Bật</label><br>
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}> Có
      </div>

      <div class="form-group">
        <label>Danh Mục <span class="text-danger">*</span></label>
        <select name="cat_id" id="cat_id" class="form-control">
          <option value="">-- Chọn danh mục --</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('cat_id', $product->cat_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
          @endforeach
        </select>
        @error('cat_id')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group {{ $product->child_cat_id ? '' : 'd-none' }}" id="child_cat_div">
        <label>Danh Mục Con</label>
        <select name="child_cat_id" id="child_cat_id" class="form-control"></select>
        @error('child_cat_id')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Giá (VNĐ)</label>
        <input type="number" name="price" value="{{ old('price', $product->price) }}" class="form-control">
        @error('price')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Số Lượng</label>
        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="form-control">
        @error('stock')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Thương Hiệu</label>
        <select name="brand_id" class="form-control">
          <option value="">-- Chọn thương hiệu --</option>
          @foreach($brands as $brand)
            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->title }}</option>
          @endforeach
        </select>
        @error('brand_id')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Tình Trạng</label>
        <select name="condition" class="form-control">
          <option value="default" {{ old('condition', $product->condition) == 'default' ? 'selected' : '' }}>Mặc định</option>
          <option value="new" {{ old('condition', $product->condition) == 'new' ? 'selected' : '' }}>Mới</option>
          <option value="hot" {{ old('condition', $product->condition) == 'hot' ? 'selected' : '' }}>Nổi bật</option>
        </select>
        @error('condition')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Hình Ảnh</label>
        <div class="input-group">
          <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-secondary">Chọn</a>
          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{ old('photo', $product->photo) }}">
        </div>
        <div id="holder" style="margin-top:15px;max-height:100px;"></div>
        @error('photo')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group">
        <label>Trạng Thái</label>
        <select name="status" class="form-control">
          <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
          <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
        </select>
        @error('status')<span class="text-danger">{{ $message }}</span>@enderror
      </div>

      <div class="form-group mb-3">
        <button class="btn btn-success" type="submit">Cập Nhật</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('backend/summernote/summernote.min.css') }}">
@endpush

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{ asset('backend/summernote/summernote.min.js') }}"></script>
<script>
  $('#lfm').filemanager('image');
  $('textarea[name="summary"]').summernote({ height: 100 });
  $('textarea[name="description"]').summernote({ height: 150 });

  const childCatId = '{{ old('child_cat_id', $product->child_cat_id) }}';

  $('#cat_id').change(function() {
    const catId = $(this).val();
    if (!catId) return;

    $.post('/admin/category/' + catId + '/child', {_token: '{{ csrf_token() }}'}, function(res) {
      let html = '<option value="">-- Chọn danh mục con --</option>';
      if (res.status) {
        $('#child_cat_div').removeClass('d-none');
        $.each(res.data, function(id, name) {
          html += `<option value="${id}" ${childCatId == id ? 'selected' : ''}>${name}</option>`;
        });
      } else {
        $('#child_cat_div').addClass('d-none');
      }
      $('#child_cat_id').html(html);
    });
  });

  if (childCatId) $('#cat_id').change();
</script>
@endpush
