<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\PermissionHelper;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Необходимо войти в систему');
        }

        $userId = Auth::id();
        
        // Админы имеют все права
        if (PermissionHelper::isAdmin($userId)) {
            return $next($request);
        }

        // Проверяем разрешение
        if (!PermissionHelper::hasPermission($userId, $permission)) {
            abort(403, 'У вас нет прав для выполнения этого действия');
        }

        return $next($request);
    }
}
