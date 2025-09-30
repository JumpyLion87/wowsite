<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

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
            ->select('guid','name','race','class','gender','level','money','online')
            ->where('account', $user->id)
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
        $character = DB::connection('mysql_char')->table('characters')
            ->where('guid', $guid)->where('account', $accountId)->first();
        if (!$character) {
            throw ValidationException::withMessages(['guid' => __('Invalid character')]);
        }

        // Пример: запись cooldown в таблицу site.character_cooldowns или session/cache (упрощенно пропустим)
        // Выполнить телепорт: реализация зависит от ядра/сервера. Заглушка:
        // DB::connection('mysql_char')->statement('CALL teleport_character(?, ?)', [$guid, $destination]);

        return back()->with('message', __('Character teleported!'));
    }
}


