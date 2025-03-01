<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfTokenForAjax extends Middleware
{
    public function handle($request, Closure $next)
    {
        if ($request->ajax()) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
