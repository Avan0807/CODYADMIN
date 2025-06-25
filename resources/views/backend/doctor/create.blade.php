@extends('backend.layouts.master')

@section('main-content')
<div class="card">
    <h5 class="card-header">Thêm Bác Sĩ</h5>
    <div class="card-body">
      <form method="post" action="{{ route('doctor.store') }}">
        @csrf

        {{-- Tên --}}
        <div class="form-group">
          <label for="name">Tên Bác Sĩ <span class="text-danger">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Nhập tên bác sĩ">
          @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

    {{-- Chuyên khoa --}}
    <div class="form-group">
        <label for="specialization">Chuyên Khoa <span class="text-danger">*</span></label>
        <select name="specialization" id="specialization" class="form-control">
            <option value="">-- Chọn chuyên khoa --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}"
                    {{ old('specialization') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('specialization') <span class="text-danger">{{ $message }}</span> @enderror
    </div>


        {{-- Nơi làm việc --}}
        <div class="form-group">
          <label for="workplace">Nơi làm việc</label>
          <input type="text" name="workplace" value="{{ old('workplace') }}" class="form-control" placeholder="VD: Bệnh viện Bạch Mai">
        </div>

        {{-- Kinh nghiệm --}}
        <div class="form-group">
          <label for="experience">Kinh nghiệm (năm) <span class="text-danger">*</span></label>
          <input type="number" name="experience" min="0" value="{{ old('experience') }}" class="form-control">
          @error('experience') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        {{-- Giờ làm việc --}}
        <div class="form-group">
          <label for="working_hours">Giờ làm việc</label>
          <input type="text" name="working_hours" value="{{ old('working_hours') }}" class="form-control">
        </div>

        {{-- Địa điểm --}}
        <div class="form-group">
          <label for="location">Địa điểm</label>
          <input type="text" name="location" value="{{ old('location') }}" class="form-control">
        </div>

        {{-- Dịch vụ --}}
        <div class="form-group">
          <label for="services">Dịch vụ</label>
          <textarea name="services" rows="3" class="form-control">{{ old('services') }}</textarea>
        </div>

        {{-- SĐT --}}
        <div class="form-group">
          <label for="phone">Số điện thoại</label>
          <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
          @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        {{-- Email --}}
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="form-control">
          @error('email') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        {{-- Ảnh --}}
        <div class="form-group">
            <label for="photo">Ảnh đại diện</label>
            <div class="input-group">
                <a id="lfm" data-input="doctor_photo" data-preview="holder" class="btn btn-primary text-white">
                    <i class="fas fa-image"></i> Chọn
                </a>
                <input id="doctor_photo" class="form-control" type="text" name="photo" value="{{ old('photo') }}">
            </div>
            <div id="holder" style="margin-top:15px;max-height:100px;"></div>
            @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        {{-- Mật khẩu --}}
        <div class="form-group">
          <label for="password">Mật khẩu <span class="text-danger">*</span></label>
          <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu">
          @error('password') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        {{-- Trạng thái --}}
        <div class="form-group">
          <label for="status">Trạng thái <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
              <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
          </select>
        </div>

        {{-- Các trường bổ sung --}}
        <div class="form-group">
          <label for="rating">Đánh giá (1 - 5)</label>
          <input type="number" name="rating" step="0.1" max="5" value="{{ old('rating') }}" class="form-control">
        </div>

        <div class="form-group">
          <label for="consultation_fee">Phí tư vấn</label>
          <input type="number" name="consultation_fee" step="1000" value="{{ old('consultation_fee') }}" class="form-control">
        </div>

        <div class="form-group">
          <label for="short_bio">Giới thiệu ngắn</label>
          <input type="text" name="short_bio" value="{{ old('short_bio') }}" class="form-control">
        </div>

        <div class="form-group">
          <label for="bio">Giới thiệu chi tiết</label>
          <textarea name="bio" rows="3" class="form-control">{{ old('bio') }}</textarea>
        </div>

        <div class="form-group">
          <label for="points">Điểm tích lũy</label>
          <input type="number" name="points" value="{{ old('points') }}" class="form-control">
        </div>

        <div class="form-group">
          <label for="total_commission">Hoa hồng</label>
          <input type="number" name="total_commission" step="1000" value="{{ old('total_commission') }}" class="form-control">
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
