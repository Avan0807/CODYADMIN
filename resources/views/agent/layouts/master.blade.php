<!DOCTYPE html>
<html lang="vi">
@include('agent.layouts.head') <!-- Dùng file head RIÊNG của agent -->

<body id="page-top">
  <div id="wrapper">
    @include('agent.layouts.sidebar') <!-- Sidebar riêng cho agent -->

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        @include('agent.layouts.header') <!-- Header riêng cho agent -->
        @yield('main-content') <!-- Nội dung chính -->
      </div>

      @include('agent.layouts.footer') <!-- Footer riêng nếu cần -->
    </div>
  </div>

  @include('agent.layouts.scripts') <!-- JS riêng nếu muốn -->
</body>
</html>
