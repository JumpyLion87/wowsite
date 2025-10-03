<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class VoteController extends Controller
{
    public function submitVote(Request $request)
    {
        $user = Auth::user();
        $cooldownHours = env('VOTE_COOLDOWN_HOURS', 12);
        $rewardPoints = env('VOTE_REWARD_POINTS', 100);

        // Проверка наличия токена
        $token = $request->input('token');
        if (!$token) {
            return back()->withErrors(__('vote.missing_token'));
        }

        // Проверка уникальности токена
        if (DB::table('votes')->where('token', $token)->exists()) {
            return back()->withErrors(__('vote.token_already_used'));
        }

        // Проверка кулдауна
        $lastVote = DB::table('votes')
            ->where('user_id', $user->id)
            ->orderBy('voted_at', 'desc')
            ->first();

        if ($lastVote && Carbon::parse($lastVote->voted_at)->addHours($cooldownHours) > now()) {
            return back()->withErrors(__('vote.cooldown_error', ['hours' => $cooldownHours]));
        }

        // Проверка токена через запрос к mmotop
        $token = $request->input('token');
        $mmotopUrl = env('MMOTOP_VOTE_URL') . $token . '.txt?' . env('MMOTOP_API_TOKEN');
        $response = Http::get($mmotopUrl);

        if ($response->successful()) {
            $content = $response->body();
            if (str_contains($content, 'OK')) {
                // Начисление очков и запись голоса
                DB::connection('mysql')->transaction(function () use ($user, $rewardPoints, $token) {
                    DB::connection('mysql')->table('user_currencies')
                        ->where('account_id', $user->id)
                        ->update([
                            'points' => DB::raw("points + $rewardPoints"),
                            'last_vote_time' => now()
                        ]);

                    DB::table('votes')->insert([
                        'user_id' => $user->id,
                        'token' => $token,
                        'voted_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });

                return back()->with('message', __('vote.success', ['points' => $rewardPoints]));
            } else {
                return back()->withErrors(__('vote.invalid_token'));
            }
        } else {
            return back()->withErrors(__('vote.failed_to_verify'));
        }
    }

    public function generateToken(Request $request)
    {
        $user = Auth::user();

        // Генерация уникального токена
        $token = Str::random(32);

        // Сохранение токена в БД (временная таблица pending_votes)
        DB::table('pending_votes')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(60), // Токен действует 60 минут
        ]);

        // Перенаправление на сайт голосования
        return redirect(env('MMOTOP_SERVER_URL') . '?token=' . $token);
    }
}
