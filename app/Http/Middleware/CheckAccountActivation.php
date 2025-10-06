<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccountActivation
{
    /**
     * Handle an incoming request.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param mixed
     * 
     */
    public function handle(Request $request, Closure $next)
    {
        // Проверить, авторизован ли пользователь
        if (!Auth::check()) {
            //Если не авторизован. продолжаем выполнение (или перенаправляем на страницу входа)
            return $next($request);
        }

        // Проверяем статус активации аккаунта
        $user = Auth::user();
        if ($user->activated !==1) {
            // Аккаунт не активирован - перенаправляем на страницу повторной активации
            return redirect('/resend-activation')->with('error', 'Account not activated');
        }

        // Аккаунт активирован - продолжаем выполнение
        return $next($request);
    }
}
