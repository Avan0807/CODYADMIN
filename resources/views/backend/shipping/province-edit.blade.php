@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Sửa tỉnh/thành phố</h5>
    <div class="card-body">
      <form method="post" action="{{route('shipping.province.update', $province->id)}}">
        @csrf
        @method('PATCH')
        <div class="form-group">
          <label for="inputName" class="col-form-label">Tên tỉnh/thành phố <span class="text-danger">*</span></label>
          <input id="inputName" type="text" name="name" placeholder="Nhập tên tỉnh/thành phố"  value="{{$province->name}}" class="form-control">
          @error('name')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="region_id">Vùng miền <span class="text-danger">*</span></label>
          <select name="region_id" class="form-control">
              <option value="">--Chọn vùng--</option>
              <option value="1" {{(($province->region_id==1) ? 'selected' : '')}}>Miền Bắc</option>
              <option value="2" {{(($province->region_id==2) ? 'selected' : '')}}>Miền Trung</option>
              <option value="3" {{(($province->region_id==3) ? 'selected' : '')}}>Miền Nam</option>
          </select>
          @error('region_id')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group mb-3">
           <button class="btn btn-success" type="submit">Cập nhật</button>
        </div>
      </form>
    </div>
</div>

@endsection
