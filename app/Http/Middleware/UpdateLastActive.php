<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateLastActive
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            DB::connection('mysql')
                ->table('user_sessions')
                ->where('account_id', Auth::id())
                ->where('active', 1)
                ->update(['last_active' => now()]);
        }

        return $next($request);
    }
}
