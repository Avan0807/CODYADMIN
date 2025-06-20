<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Đại lý - CODY</title>
    <link rel="stylesheet" href="{{ asset('css/agent-login.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

</head>
<body>

    <div class="agent-login-wrapper">
        <!-- Left Side - Welcome -->
        <div class="agent-left">
            <h1><i class="fas fa-handshake"></i>Chào mừng Đại lý</h1>
            <p>Truy cập hệ thống để quản lý đơn hàng, theo dõi doanh thu, và nhận hoa hồng mỗi ngày!</p>
            
            <div class="benefits">
                <div class="benefit-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Theo dõi doanh thu real-time</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-link"></i>
                    <span>Tạo link affiliate nhanh chóng</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-coins"></i>
                    <span>Nhận hoa hồng hấp dẫn</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Quản lý mọi lúc, mọi nơi</span>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="agent-right">
            <h2>Đăng nhập</h2>
            <p class="subtitle">Nhập thông tin để truy cập tài khoản đại lý</p>

            <!-- Error Messages -->
            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('agent.login') }}">
                @csrf

                <!-- Phone Input -->
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Số Điện Thoại
                    </label>
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" 
                               name="phone" 
                               id="phone" 
                               class="{{ $errors->has('phone') ? 'input-error' : '' }}"
                               placeholder="VD: 0987654321"
                               pattern="^0[0-9]{9}$"
                               title="Số điện thoại phải có 10 số và bắt đầu bằng số 0"
                               required 
                               value="{{ old('phone') }}"
                               autocomplete="tel">
                    </div>
                    @error('phone')
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="{{ $errors->has('password') ? 'input-error' : '' }}"
                               placeholder="Nhập mật khẩu"
                               required
                               autocomplete="current-password">
                    </div>
                    @error('password')
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="remember-group">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Ghi nhớ đăng nhập</label>
                </div>

                <!-- Login Button -->
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Đăng nhập Đại lý
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <span>hoặc</span>
            </div>

            <!-- Admin Link -->
            <div class="admin-link">
                <a href="{{ route('login') }}">
                    <i class="fas fa-user-shield"></i>
                    Đăng nhập dành cho Quản trị viên
                </a>
            </div>

            <!-- Footer Note -->
            <div class="footer-note">
                <i class="fas fa-info-circle"></i>
                Chỉ dành cho đại lý đã được phê duyệt
            </div>
        </div>
    </div>

    <script>
        // Auto-format phone number
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.slice(0, 10);
            }
            e.target.value = value;
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            
            if (!/^0[0-9]{9}$/.test(phone)) {
                e.preventDefault();
                alert('Số điện thoại phải có 10 số và bắt đầu bằng số 0');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự');
                return;
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>

</body>
</html>