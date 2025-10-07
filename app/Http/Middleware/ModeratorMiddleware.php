<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModeratorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, что пользователь авторизован
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Необходимо войти в систему');
        }

        $userId = auth()->id();
        $user = auth()->user();
        
        // Проверяем права администратора или модератора
        if ($user && ($user->isAdministrator() || $user->isModerator())) {
            return $next($request);
        }

        // Для остальных пользователей показываем ошибку доступа
        abort(403, 'Доступ запрещен. Требуются права администратора или модератора.');
    }
}
