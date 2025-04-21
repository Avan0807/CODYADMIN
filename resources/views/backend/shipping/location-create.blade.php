@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm quy tắc tính phí vận chuyển</h5>
    <div class="card-body">
      <form method="post" action="{{route('shipping.location.store')}}">
        {{csrf_field()}}
        <div class="form-group">
          <label for="shipping_id">Phương thức vận chuyển <span class="text-danger">*</span></label>
          <select name="shipping_id" class="form-control">
              <option value="">--Chọn phương thức vận chuyển--</option>
              @foreach($shippings as $shipping)
              <option value="{{$shipping->id}}">{{$shipping->type}} ({{number_format($shipping->price, 0, ',', '.')}}đ)</option>
              @endforeach
          </select>
          @error('shipping_id')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="from_province_id">Từ tỉnh/thành phố <span class="text-danger">*</span></label>
          <select name="from_province_id" class="form-control">
              <option value="">--Chọn tỉnh/thành phố gửi--</option>
              @foreach($provinces as $province)
              <option value="{{$province->id}}">{{$province->name}}</option>
              @endforeach
          </select>
          @error('from_province_id')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="to_province_id">Đến tỉnh/thành phố <span class="text-danger">*</span></label>
          <select name="to_province_id" class="form-control">
              <option value="">--Chọn tỉnh/thành phố nhận--</option>
              @foreach($provinces as $province)
              <option value="{{$province->id}}">{{$province->name}}</option>
              @endforeach
          </select>
          @error('to_province_id')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="price" class="col-form-label">Phí vận chuyển cơ bản (VNĐ)<span class="text-danger">*</span></label>
          <input id="price" type="number" name="price" placeholder="Nhập phí vận chuyển cơ bản"  value="{{old('price')}}" class="form-control">
          @error('price')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="weight_price" class="col-form-label">Phí theo cân nặng (VNĐ/kg)<span class="text-danger">*</span></label>
          <input id="weight_price" type="number" name="weight_price" placeholder="Nhập phí theo cân nặng"  value="{{old('weight_price')}}" class="form-control">
          @error('weight_price')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">Đặt lại</button>
           <button class="btn btn-success" type="submit">Lưu</button>
        </div>
      </form>
    </div>
</div>

@endsection
