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
                'characters.totaltime',
                'characters.totalKills',
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
        
        // Расширенная статистика
        $totalPlaytime = $characters->sum('totaltime'); // в секундах
        $totalKills = $characters->sum('totalKills');
        $avgLevel = $characters->avg('level');
        
        // Топ персонажи
        $topCharacterByLevel = $characters->sortByDesc('level')->first();
        $topCharacterByGold = $characters->sortByDesc('money')->first();
        $topCharacterByPlaytime = $characters->sortByDesc('totaltime')->first();
        $topCharacterByKills = $characters->sortByDesc('totalKills')->first();

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
        
        // Получаем информацию о голосовании (упрощенная версия)
        $voteCheckService = app(\App\Services\VoteCheckService::class);
        $voteInfo = $voteCheckService->getVoteInfo($user);

        // Проверяем голос автоматически
        $voteCheckService = app(\App\Services\VoteCheckService::class);
        $voteResult = $voteCheckService->checkUserVote($user);
        
        // Если голос найден, сохраняем уведомление
        if ($voteResult['success']) {
            session()->flash('vote_notification', [
                'type' => 'success',
                'message' => $voteResult['message'],
                'points' => $voteResult['points']
            ]);
        }

        $activeSessions = $this->getActiveSessions($user->id);
        
        // Получаем дату последней смены пароля
        $userCurrency = \App\Models\UserCurrency::where('account_id', $user->id)->first();
        $lastPasswordChange = $userCurrency && $userCurrency->last_password_change 
            ? $userCurrency->last_password_change 
            : $accountInfo['joindate'];

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
            'lastPasswordChange' => $lastPasswordChange,
            'voteInfo' => $voteInfo,
            // Расширенная статистика
            'totalPlaytime' => $totalPlaytime,
            'totalKills' => $totalKills,
            'avgLevel' => round($avgLevel, 1),
            // Топ персонажи
            'topCharacterByLevel' => $topCharacterByLevel,
            'topCharacterByGold' => $topCharacterByGold,
            'topCharacterByPlaytime' => $topCharacterByPlaytime,
            'topCharacterByKills' => $topCharacterByKills,
        ]);

        
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|min:3|max:64',
            'current_password' => 'required|string|min:3',
        ]);

        $user = Auth::user();

        // Проверяем текущий пароль через SRP6
        if (!$user->authenticateWithSRP6($request->input('current_password'), $user->username)) {
            return back()->withErrors(['current_password' => __('account.incorrect_password')]);
        }

        // Проверяем, не используется ли уже этот email
        $emailExists = \App\Models\User::where('email', $request->input('new_email'))
            ->where('id', '!=', $user->id)
            ->exists();
        
        if ($emailExists) {
            return back()->withErrors(['new_email' => __('account.email_already_used')]);
        }

        // Обновляем email в таблице account
        $user->email = $request->input('new_email');
        $user->save();

        // Обновляем email в таблице user_currencies (если запись существует)
        $userCurrency = \App\Models\UserCurrency::where('account_id', $user->id)->first();
        if ($userCurrency) {
            $userCurrency->email = $request->input('new_email');
            $userCurrency->save();
        }

        return back()->with('success', __('account.email_updated'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|min:3',
            'new_password' => 'required|string|min:6|max:32',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = Auth::user();
        
        // Проверяем текущий пароль через SRP6
        if (!$user->authenticateWithSRP6($request->input('current_password'), $user->username)) {
            return back()->withErrors(['current_password' => __('account.incorrect_password')]);
        }
        
        // Обновление SRP6
        $user->updatePasswordWithSRP6($request->input('new_password'));

        // Обновляем дату смены пароля в таблице user_currencies
        $userCurrency = \App\Models\UserCurrency::where('account_id', $user->id)->first();
        if ($userCurrency) {
            $userCurrency->last_password_change = now();
            $userCurrency->save();
        }

        return back()->with('success', __('account.password_updated'));
    }

    public function changeAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|string|max:128'
        ]);

        $accountId = Auth::id();
        $user = Auth::user();
        
        // Используем модель UserCurrency для обновления аватара
        $userCurrency = \App\Models\UserCurrency::where('account_id', $accountId)->first();
        
        if ($userCurrency) {
            $userCurrency->avatar = $request->input('avatar');
            $userCurrency->save();
        } else {
            // Если записи нет, создаем новую
            \App\Models\UserCurrency::create([
                'account_id' => $accountId,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $request->input('avatar'),
                'points' => 0,
                'tokens' => 0,
                'role' => 'player'
            ]);
        }

        return back()->with('success', __('account.avatar_updated'));
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
        $accountId = $user->id;

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


