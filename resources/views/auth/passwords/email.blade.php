<!DOCTYPE html>
<html lang="en">

<head>
@include('backend.layouts.head')

</head>

<body class="bg-gradient-primary">

  <div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-2">Quên mật khẩu?</h1>
                    <p class="mb-4">Chúng tôi hiểu, mọi chuyện đều có thể xảy ra. Chỉ cần nhập số điện thoại của bạn bên dưới và chúng tôi sẽ gửi cho bạn liên kết để đặt lại mật khẩu!</p>
                  </div>
                  @if (session('status'))
                      <div class="alert alert-success" role="alert">
                          {{ session('status') }}
                      </div>
                  @endif
                  <form class="user" method="POST" action="{{ route('password.phone') }}">
                    @csrf
                    <div class="form-group">
                      <input type="text" class="form-control form-control-user @error('phoneNumber') is-invalid @enderror" id="exampleInputPhone" placeholder="Nhập số điện thoại..." name="phoneNumber" value="{{ old('phoneNumber') }}" required autofocus>
                        @error('phoneNumber')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-user btn-block">
                      Đặt lại mật khẩu
                    </button>
                  </form>
                  <hr>
                  <div class="text-center">
                    <a class="small" href="{{ route('login') }}">Bạn đã có tài khoản? Đăng nhập!</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

</body>

</html>
