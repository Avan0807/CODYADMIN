@extends('backend.layouts.master')

@section('main-content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thêm Đại lý mới</h6>
    </div>
    <div class="card-body">
        <form method="post" action="{{ route('agent.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <!-- Thông tin cơ bản -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin cơ bản</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Họ và tên <span class="text-danger">*</span></label>
                                <input id="name" name="name" type="text" placeholder="Nhập họ và tên" value="{{ old('name') }}" class="form-control">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input id="email" name="email" type="email" placeholder="Nhập email" value="{{ old('email') }}" class="form-control">
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                                <input id="phone" name="phone" type="text" placeholder="Nhập số điện thoại" value="{{ old('phone') }}" class="form-control">
                                @error('phone')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="location">Địa chỉ <span class="text-danger">*</span></label>
                                <input id="location" name="location" type="text" placeholder="Nhập địa chỉ" value="{{ old('location') }}" class="form-control">
                                @error('location')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="photo">Ảnh đại diện</label>
                                <input id="photo" name="photo" type="file" class="form-control-file" accept="image/*">
                                @error('photo')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin công ty -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin công ty</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="company">Tên công ty</label>
                                <input id="company" name="company" type="text" placeholder="Nhập tên công ty (tùy chọn)" value="{{ old('company') }}" class="form-control">
                                @error('company')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="business_type">Loại hình kinh doanh</label>
                                <select id="business_type" name="business_type" class="form-control">
                                    <option value="">Chọn loại hình</option>
                                    <option value="Pharmacy" {{ old('business_type') == 'Pharmacy' ? 'selected' : '' }}>Nhà thuốc</option>
                                    <option value="Clinic" {{ old('business_type') == 'Clinic' ? 'selected' : '' }}>Phòng khám</option>
                                    <option value="Medical Store" {{ old('business_type') == 'Medical Store' ? 'selected' : '' }}>Cửa hàng y tế</option>
                                    <option value="Individual" {{ old('business_type') == 'Individual' ? 'selected' : '' }}>Cá nhân</option>
                                    <option value="Other" {{ old('business_type') == 'Other' ? 'selected' : '' }}>Khác</option>
                                </select>
                                @error('business_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="experience">Số năm kinh nghiệm <span class="text-danger">*</span></label>
                                <input id="experience" name="experience" type="number" min="0" placeholder="Nhập số năm kinh nghiệm" value="{{ old('experience', 0) }}" class="form-control">
                                @error('experience')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="tax_code">Mã số thuế</label>
                                <input id="tax_code" name="tax_code" type="text" placeholder="Nhập mã số thuế" value="{{ old('tax_code') }}" class="form-control">
                                @error('tax_code')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Mô tả -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Mô tả</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="short_bio">Mô tả ngắn</label>
                                <input id="short_bio" name="short_bio" type="text" placeholder="Mô tả ngắn gọn" value="{{ old('short_bio') }}" class="form-control" maxlength="255">
                                @error('short_bio')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="bio">Mô tả chi tiết</label>
                                <textarea id="bio" name="bio" rows="4" placeholder="Mô tả chi tiết về đại lý" class="form-control">{{ old('bio') }}</textarea>
                                @error('bio')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cài đặt -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Cài đặt</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="commission_rate">Tỷ lệ hoa hồng (%) <span class="text-danger">*</span></label>
                                <input id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" placeholder="10.00" value="{{ old('commission_rate', 10.00) }}" class="form-control">
                                @error('commission_rate')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-control">
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                </select>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                <input id="password" name="password" type="password" placeholder="Nhập mật khẩu" class="form-control">
                                @error('password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Nhập lại mật khẩu" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin ngân hàng -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin ngân hàng (Tùy chọn)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bank_name">Tên ngân hàng</label>
                                        <input id="bank_name" name="bank_info[bank_name]" type="text" placeholder="VD: Vietcombank" value="{{ old('bank_info.bank_name') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="account_number">Số tài khoản</label>
                                        <input id="account_number" name="bank_info[account_number]" type="text" placeholder="Số tài khoản" value="{{ old('bank_info.account_number') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="account_holder">Chủ tài khoản</label>
                                        <input id="account_holder" name="bank_info[account_holder]" type="text" placeholder="Tên chủ tài khoản" value="{{ old('bank_info.account_holder') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="branch">Chi nhánh</label>
                                        <input id="branch" name="bank_info[branch]" type="text" placeholder="Chi nhánh" value="{{ old('bank_info.branch') }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Tạo đại lý
                </button>
                <a href="{{ route('agent.index') }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-header h6 {
        margin-bottom: 0;
    }
    .form-group label {
        font-weight: 600;
        color: #5a5c69;
    }
    .text-danger {
        font-size: 0.875rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Preview ảnh khi upload
    $('#photo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photo-preview').remove();
                $('#photo').after('<img id="photo-preview" src="' + e.target.result + '" class="mt-2 rounded" style="max-width: 200px; max-height: 200px;">');
            };
            reader.readAsDataURL(file);
        }
    });

    // Auto generate referral code based on name
    $('#name').on('blur', function() {
        const name = $(this).val();
        if (name && !$('#referral_code').val()) {
            const code = 'AGT_' + name.split(' ').map(word => word.charAt(0)).join('').toUpperCase() + '_' + Math.random().toString(36).substr(2, 4).toUpperCase();
            $('#referral_code').val(code);
        }
    });
});
</script>
@endpush