<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VoteCheckService
{
    /**
     * Проверить новые голоса пользователя
     * 
     * @param User $user
     * @return array ['success' => bool, 'points' => int, 'message' => string]
     */
    public function checkUserVote(User $user): array
    {
        $cooldownHours = (int) env('VOTE_COOLDOWN_HOURS', 24);
        $rewardPoints = (int) env('VOTE_REWARD_POINTS', 100);
        
        // Правильный URL для получения логов голосов
        $mmotopLogsUrl = env('MMOTOP_LOGS_URL');
        $mmotopApiToken = env('MMOTOP_API_TOKEN');
        
        // Формируем полный URL с токеном
        $mmotopVoteUrl = $mmotopLogsUrl . '?' . $mmotopApiToken;

        // Проверка наличия URL
        if (empty($mmotopLogsUrl) || empty($mmotopApiToken)) {
            return ['success' => false, 'points' => 0, 'message' => 'MMOTOP URL not configured'];
        }

        // Проверка кулдауна
        $lastVote = DB::table('votes')
            ->where('user_id', $user->id)
            ->orderBy('voted_at', 'desc')
            ->first();

        if ($lastVote && Carbon::parse($lastVote->voted_at)->addHours($cooldownHours) > now()) {
            $remainingTime = Carbon::parse($lastVote->voted_at)->addHours($cooldownHours)->diffForHumans();
            return [
                'success' => false, 
                'points' => 0, 
                'message' => __('vote.cooldown_active', ['time' => $remainingTime])
            ];
        }

        try {
            // Запрос к MMOTOP API
            $response = Http::timeout(10)->get($mmotopVoteUrl);

            if (!$response->successful()) {
                Log::warning('MMOTOP API request failed', [
                    'status' => $response->status(),
                    'user_id' => $user->id
                ]);
                return ['success' => false, 'points' => 0, 'message' => 'Failed to connect to MMOTOP'];
            }

            $content = $response->body();
            
            // Парсинг данных от MMOTOP
            $voteData = $this->parseVoteData($content, $user, $lastVote);

            if ($voteData) {
                // Начисление очков с сохранением ID голоса и времени mmotop
                $this->rewardUser($user, $rewardPoints, $voteData['vote_id'], $voteData['mmotop_datetime'] ?? null);
                
                return [
                    'success' => true,
                    'points' => $rewardPoints,
                    'message' => __('vote.success', ['points' => $rewardPoints])
                ];
            }

            return ['success' => false, 'points' => 0, 'message' => 'No new votes found'];

        } catch (\Exception $e) {
            Log::error('Vote check error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return ['success' => false, 'points' => 0, 'message' => 'Error checking vote'];
        }
    }

    /**
     * Парсинг данных голосования от MMOTOP
     * 
     * @param string $content
     * @param User $user
     * @param object|null $lastVote
     * @return array|false ['vote_id' => string, 'timestamp' => int] или false
     */
    protected function parseVoteData(string $content, User $user, $lastVote)
    {
        // Формат данных MMOTOP: vote_id date time ip username server_id
        // Пример: 283395366 03.10.2025 04:01:56 89.169.186.89 farkadi 1
        
        $lines = explode("\n", trim($content));
        $lastVoteTimestamp = $lastVote ? Carbon::parse($lastVote->voted_at)->timestamp : 0;

        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', trim($line));
            
            if (count($parts) < 6) {
                continue;
            }

            $voteId = $parts[0];      // Уникальный ID голоса от MMOTOP
            $date = $parts[1];         // Дата
            $time = $parts[2];         // Время
            $ip = $parts[3];           // IP адрес
            $username = $parts[4];     // Username
            $serverId = $parts[5];     // ID сервера

            // Проверка по username (регистронезависимо)
            $isMatch = strcasecmp($username, $user->username) === 0;

            if ($isMatch) {
                // Проверка, не был ли этот голос уже учтен
                $alreadyProcessed = DB::table('votes')
                    ->where('mmotop_vote_id', $voteId)
                    ->exists();

                if ($alreadyProcessed) {
                    continue; // Этот голос уже был учтен
                }

                // Парсинг времени голоса - используем время mmotop как есть
                try {
                    // Получаем часовой пояс mmotop из конфигурации (по умолчанию UTC)
                    $mmotopTimezone = env('MMOTOP_TIMEZONE', 'UTC');
                    
                    // Создаем Carbon объект с временем mmotop в его часовом поясе
                    $voteDateTime = Carbon::createFromFormat('d.m.Y H:i:s', "$date $time", $mmotopTimezone);
                    $voteTimestamp = $voteDateTime->timestamp;
                    
                    // Проверка, что голос новый (после последнего учтенного)
                    if ($voteTimestamp > $lastVoteTimestamp) {
                        return [
                            'vote_id' => $voteId,
                            'timestamp' => $voteTimestamp,
                            'mmotop_datetime' => $voteDateTime->toDateTimeString()
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to parse vote timestamp', [
                        'vote_id' => $voteId,
                        'date' => $date,
                        'time' => $time,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
        }

        return false;
    }

    /**
     * Начисление награды пользователю
     * 
     * @param User $user
     * @param int $points
     * @param string $mmotopVoteId
     * @param string|null $mmotopDateTime
     * @return void
     */
    protected function rewardUser(User $user, int $points, string $mmotopVoteId, string $mmotopDateTime = null): void
    {
        DB::transaction(function () use ($user, $points, $mmotopVoteId, $mmotopDateTime) {
            // Используем время mmotop как время голоса, если доступно
            $voteTime = $mmotopDateTime ? Carbon::parse($mmotopDateTime) : now();
            
            // Начисление очков
            DB::connection('mysql')->table('user_currencies')
                ->where('account_id', $user->id)
                ->update([
                    'points' => DB::raw("points + $points"),
                    'last_vote_time' => $voteTime
                ]);

            // Запись голоса с ID от MMOTOP и временем mmotop
            DB::table('votes')->insert([
                'user_id' => $user->id,
                'mmotop_vote_id' => $mmotopVoteId,
                'voted_at' => $voteTime,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Лог активности
            DB::connection('mysql')->table('website_activity_log')->insert([
                'account_id' => $user->id,
                'action' => 'vote_rewarded',
                'timestamp' => $voteTime->timestamp, // UNIX timestamp времени mmotop
                'details' => "Получено $points поинтов за голосование на MMOTOP (Vote ID: $mmotopVoteId, MMOTOP Time: " . ($mmotopDateTime ?? 'N/A') . ")",
            ]);
        });
    }

    /**
     * Получить информацию о голосовании с учетом времени mmotop
     * 
     * @param User $user
     * @return array
     */
    public function getVoteInfo(User $user): array
    {
        $cooldownHours = (int) env('VOTE_COOLDOWN_HOURS', 24);
        $rewardPoints = (int) env('VOTE_REWARD_POINTS', 100);
        
        // Получаем последний голос
        $lastVote = DB::table('votes')
            ->where('user_id', $user->id)
            ->orderBy('voted_at', 'desc')
            ->first();

        $canVote = true;
        $remainingTime = null;
        $nextVoteTime = null;

        if ($lastVote) {
            // Используем время mmotop для расчета следующего голоса
            $lastVoteTime = Carbon::parse($lastVote->voted_at);
            $nextVoteTime = $lastVoteTime->copy()->addHours($cooldownHours);
            
            // Получаем текущее время mmotop
            $mmotopTimezone = env('MMOTOP_TIMEZONE', 'UTC');
            $currentMmotopTime = Carbon::now($mmotopTimezone);
            
            if ($currentMmotopTime < $nextVoteTime) {
                $canVote = false;
                $remainingTime = $nextVoteTime->diffForHumans($currentMmotopTime, true);
            }
        }

        return [
            'success' => true,
            'can_vote' => $canVote,
            'remaining_time' => $remainingTime,
            'next_vote_time' => $nextVoteTime ? $nextVoteTime->format('d.m.Y H:i') : null,
            'last_vote' => $lastVote ? Carbon::parse($lastVote->voted_at)->format('d.m.Y H:i') : null,
            'reward_points' => $rewardPoints,
            'cooldown_hours' => $cooldownHours,
            'mmotop_timezone' => $mmotopTimezone ?? 'UTC'
        ];
    }

    /**
     * Проверить голоса для всех онлайн пользователей
     * (для cron задачи)
     * 
     * @return array
     */
    public function checkAllOnlineUsers(): array
    {
        $results = [
            'checked' => 0,
            'rewarded' => 0,
            'errors' => 0
        ];

        // Получить пользователей, которые были онлайн в последние 5 минут
        $recentSessions = DB::connection('mysql')
            ->table('user_sessions')
            ->where('active', 1)
            ->where('last_active', '>=', now()->subMinutes(5))
            ->pluck('account_id')
            ->unique();

        foreach ($recentSessions as $accountId) {
            $user = User::find($accountId);
            if (!$user) {
                continue;
            }

            $results['checked']++;
            $result = $this->checkUserVote($user);
            
            if ($result['success']) {
                $results['rewarded']++;
            } elseif (str_contains($result['message'], 'Error')) {
                $results['errors']++;
            }
        }

        return $results;
    }
}
