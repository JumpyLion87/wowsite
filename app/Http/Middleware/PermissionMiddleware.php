<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (!Auth::check() || !hasPermission($permission)) {
            return redirect('/login');
        }

        return $next($request);
    }
}
