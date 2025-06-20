<!DOCTYPE html>
<html lang="en">

<head>
  <title>CODY - ĐĂNG NHẬP QUẢN TRỊ</title>
  @include('backend.layouts.head')
  <style>
    .bg-gradient-info {
      background: linear-gradient(135deg, #36b9cc 0%, #1cc88a 100%);
    }
    
    .card {
      border-radius: 1rem;
      box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .bg-login-image {
      background: linear-gradient(rgba(54, 185, 204, 0.1), rgba(54, 185, 204, 0.1));
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      text-align: center;
      padding: 2rem;
    }
    
    .admin-welcome {
      color: #36b9cc;
      font-weight: 600;
      margin-top: 1rem;
    }
    
    .admin-subtitle {
      color: #5a5c69;
      font-size: 0.9rem;
      margin-top: 0.5rem;
    }
    
    .form-control-user {
      transition: all 0.3s ease;
      border: 2px solid #e3e6f0;
    }
    
    .form-control-user:focus {
      border-color: #36b9cc;
      box-shadow: 0 0 0 0.2rem rgba(54, 185, 204, 0.25);
    }
    
    .btn-success {
      background: linear-gradient(45deg, #1cc88a, #36b9cc);
      border: none;
      transition: all 0.3s ease;
    }
    
    .btn-success:hover {
      background: linear-gradient(45deg, #17a673, #2c9faf);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(28, 200, 138, 0.4);
    }
    
    .agent-link {
      color: #36b9cc;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }
    
    .agent-link:hover {
      color: #2c9faf;
      text-decoration: none;
    }
    
    .footer-info {
      text-align: center;
      margin-top: 1rem;
      color: #858796;
      font-size: 0.8rem;
    }
    
    .alert-custom {
      border-radius: 0.5rem;
      border: none;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    
    .alert-error {
      background: linear-gradient(45deg, #e74c3c, #c0392b);
      color: white;
    }
    
    .alert-success {
      background: linear-gradient(45deg, #1cc88a, #17a673);
      color: white;
    }
    
    .input-group-icon {
      position: relative;
    }
    
    .input-group-icon i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #858796;
      z-index: 10;
    }
    
    .input-group-icon input {
      padding-left: 2.5rem;
    }
    
    @media (max-width: 768px) {
      .bg-login-image {
        display: none !important;
      }
    }
  </style>
</head>

<body class="bg-gradient-info">

  <div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9 mt-5">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <!-- Left Side - Logo & Welcome -->
              <div class="col-lg-6 d-none d-lg-block bg-login-image">
                <img src="{{ asset('backend/img/logo.png') }}" alt="CODY Logo" class="img-fluid" style="max-width: 200px;">
                <h4 class="admin-welcome">Hệ thống Quản trị CODY</h4>
                <p class="admin-subtitle">Quản lý toàn diện - Kiểm soát hiệu quả</p>
                
                <div class="mt-4">
                  <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-users text-info mr-2"></i>
                    <small class="text-muted">Quản lý người dùng</small>
                  </div>
                  <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-chart-bar text-info mr-2"></i>
                    <small class="text-muted">Thống kê doanh thu</small>
                  </div>
                  <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-cogs text-info mr-2"></i>
                    <small class="text-muted">Cấu hình hệ thống</small>
                  </div>
                </div>
              </div>
              
              <!-- Right Side - Login Form -->
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-2">
                      <i class="fas fa-user-shield text-info mr-2"></i>
                      Đăng nhập Quản trị
                    </h1>
                    <p class="text-muted mb-4">Nhập thông tin để truy cập hệ thống</p>
                  </div>

                  <!-- Error Messages -->
                  @if(session('error'))
                    <div class="alert alert-custom alert-error">
                      <i class="fas fa-exclamation-triangle mr-2"></i>
                      {{ session('error') }}
                    </div>
                  @endif

                  @if(session('success'))
                    <div class="alert alert-custom alert-success">
                      <i class="fas fa-check-circle mr-2"></i>
                      {{ session('success') }}
                    </div>
                  @endif

                  <!-- Login Form -->
                  <form class="user" method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <!-- Phone Input -->
                    <div class="form-group">
                      <div class="input-group-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" 
                               class="form-control form-control-user @error('phone') is-invalid @enderror" 
                               name="phone" 
                               value="{{ old('phone') }}" 
                               id="adminInputPhone" 
                               placeholder="Nhập số điện thoại quản trị..."
                               pattern="^0[0-9]{9}$"
                               title="Số điện thoại phải có 10 số và bắt đầu bằng số 0"
                               required 
                               autocomplete="phone" 
                               autofocus>
                      </div>
                      @error('phone')
                        <span class="invalid-feedback" role="alert">
                          <strong><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</strong>
                        </span>
                      @enderror
                    </div>
                    
                    <!-- Password Input -->
                    <div class="form-group">
                      <div class="input-group-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               class="form-control form-control-user @error('password') is-invalid @enderror" 
                               id="adminInputPassword" 
                               placeholder="Mật khẩu quản trị" 
                               name="password" 
                               required 
                               autocomplete="current-password">
                      </div>
                      @error('password')
                        <span class="invalid-feedback" role="alert">
                          <strong><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</strong>
                        </span>
                      @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="form-group">
                      <div class="custom-control custom-checkbox small">
                        <input type="checkbox" 
                               class="custom-control-input" 
                               name="remember" 
                               id="adminRemember" 
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="adminRemember">
                          <i class="fas fa-memory mr-1"></i>Ghi nhớ tôi
                        </label>
                      </div>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" class="btn btn-success btn-user btn-block">
                      <i class="fas fa-sign-in-alt mr-2"></i>
                      Đăng nhập Quản trị
                    </button>
                  </form>
                  
                  <hr>
                  
                  <!-- Agent Login Link -->
                  <div class="text-center">
                    <a class="agent-link" href="{{ route('agent.login.form') }}">
                      <i class="fas fa-handshake mr-1"></i>
                      Đăng nhập dành cho Đại lý
                    </a>
                  </div>
                  
                  <!-- Footer Info -->
                  <div class="footer-info">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Chỉ dành cho quản trị viên được ủy quyền
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

  <script>
    // Auto-format phone number for admin
    document.getElementById('adminInputPhone').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 10) {
        value = value.slice(0, 10);
      }
      e.target.value = value;
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const phone = document.getElementById('adminInputPhone').value;
      const password = document.getElementById('adminInputPassword').value;
      
      if (!/^0[0-9]{9}$/.test(phone)) {
        e.preventDefault();
        alert('Số điện thoại quản trị phải có 10 số và bắt đầu bằng số 0');
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
      const alerts = document.querySelectorAll('.alert-custom');
      alerts.forEach(function(alert) {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s ease';
        setTimeout(function() {
          alert.style.display = 'none';
        }, 500);
      });
    }, 5000);

    // Focus effect enhancement
    document.querySelectorAll('.form-control-user').forEach(function(input) {
      input.addEventListener('focus', function() {
        this.parentElement.querySelector('i').style.color = '#36b9cc';
      });
      
      input.addEventListener('blur', function() {
        this.parentElement.querySelector('i').style.color = '#858796';
      });
    });
  </script>

</body>

</html>

@push('scripts')
@endpush