<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                
                // 🎯 Redirect theo guard tương ứng
                if ($guard === 'web' || $guard === null || $guard === 'admin') {
                    // Admin đã login → về admin dashboard
                    return redirect('/admin');
                }
                
                if ($guard === 'agent') {
                    // Agent đã login → về agent dashboard
                    return redirect()->route('agent.dashboard');
                }
                
                // Fallback mặc định
                return redirect(RouteServiceProvider::HOME);
            }
        }

        // 🔀 Cross-guard checking: Nếu vào login này nhưng đã login guard khác
        
        // Nếu vào agent login nhưng đã login admin
        if ($request->is('agent/login') && (Auth::guard('web')->check() || Auth::guard('admin')->check())) {
            return redirect('/admin')->with('info', 'Bạn đang đăng nhập với tài khoản admin');
        }
        
        // Nếu vào admin login nhưng đã login agent  
        if (($request->is('admin/login') || $request->is('login')) && Auth::guard('agent')->check()) {
            return redirect()->route('agent.dashboard')->with('info', 'Bạn đang đăng nhập với tài khoản đại lý');
        }

        return $next($request);
    }
}