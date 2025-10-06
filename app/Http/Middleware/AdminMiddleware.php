<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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

        // Проверяем, что пользователь является администратором
        // Пока используем простую проверку по ID, позже можно добавить поле is_admin
        $adminIds = [1]; // ID администраторов
        
        if (!in_array(auth()->id(), $adminIds)) {
            abort(403, 'Доступ запрещен. Требуются права администратора.');
        }

        return $next($request);
    }
}
