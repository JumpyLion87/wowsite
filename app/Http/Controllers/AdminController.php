<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Services\ServerStatusService;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        // Получение общих статистик
        $totalUsers = $this->getTotalUsers();
        $totalAccounts = $this->getTotalAccounts();
        $totalChars = $this->getTotalChars();
        $totalBans = $this->getTotalBans();

        
        // Обработка фильтров
        $searchUsername = $request->input('search_username', '');
        $searchEmail = $request->input('search_email', '');
        $roleFilter = $request->input('role_filter', '');

        // Получение пользователей (отдельные запросы + объединение)
        $users = $this->getFilteredUsers($searchUsername, $searchEmail, $roleFilter);

        // Получение банов
        $bans = $this->getActiveBans();

        // Передача данных в шаблон
        return View::make('admin.dashboard', compact(
            'totalUsers', 'totalAccounts', 'totalChars', 'totalBans',
            'users', 'bans', 'searchUsername', 'searchEmail', 'roleFilter'
        ));
    }

    /**
     * Получить общее количество пользователей (user_currencies)
     */
    protected function getTotalUsers(): int
    {
        return DB::connection('mysql')->table('user_currencies')->count();
    }

    /**
     * Получить общее количество аккаунтов (account)
     */
    protected function getTotalAccounts(): int
    {
        return DB::connection('mysql_auth')->table('account')->count();
    }

    /**
     * Получить общее количество персонажей (characters)
     */
    protected function getTotalChars(): int
    {
        return DB::connection('mysql_char')->table('characters')->count();
    }

    /**
     * Получить активные бани (account_banned)
     */
    protected function getTotalBans(): int
    {
        return DB::connection('mysql_auth')->table('account_banned')
            ->where('active', 1)
            ->count();
    }

    /**
     * Получить отфильтрованных пользователей (admin/moderator)
     */
    protected function getFilteredUsers(string $searchUsername, string $searchEmail, ?string $roleFilter)
    {
        // 1. Получаем пользователей из user_currencies
        $userCurrencies = DB::connection('mysql')->table('user_currencies');

        if ($searchUsername) {
            $userCurrencies->where('username', 'like', "%{$searchUsername}%");
        }
        if ($roleFilter) {
            $userCurrencies->where('role', $roleFilter);
        }

        $userCurrencies = $userCurrencies->whereIn('role', ['admin', 'moderator'])
            ->orderBy('last_updated', 'desc')
            ->limit(5)
            ->get();

        // 2. Получаем почты из account (mysql_auth)
        $accounts = DB::connection('mysql_auth')->table('account')
            ->whereIn('id', $userCurrencies->pluck('account_id'))
            ->pluck('email', 'id');

        // 3. Объединяем данные
        return $userCurrencies->map(function ($item) use ($accounts) {
            $item->email = $accounts[$item->account_id] ?? 'Не найдено';
            return $item;
        });
    }

    /**
     * Получить последние активные бани
     */
    protected function getActiveBans()
    {
        return DB::connection('mysql_auth')->table('account_banned')
            ->join('account', 'account_banned.id', '=', 'account.id')
            ->select(
                'account_banned.id',
                'account_banned.bandate',
                'account_banned.unbandate',
                'account_banned.banreason',
                'account.username'
            )
            ->where('account_banned.active', 1)
            ->orderBy('bandate', 'desc')
            ->limit(5)
            ->get();
    }
}
