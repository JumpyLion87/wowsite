<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\VoteCheckService;
use Symfony\Component\HttpFoundation\Response;

class CheckVoteMiddleware
{
    protected $voteCheckService;

    public function __construct(VoteCheckService $voteCheckService)
    {
        $this->voteCheckService = $voteCheckService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем только для авторизованных пользователей
        if (Auth::check()) {
            $user = Auth::user();
            
            // Проверяем, не проверяли ли мы голос в этой сессии
            if (!session()->has('vote_checked_at') || 
                session('vote_checked_at') < now()->subMinutes(5)->timestamp) {
                
                // Проверяем голос
                $result = $this->voteCheckService->checkUserVote($user);
                
                // Сохраняем время проверки
                session(['vote_checked_at' => now()->timestamp]);
                
                // Если голос найден, сохраняем уведомление
                if ($result['success']) {
                    session()->flash('vote_notification', [
                        'type' => 'success',
                        'message' => $result['message'],
                        'points' => $result['points']
                    ]);
                }
            }
        }

        return $next($request);
    }
}
