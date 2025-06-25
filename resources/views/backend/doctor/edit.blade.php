@extends('backend.layouts.master')

@section('main-content')
<div class="card">
    <h5 class="card-header">Chỉnh Sửa Bác Sĩ</h5>
    <div class="card-body">
        <form method="post" action="{{route('doctor.update',$doctor->id)}}">
            @csrf
            @method('PATCH')

            {{-- Tên bác sĩ --}}
            <div class="form-group">
                <label for="name">Tên Bác Sĩ <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $doctor->name) }}" class="form-control">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Chuyên khoa (dùng category) --}}
            <div class="form-group">
                <label for="specialization">Chuyên Khoa <span class="text-danger">*</span></label>
                <select name="specialization" class="form-control">
                    <option value="">-- Chọn chuyên khoa --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ $doctor->specializations->first()?->id == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('specialization') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            {{-- Nơi làm việc --}}
            <div class="form-group">
                <label for="workplace">Nơi Làm Việc</label>
                <input type="text" name="workplace" value="{{ old('workplace', $doctor->workplace) }}" class="form-control">
            </div>

            {{-- Kinh nghiệm --}}
            <div class="form-group">
                <label for="experience">Kinh Nghiệm (năm)</label>
                <input type="number" name="experience" value="{{ old('experience', $doctor->experience) }}" class="form-control">
            </div>

            {{-- Giờ làm việc --}}
            <div class="form-group">
                <label for="working_hours">Giờ Làm Việc</label>
                <input type="text" name="working_hours" value="{{ old('working_hours', $doctor->working_hours) }}" class="form-control">
            </div>

            {{-- Địa điểm --}}
            <div class="form-group">
                <label for="location">Địa Điểm</label>
                <input type="text" name="location" value="{{ old('location', $doctor->location) }}" class="form-control">
            </div>

            {{-- Dịch vụ --}}
            <div class="form-group">
                <label for="services">Dịch Vụ</label>
                <textarea name="services" class="form-control" rows="3">{{ old('services', $doctor->services) }}</textarea>
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" value="{{ old('email', $doctor->email) }}" class="form-control">
            </div>

            {{-- Số điện thoại --}}
            <div class="form-group">
                <label for="phone">Số Điện Thoại</label>
                <input type="text" name="phone" value="{{ old('phone', $doctor->phone) }}" class="form-control">
            </div>

            {{-- Hình ảnh --}}
            <div class="form-group">
                <label for="photo">Hình Ảnh</label>
                <div class="input-group">
                    <a id="lfm" data-input="doctor_photo" data-preview="holder" class="btn btn-primary text-white">
                        <i class="fas fa-image"></i> Chọn
                    </a>
                    <input id="doctor_photo" class="form-control" type="text" name="photo" value="{{ old('photo', $doctor->photo) }}">
                </div>
                <div id="holder" style="margin-top:15px;max-height:100px;">
                    @if($doctor->photo)
                        <img src="{{ asset('storage/'.$doctor->photo) }}" style="height: 5rem;">
                    @endif
                </div>
            </div>

            {{-- Mật khẩu --}}
            <div class="form-group">
                <label for="password">Mật Khẩu (để trống nếu không đổi)</label>
                <input type="password" name="password" class="form-control">
            </div>

            {{-- Trạng thái --}}
            <div class="form-group">
                <label for="status">Trạng Thái</label>
                <select name="status" class="form-control">
                    <option value="active" {{ $doctor->status == 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="inactive" {{ $doctor->status == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                </select>
            </div>

            {{-- Điểm thưởng --}}
            <div class="form-group">
                <label for="points">Điểm Thưởng</label>
                <input type="number" name="points" value="{{ old('points', $doctor->points) }}" class="form-control">
            </div>

            {{-- Tổng hoa hồng --}}
            <div class="form-group">
                <label for="total_commission">Tổng Hoa Hồng</label>
                <input type="number" name="total_commission" value="{{ old('total_commission', $doctor->total_commission) }}" class="form-control">
            </div>

            {{-- Giá khám --}}
            <div class="form-group">
                <label for="consultation_fee">Phí Tư Vấn</label>
                <input type="number" name="consultation_fee" value="{{ old('consultation_fee', $doctor->consultation_fee) }}" class="form-control">
            </div>

            {{-- Mô tả ngắn --}}
            <div class="form-group">
                <label for="short_bio">Tiểu sử ngắn</label>
                <textarea name="short_bio" class="form-control" rows="2">{{ old('short_bio', $doctor->short_bio) }}</textarea>
            </div>

            {{-- Mô tả dài --}}
            <div class="form-group">
                <label for="bio">Tiểu sử chi tiết</label>
                <textarea name="bio" class="form-control" rows="5">{{ old('bio', $doctor->bio) }}</textarea>
            </div>

            {{-- Rating (ẩn nếu không dùng form input) --}}
            <input type="hidden" name="rating" value="{{ old('rating', $doctor->rating) }}">

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-success">Cập Nhật</button>
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
