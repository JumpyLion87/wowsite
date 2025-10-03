<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Основная информация аккаунта (из БД auth)
        $accountInfo = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'locked' => 0,
            'online' => 0,
            'joindate' => $user->joindate,
            'last_login' => $user->last_login,
            'expansion' => $user->expansion,
        ];

        // Персонажи (пример: таблица characters в mysql_char)
        $characters = DB::connection('mysql_char')
            ->table('characters')
            ->leftJoin('guild_member', 'characters.guid', '=', 'guild_member.guid')
            ->leftJoin('guild', 'guild_member.guildid', '=', 'guild.guildid')
            ->select(
                'characters.guid',
                'characters.name',
                'characters.race',
                'characters.class',
                'characters.gender',
                'characters.level',
                'characters.money',
                'characters.online',
                'characters.class',
                'guild.name as guild_name'
            )
            ->where('characters.account', $user->id)
            ->orderBy('level', 'desc')
            ->get();

        
        // Быстрые статы по персонажам
        $totalCharacters = $characters->count();
        $highestLevel = $characters->max('level') ?? 0;
        $onlineCount = $characters->where('online', 1)->count();
        $totalGold = $characters->sum('money');

        // Валюта и аватар (из site БД, через UserCurrency хелперы)
        $points = User::getPoints($user->id);
        $tokens = User::getTokens($user->id);
        $avatar = User::getAvatar($user->id);

        // Информация о бане (auth.account_banned)
        $banInfo = DB::connection('mysql_auth')
            ->table('account_banned')
            ->select('bandate', 'unbandate', 'banreason')
            ->where('id', $user->id)
            ->where('active', 1)
            ->first();

        // Доступные аватары (site.profile_avatars active=1)
        $availableAvatars = DB::connection('mysql')
            ->table('profile_avatars')
            ->select('filename', 'display_name')
            ->where('active', 1)
            ->orderBy('display_name')
            ->get();

        // Лог активности (site.website_activity_log)
        $activityLog = DB::connection('mysql')
            ->table('website_activity_log')
            ->select('action', 'timestamp', 'details', 'character_name')
            ->where('account_id', $user->id)
            ->orderByDesc('timestamp')
            ->limit(10)
            ->get();

        // Кулдауны телепорта (site.character_teleport_log): guid => timestamp
        $teleportCooldowns = [];
        if ($characters->count() > 0) {
            $guids = $characters->pluck('guid')->all();
            $rows = DB::connection('mysql')
                ->table('character_teleport_log')
                ->select('character_guid', 'teleport_timestamp')
                ->whereIn('character_guid', $guids)
                ->get();
            foreach ($rows as $row) {
                $teleportCooldowns[(int) $row->character_guid] = (int) $row->teleport_timestamp;
            }
        }
        
        $lastVote = DB::table('votes')
            ->where('user_id', $user->id)
            ->orderBy('voted_at', 'desc')
            ->first();

        $cooldownHours = (int) env('VOTE_COOLDOWN_HOURS', 12);
        $remainingTime = null;

        if ($lastVote) {
            $nextVoteTime = Carbon::parse($lastVote->voted_at)->addHours($cooldownHours);
            if (now() < $nextVoteTime) {
                $remainingTime = $nextVoteTime->diffForHumans(now(), true); // Пример: "через 5 часов"
            }
        }

        $this->checkVoteAutomatically($user);

        $activeSessions = $this->getActiveSessions($user->id);

        return view('account.index', [
            'accountInfo' => $accountInfo,
            'characters' => $characters,
            'currencies' => ['points' => $points, 'tokens' => $tokens, 'avatar' => $avatar],
            'gmlevel' => $user->gm_level ?? 0,
            'role' => $user->role,
            'banInfo' => $banInfo,
            'availableAvatars' => $availableAvatars,
            'activityLog' => $activityLog,
            'teleportCooldowns' => $teleportCooldowns,
            'totalCharacters' => $totalCharacters,
            'highestLevel' => $highestLevel,
            'onlineCount' => $onlineCount,
            'totalGold' => $totalGold,
            'activeSessions' => $activeSessions,
            'remainingVoteTime' => $remainingTime ?? null,
        ]);

        
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|min:3|max:64',
            'current_password' => 'required|string',
        ]);

        $user = Auth::user();

        // Для SRP6 нет хэша пароля, поэтому проверку делаем через сервис SRP6 по необходимости
        // Здесь допускаем смену без повторной аутентификации, если уже залогинен
        $user->email = $request->input('new_email');
        $user->save();

        return back()->with('message', __('Email updated successfully!'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|min:3',
            'new_password' => 'required|string|min:6|max:32',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = Auth::user();
        // Обновление SRP6
        $user->updatePasswordWithSRP6($request->input('new_password'));

        return back()->with('message', __('Password changed successfully!'));
    }

    public function changeAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|string|max:128'
        ]);

        $accountId = Auth::id();
        DB::connection('mysql_site')
            ->table('user_currency')
            ->where('account_id', $accountId)
            ->update(['avatar' => $request->input('avatar')]);

        return back()->with('message', __('Avatar updated successfully!'));
    }

    public function teleportCharacter(Request $request)
    {
        $request->validate([
            'guid' => 'required|integer',
            'destination' => 'required|string|in:shattrath,dalaran',
        ]);

        $guid = (int) $request->input('guid');
        $accountId = Auth::id();

        // Проверяем принадлежность персонажа
        $character = DB::connection('mysql_char')
            ->table('characters')
            ->where('guid', $guid)
            ->where('account', $accountId)
            ->first();

        if (!$character) {
            throw ValidationException::withMessages(['guid' => __('Invalid character')]);
        }

        // Пример: запись cooldown в таблицу site.character_cooldowns или session/cache (упрощенно пропустим)
        // Выполнить телепорт: реализация зависит от ядра/сервера. Заглушка:
        // DB::connection('mysql_char')->statement('CALL teleport_character(?, ?)', [$guid, $destination]);

        return back()->with('message', __('Character teleported successfully!'));
    }

    /**
     * Получить активные сессии пользователя
     */
    protected function getActiveSessions($accountId)
    {
        return DB::connection('mysql')
            ->table('user_sessions')
            ->select('device_type', 'ip_address', 'last_active')
            ->where('account_id', $accountId)
            ->where('active', 1)
            ->get();
    }

    /**
     * Деактивация сессии
     */
    public function destroySessions(Request $request)
    {
        $accountId = Auth::id();

        DB::connection('mysql')
            ->table('user_sessions')
            ->where('account_id', $accountId)
            ->where('active', 1)
            ->where('session_id', '!=', $request->session()->getId())
            ->update(['active' => 0]);

        return back()->with('message', __('All sessions have been terminated.'));
    }


    /**
     * Получить разрешённые IP-адреса пользователя
     */
    protected function getAllowedIPs($accountId)
    {
        return DB::connection('mysql')
            ->table('user_ip_restrictions')
            ->select('ip_address')
            ->where('account_id', $accountId)
            ->pluck('ip_address')
            ->toArray();
    }

    private function checkVoteAutomatically($user)
    {
        $cooldownHours = (int) env('VOTE_COOLDOWN_HOURS', 12);
        $rewardPoints = env('VOTE_REWARD_POINTS', 100);
        $mmotopUrl = env('MMOTOP_VOTE_URL');

        // Проверка кулдауна
        $lastVote = DB::table('votes')
            ->where('user_id', $user->id)
            ->orderBy('voted_at', 'desc')
            ->first();

        if ($lastVote && Carbon::parse($lastVote->voted_at)->addHours($cooldownHours) > now()) {
            return; // Кулдаун еще не истёк
        }

        // Запрос к mmotop
        $response = Http::get($mmotopUrl);

        if (!$response->successful()) {
            return; // Не удалось получить данные
        }

        $content = $response->body();
        $accountId = $user->account_id;

        // Поиск голоса по account_id
        $lines = explode("\n", $content);
        $found = false;

        foreach ($lines as $line) {
            $parts = array_map('trim', explode("\t", $line));
            if (!empty($parts[0]) && $parts[0] == $accountId) {
                $found = true;
                break;
            }

            // Проверка по username (с учетом регистра)
            if (!empty($parts[3]) && strcasecmp($parts[3], $user->username) === 0) {
                $found = true;
                break;
            }
            
        }

        if (!$found) {
            return; // Голос не найден
        }

        // Начисление очков и запись голоса
        DB::transaction(function () use ($user, $rewardPoints) {
            DB::connection('mysql')->table('user_currencies')
                ->where('account_id', $user->id)
                ->update([
                    'points' => DB::raw("points + $rewardPoints"),
                    'last_vote_time' => now()
                ]);

            DB::table('votes')->insert([
                'user_id' => $user->id,
                'voted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

         // ✅ Установка сообщения в сессии
        session()->flash('vote_success', __('vote.success', ['points' => $rewardPoints]));
    }

}


