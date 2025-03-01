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
          <label for="specialization" class="col-form-label">Chuyên Môn <span class="text-danger">*</span></label>
          <input id="specialization" type="text" name="specialization" placeholder="Nhập chuyên môn" value="{{old('specialization')}}" class="form-control">
          @error('specialization')
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
          <label for="photo" class="col-form-label">Hình Ảnh</label>
          <input id="photo" type="text" name="photo" value="{{old('photo')}}" class="form-control">
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
