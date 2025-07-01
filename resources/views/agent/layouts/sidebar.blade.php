<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
  <!-- Sidebar - Brand -->
  <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('agent.dashboard') }}">
    <div class="sidebar-brand-icon rotate-n-15">
      <i class="fas fa-user-shield"></i>
    </div>
    <div class="sidebar-brand-text mx-3">Đại lý</div>
  </a>

  <hr class="sidebar-divider my-0">

  <!-- Dashboard -->
  <li class="nav-item {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('agent.dashboard') }}">
      <i class="fas fa-fw fa-tachometer-alt"></i>
      <span>Bảng điều khiển</span>
    </a>
  </li>

  <!-- Profile -->
  <li class="nav-item {{ request()->routeIs('agent.profile*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('agent.profile') }}">
      <i class="fas fa-user"></i>
      <span>Hồ sơ của tôi</span>
    </a>
  </li>

  <!-- Orders -->
  <li class="nav-item {{ request()->routeIs('agentorder*') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('agentorder.index') }}">
          <i class="fas fa-shopping-cart"></i>
          <span>Đơn hàng</span>
      </a>
  </li>

  <!-- Affiliate Links -->
  <li class="nav-item {{ request()->routeIs('agent.links*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('agent.links.index') }}">
      <i class="fas fa-link"></i>
      <span>Link tiếp thị</span>
    </a>
  </li>

  <!-- Logout -->
  <hr class="sidebar-divider d-none d-md-block">
  <li class="nav-item">
    <a class="nav-link" href="{{ route('agent.logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      <i class="fas fa-sign-out-alt"></i>
      <span>Đăng xuất</span>
    </a>
    <form id="logout-form" action="{{ route('agent.logout') }}" method="POST" class="d-none">
      @csrf
    </form>
  </li>
</ul>