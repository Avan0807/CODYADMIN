@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm tỉnh/thành phố</h5>
    <div class="card-body">
      <form method="post" action="{{route('shipping.province.store')}}">
        {{csrf_field()}}
        <div class="form-group">
          <label for="inputName" class="col-form-label">Tên tỉnh/thành phố <span class="text-danger">*</span></label>
          <input id="inputName" type="text" name="name" placeholder="Nhập tên tỉnh/thành phố"  value="{{old('name')}}" class="form-control">
          @error('name')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="region_id">Vùng miền <span class="text-danger">*</span></label>
          <select name="region_id" class="form-control">
              <option value="">--Chọn vùng--</option>
              <option value="1">Miền Bắc</option>
              <option value="2">Miền Trung</option>
              <option value="3">Miền Nam</option>
          </select>
          @error('region_id')
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
