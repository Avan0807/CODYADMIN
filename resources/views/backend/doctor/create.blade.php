@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Thêm Bác Sĩ</h5>
    <div class="card-body">
      <form method="post" action="{{route('doctor.store')}}">
        {{csrf_field()}}

        <div class="form-group">
          <label for="name" class="col-form-label">Tên Bác Sĩ <span class="text-danger">*</span></label>
          <input id="name" type="text" name="name" placeholder="Nhập tên bác sĩ" value="{{old('name')}}" class="form-control">
          @error('name')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="experience" class="col-form-label">Kinh Nghiệm (năm) <span class="text-danger">*</span></label>
          <input id="experience" type="number" name="experience" min="0" placeholder="Nhập số năm kinh nghiệm" value="{{old('experience')}}" class="form-control">
          @error('experience')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="working_hours" class="col-form-label">Giờ Làm Việc</label>
          <input id="working_hours" type="text" name="working_hours" placeholder="Nhập giờ làm việc" value="{{old('working_hours')}}" class="form-control">
          @error('working_hours')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="location" class="col-form-label">Địa Điểm</label>
          <input id="location" type="text" name="location" placeholder="Nhập địa điểm làm việc" value="{{old('location')}}" class="form-control">
          @error('location')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="phone" class="col-form-label">Số Điện Thoại</label>
          <input id="phone" type="text" name="phone" placeholder="Nhập số điện thoại" value="{{old('phone')}}" class="form-control">
          @error('phone')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="email" class="col-form-label">Email</label>
          <input id="email" type="email" name="email" placeholder="Nhập email" value="{{old('email')}}" class="form-control">
          @error('email')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
            <label for="inputPhoto" class="col-form-label">Hình Ảnh <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-btn">
                    <a id="lfm" data-input="doctor_photo" data-preview="holder" class="btn btn-primary text-white">
                        <i class="fas fa-image"></i> Chọn
                    </a>
                </span>
                <input id="doctor_photo" class="form-control" type="text" name="photo" value="{{old('photo')}}">
            </div>
            <div id="holder" style="margin-top:15px;max-height:100px;"></div>
            @error('photo')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>

        <div class="form-group">
          <label for="password" class="col-form-label">Mật Khẩu <span class="text-danger">*</span></label>
          <input id="password" type="password" name="password" placeholder="Nhập mật khẩu" class="form-control">
          @error('password')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>


        <div class="form-group">
          <label for="status" class="col-form-label">Trạng Thái <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active" {{old('status')=='active' ? 'selected' : ''}}>Hoạt Động</option>
              <option value="inactive" {{old('status')=='inactive' ? 'selected' : ''}}>Không Hoạt Động</option>
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

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script>
    $('#lfm').filemanager('image');
</script>
@endpush
