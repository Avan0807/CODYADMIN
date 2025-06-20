<nav class="agent-navbar">
    <div class="container d-flex justify-content-between align-items-center py-3">
        <h4 class="m-0 text-white">Agent Dashboard</h4>
        <div class="text-white">
            Xin chào, <strong>{{ Auth::guard('agent')->user()->name ?? 'Đại lý' }}</strong> |
            <a href="{{ route('agent.logout') }}"
               onclick="event.preventDefault(); document.getElementById('agent-logout-form').submit();"
               class="text-warning ml-2">Đăng xuất</a>

            <form id="agent-logout-form" action="{{ route('agent.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</nav>
