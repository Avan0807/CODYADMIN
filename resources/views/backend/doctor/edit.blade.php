@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Chỉnh Sửa Bác Sĩ</h5>
    <div class="card-body">
      <form method="post" action="{{route('doctor.update',$doctor->id)}}">
        @csrf
        @method('PATCH')
        <div class="form-group">
          <label for="inputName" class="col-form-label">Tên <span class="text-danger">*</span></label>
          <input id="inputName" type="text" name="name" placeholder="Nhập tên bác sĩ" value="{{$doctor->name}}" class="form-control">
          @error('name')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="specialization" class="col-form-label">Chuyên Môn <span class="text-danger">*</span></label>
          <input id="specialization" type="text" name="specialization" placeholder="Nhập chuyên môn" value="{{$doctor->specialization}}" class="form-control">
          @error('specialization')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="experience" class="col-form-label">Kinh Nghiệm (năm) <span class="text-danger">*</span></label>
          <input id="experience" type="number" name="experience" min="0" placeholder="Nhập số năm kinh nghiệm" value="{{$doctor->experience}}" class="form-control">
          @error('experience')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="working_hours" class="col-form-label">Giờ Làm Việc</label>
          <input id="working_hours" type="text" name="working_hours" placeholder="Nhập giờ làm việc" value="{{$doctor->working_hours}}" class="form-control">
          @error('working_hours')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="location" class="col-form-label">Địa Điểm</label>
          <input id="location" type="text" name="location" placeholder="Nhập địa điểm làm việc" value="{{$doctor->location}}" class="form-control">
          @error('location')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="phone" class="col-form-label">Số Điện Thoại</label>
          <input id="phone" type="text" name="phone" placeholder="Nhập số điện thoại" value="{{$doctor->phone}}" class="form-control">
          @error('phone')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="email" class="col-form-label">Email</label>
          <input id="email" type="email" name="email" placeholder="Nhập email" value="{{$doctor->email}}" class="form-control">
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
                <input id="doctor_photo" class="form-control" type="text" name="photo" value="{{$doctor->photo}}">
            </div>
            <div id="holder" style="margin-top:15px;max-height:100px;">
                @if($doctor->photo)
                    <img src="{{$doctor->photo}}" style="height: 5rem;">
                @endif
            </div>
            @error('photo')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>

        <div class="form-group">
          <label for="status" class="col-form-label">Trạng Thái <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
            <option value="active" {{(($doctor->status=='active')? 'selected' : '')}}>Hoạt Động</option>
            <option value="inactive" {{(($doctor->status=='inactive')? 'selected' : '')}}>Không Hoạt Động</option>
          </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group mb-3">
           <button class="btn btn-success" type="submit">Cập Nhật</button>
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
