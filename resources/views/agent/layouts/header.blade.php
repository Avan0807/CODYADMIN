<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
  <!-- Sidebar Toggle (Topbar) -->
  <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
    <i class="fa fa-bars"></i>
  </button>

  <!-- Topbar Navbar -->
  <ul class="navbar-nav ml-auto">
    <!-- User Info -->
    <li class="nav-item dropdown no-arrow">
      <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
        @php
          $agent = Auth::guard('agent')->user();
        @endphp
        <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ $agent->name ?? 'Đại lý' }}</span>
        <img class="img-profile rounded-circle" src="{{ $agent->photo ?? asset('backend/img/avatar.png') }}" width="30" height="30">
      </a>
      <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
        <a class="dropdown-item" href="#">
          <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
          Hồ sơ
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ route('agent.logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
          Đăng xuất
        </a>
        <form id="logout-form" action="{{ route('agent.logout') }}" method="POST" class="d-none">
          @csrf
        </form>
      </div>
    </li>
  </ul>
</nav>
