@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm người dùng</h5>
    <div class="card-body">
      <form method="post" action="{{route('users.store')}}">
        {{csrf_field()}}

        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Tên</label>
          <input id="inputTitle" type="text" name="name" placeholder="Nhập tên" value="{{old('name')}}" class="form-control">
          @error('name')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
            <label for="inputEmail" class="col-form-label">Email</label>
            <input id="inputEmail" type="email" name="email" placeholder="Nhập email" value="{{old('email')}}" class="form-control">
            @error('email')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="inputPhone" class="col-form-label">Số điện thoại</label>
            <input id="inputPhone" type="text" name="phone" placeholder="Nhập số điện thoại" value="{{old('phone')}}" class="form-control">
            @error('phone')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="inputPassword" class="col-form-label">Mật khẩu</label>
            <input id="inputPassword" type="password" name="password" placeholder="Nhập mật khẩu" class="form-control">
            @error('password')
            <span class="text-danger">{{$message}}</span>
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
          <img id="holder" style="margin-top:15px;max-height:100px;">
          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
            <label for="role" class="col-form-label">Vai trò</label>
            <select name="role" class="form-control">
                <option value="">-----Chọn vai trò-----</option>
                <option value="admin">Quản trị viên</option>
                <option value="user">Người dùng</option>
            </select>
            @error('role')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="status" class="col-form-label">Trạng thái</label>
            <select name="status" class="form-control">
                <option value="active">Hoạt động</option>
                <option value="inactive">Không hoạt động</option>
            </select>
            @error('status')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>

        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">Cài lại</button>
          <button class="btn btn-success" type="submit">Gửi</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script>
    $('#lfm').filemanager('image');
</script>
@endpush
