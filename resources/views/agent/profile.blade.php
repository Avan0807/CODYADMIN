@extends('agent.layouts.master')

@section('title', 'Hồ sơ của tôi')

@section('main-content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Hồ sơ của tôi</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Profile Info Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin cá nhân</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img class="img-profile rounded-circle" 
                             src="{{ $agent->photo ? Storage::url($agent->photo) : asset('backend/img/avatar.png') }}" 
                             style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <h5 class="font-weight-bold">{{ $agent->name }}</h5>
                    <p class="text-muted">{{ $agent->short_bio ?? 'Đại lý bán hàng' }}</p>
                    
                    <div class="row text-center mt-4">
                        <div class="col-4">
                            <div class="text-primary font-weight-bold h4">{{ number_format($agent->total_sales ?? 0) }}đ</div>
                            <div class="small text-muted">Tổng doanh số</div>
                        </div>
                        <div class="col-4">
                            <div class="text-success font-weight-bold h4">{{ number_format($agent->total_commission ?? 0) }}đ</div>
                            <div class="small text-muted">Hoa hồng</div>
                        </div>
                        <div class="col-4">
                            <div class="text-warning font-weight-bold h4">{{ $agent->rating ?? '0.0' }}</div>
                            <div class="small text-muted">Đánh giá</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-xs font-weight-bold text-uppercase mb-1">Trạng thái</span>
                            <div class="h5 mb-0 font-weight-bold">
                                @if($agent->status == 'active')
                                    <span class="badge badge-success">Đang hoạt động</span>
                                @else
                                    <span class="badge badge-secondary">Không hoạt động</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cập nhật thông tin</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('agent.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $agent->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $agent->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $agent->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location">Địa chỉ</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                           id="location" name="location" value="{{ old('location', $agent->location) }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company">Công ty</label>
                                    <input type="text" class="form-control @error('company') is-invalid @enderror" 
                                           id="company" name="company" value="{{ old('company', $agent->company) }}">
                                    @error('company')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="photo">Ảnh đại diện</label>
                                    <input type="file" class="form-control-file @error('photo') is-invalid @enderror" 
                                           id="photo" name="photo" accept="image/*">
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Chỉ chấp nhận file JPG, PNG. Tối đa 2MB.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="bio">Giới thiệu</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                      id="bio" name="bio" rows="4" 
                                      placeholder="Viết vài dòng giới thiệu về bạn...">{{ old('bio', $agent->bio) }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('agent.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật thông tin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Preview ảnh khi chọn file
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('.img-profile').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush