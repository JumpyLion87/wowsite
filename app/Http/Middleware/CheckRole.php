<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Необходимо войти в систему');
        }

        $user = Auth::user();
        
        // Админы имеют все права
        if ($user->isAdministrator()) {
            return $next($request);
        }

        // Проверяем роль
        if (!$user->hasRole($role)) {
            abort(403, 'У вас нет прав для доступа к этому разделу');
        }

        return $next($request);
    }
}
