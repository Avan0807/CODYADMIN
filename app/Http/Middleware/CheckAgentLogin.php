<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAgentLogin
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('agent')->check()) {
            return redirect()->route('agent.login.form')->with('error', 'Vui lòng đăng nhập.');
        }

        return $next($request);
    }
}
