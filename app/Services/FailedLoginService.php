<?php

namespace App\Services;

use App\Models\FailedLogin;
use Illuminate\Support\Facades\Log;

class FailedLoginService
{
    /**
     * Зарегистрировать неудачную попытку входа
     */
    public function recordFailedAttempt(string $ipAddress, ?string $username): bool
    {
        $currentTime = now()->timestamp;

        $existing = FailedLogin::where('ip_address', $ipAddress)->first();

        if ($existing) {
            $existing->attempts = (int) ($existing->attempts ?? 0) + 1;
            $existing->last_attempt = $currentTime;
            $existing->username = $username;

            // Блокировка при превышении лимита
            if ($existing->attempts >= config('app.max_login_attempts')) {
                $existing->block_until = $currentTime + ((int) config('app.lockout_duration', 15) * 60);
            }
            
            return $existing->save();
        } else {
            return FailedLogin::create([
                'ip_address' => $ipAddress,
                'username' => $username,
                'attempts' => 1,
                'last_attempt' => $currentTime,
                'block_until' => null,
            ]) !== null;
        }
    }

    /**
     * Проверить, заблокирован ли IP-адрес
     */
    public function isIpBlocked(string $ipAddress): bool
    {
        $record = FailedLogin::where('ip_address', $ipAddress)->first();
        if (!$record || empty($record->block_until)) {
            return false;
        }

        return ((int) $record->block_until) > now()->timestamp;
    }

    /**
     * Получить оставшееся время блокировки
     */

    public function getBlockTimeRemaining(string $ipAddress): int
    {
        $record = FailedLogin::where('ip_address', $ipAddress)->first();

        if (!$record || !$record->block_until) {
            return 0;
        }

        $remaining = ((int) $record->block_until) - now()->timestamp;
        return max(0, $remaining);
    }

    /**
     * Сбросить счетчик попыток
     */

    public function resetFailedAttempts(string $ipAddress): bool
    {
        return FailedLogin::where('ip_address', $ipAddress)->delete();
    }

    /**
     * Очистить устаревшие записи (старше 24 часов)
     */
    public function cleanupOldRecords(): int
    {
        $cutoffTs = now()->subDay()->timestamp;
        return FailedLogin::where('last_attempt', '<', $cutoffTs)->delete();
    }

    /**
     * Получить количество попыток
     */
    public function getAttemptCount(string $ipAddress): int
    {
        $record = FailedLogin::where('ip_address', $ipAddress)->first();
        return $record ? (int) ($record->attempts ?? 0) : 0;
    }

    /**
     * Получить статистику
     */
    public function getStatistics(): array
    {
        return [
            'total_attempts' => FailedLogin::count(),
            'blocked_ips' => FailedLogin::where('block_until', '>', now()->timestamp)->count(),
            'avg_attempts' => FailedLogin::avg('attempts') ?: 0,
        ];
    }

    public function getAllBlockedIps(): array
    {
        return FailedLogin::where('block_until', '>', now()->timestamp)
            ->pluck('ip_address')
            ->unique()
            ->values()
            ->all();
    }
}
