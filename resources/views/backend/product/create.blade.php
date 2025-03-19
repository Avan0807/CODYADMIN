@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm Sản Phẩm</h5>
    <div class="card-body">
      <form method="post" action="{{route('product.store')}}">
        {{csrf_field()}}
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Tiêu Đề <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="title" placeholder="Nhập tiêu đề"  value="{{old('title')}}" class="form-control">
          @error('title')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="summary" class="col-form-label">Tóm Tắt <span class="text-danger">*</span></label>
          <textarea class="form-control" id="summary" name="summary">{{old('summary')}}</textarea>
          @error('summary')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="description" class="col-form-label">Mô Tả</label>
          <textarea class="form-control" id="description" name="description">{{old('description')}}</textarea>
          @error('description')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>


        <div class="form-group">
          <label for="is_featured">Sản Phẩm Nổi Bật</label><br>
          <input type="checkbox" name='is_featured' id='is_featured' value='1' checked> Có
        </div>

        <div class="form-group">
          <label for="cat_id">Danh Mục <span class="text-danger">*</span></label>
          <select name="cat_id" id="cat_id" class="form-control">
              <option value="">--Chọn danh mục--</option>
              @foreach($categories as $key=>$cat_data)
                  <option value='{{$cat_data->id}}'>{{$cat_data->title}}</option>
              @endforeach
          </select>
          @error('cat_id')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group d-none" id="child_cat_div">
          <label for="child_cat_id">Danh Mục Con<span class="text-danger">*</span></label>
          <select name="child_cat_id" id="child_cat_id" class="form-control">
              <option value="">--Chọn danh mục--</option>
          </select>
          @error('child_cat_id')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="price" class="col-form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
          <input id="price" type="number" name="price" placeholder="Nhập giá"  value="{{old('price')}}" class="form-control">
          @error('price')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

 {{--       <div class="form-group">
          <label for="discount" class="col-form-label">Giảm Giá (%)</label>
            <input id="discount"
              type="number"
              name="discount"
              min="0"
              max="100"
              placeholder="Nhập phần trăm giảm giá"
              value="{{ old('discount', 0) }}"
              class="form-control"
              required
              oninput="validateDiscount(this)">
          @error('discount')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
    --}}

{{--
        <div class="form-group">
          <label for="size">Đơn Vị</label>
          <select name="size[]" class="form-control selectpicker" multiple data-live-search="true">
            <option value="">--Chọn đơn vị--</option>
              <option value="Hộp">Hộp</option>
              <option value="Viên">Viên</option>
              <option value="Chiếc">Chiếc</option>
          </select>
          @error('size[]')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
--}}
        <div class="form-group">
          <label for="brand_id">Thương Hiệu<span class="text-danger">*</span></label>
          <select name="brand_id" class="form-control">
              <option value="">--Chọn thương hiệu--</option>
             @foreach($brands as $brand)
              <option value="{{$brand->id}}">{{$brand->title}}</option>
             @endforeach
          </select>
          @error('brand_id')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="condition">Tình Trạng<span class="text-danger">*</span></label>
          <select name="condition" class="form-control">
              <option value="">--Chọn tình trạng--</option>
              <option value="default">Mặc Định</option>
              <option value="new">Mới</option>
              <option value="hot">Nổi Bật</option>
          </select>
          @error('condition')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="stock">Số Lượng <span class="text-danger">*</span></label>
          <input id="quantity" type="number" name="stock" min="0" placeholder="Nhập số lượng"  value="{{old('stock')}}" class="form-control">
          @error('stock')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="inputPhoto" class="col-form-label">Hình Ảnh <span class="text-danger">*</span></label>
          <div class="input-group">
              <span class="input-group-btn">
                  <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-secondary text-white">
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
          <label for="status" class="col-form-label">Trạng Thái <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active">Hoạt Động</option>
              <option value="inactive">Không Hoạt Động</option>
          </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
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
<link rel="stylesheet" href="{{asset('backend/summernote/summernote.min.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
@endpush
@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

<script>
    $('#lfm').filemanager('image');

    $(document).ready(function() {
      $('#summary').summernote({
        placeholder: "Viết mô tả ngắn.....",
          tabsize: 2,
          height: 100
      });
    });

    $(document).ready(function() {
      $('#description').summernote({
        placeholder: "Viết mô tả chi tiết.....",
          tabsize: 2,
          height: 150
      });
    });

</script>

<script>
  $('#cat_id').change(function(){
    var cat_id=$(this).val();
    if(cat_id !=null){
      $.ajax({
        url:"/admin/category/"+cat_id+"/child",
        data:{
          _token:"{{csrf_token()}}",
          id:cat_id
        },
        type:"POST",
        success:function(response){
          if(typeof(response) !='object'){
            response=$.parseJSON(response)
          }
          var html_option="<option value=''>----Chọn danh mục con----</option>"
          if(response.status){
            var data=response.data;
            if(response.data){
              $('#child_cat_div').removeClass('d-none');
              $.each(data,function(id,title){
                html_option +="<option value='"+id+"'>"+title+"</option>"
              });
            }
          }
          else{
            $('#child_cat_div').addClass('d-none');
          }
          $('#child_cat_id').html(html_option);
        }
      });
    }
  })
</script>
<script>
    function validateDiscount(input) {
        if (input.value > 100) {
            input.value = 100;
        } else if (input.value < 0) {
            input.value = 0;
        }
    }
</script>

@endpush
