<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
        $user = Auth::user();
        
        // Админы имеют все права
        if ($user && $user->isAdministrator()) {
            return $next($request);
        }
        
        // Модераторы имеют ограниченные права
        if ($user && $user->isModerator()) {
            // Проверяем, есть ли у модератора конкретное разрешение
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        // Для остальных пользователей показываем ошибку доступа
        abort(403, 'У вас нет прав для выполнения этого действия');
    }
}
