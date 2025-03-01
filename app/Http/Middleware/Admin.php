<?php

namespace App\Http\Middleware;

use Closure;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->user()->role=='admin'){
            return $next($request);
        }
        else{
            request()->session()->flash('error','Bạn không có quyền truy cập vào trang này');
            return redirect()->route($request->user()->role);
        }
    }
}
