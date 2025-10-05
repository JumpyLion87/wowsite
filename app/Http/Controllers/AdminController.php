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
     * Страница настроек админки
     */
    public function settings()
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        // Получаем текущие настройки из конфига
        $settings = [
            'general' => [
                'site_name' => config('app.name'),
                'site_description' => config('app.description', ''),
                'site_keywords' => config('app.keywords', ''),
                'maintenance_mode' => config('app.maintenance_mode', false),
            ],
            'server' => [
                'server_name' => config('wow.server_name', ''),
                'server_realm' => config('wow.server_realm', ''),
                'server_type' => config('wow.server_type', ''),
                'server_version' => config('wow.server_version', ''),
            ],
            'shop' => [
                'shop_enabled' => config('shop.enabled', true),
                'shop_currency_points' => config('shop.currency_points', 'Points'),
                'shop_currency_tokens' => config('shop.currency_tokens', 'Tokens'),
            ],
            'mail' => [
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'mail_username' => config('mail.mailers.smtp.username'),
                'mail_encryption' => config('mail.mailers.smtp.encryption'),
            ]
        ];

        return View::make('admin.settings', compact('settings'));
    }

    /**
     * Обновление настроек
     */
    public function updateSettings(Request $request)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $request->validate([
            'general.site_name' => 'required|string|max:255',
            'general.site_description' => 'nullable|string|max:500',
            'general.site_keywords' => 'nullable|string|max:500',
            'general.maintenance_mode' => 'boolean',
            'server.server_name' => 'required|string|max:255',
            'server.server_realm' => 'required|string|max:255',
            'server.server_type' => 'required|string|max:50',
            'server.server_version' => 'required|string|max:50',
            'shop.shop_enabled' => 'boolean',
            'shop.shop_currency_points' => 'required|string|max:50',
            'shop.shop_currency_tokens' => 'required|string|max:50',
            'mail.mail_driver' => 'required|string|max:50',
            'mail.mail_host' => 'required|string|max:255',
            'mail.mail_port' => 'required|integer|min:1|max:65535',
            'mail.mail_username' => 'nullable|string|max:255',
            'mail.mail_encryption' => 'nullable|string|max:20',
        ]);

        // Здесь можно добавить логику сохранения настроек
        // Пока просто возвращаем успех
        return redirect()->route('admin.settings')
            ->with('success', 'Настройки успешно обновлены!');
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
            $item->online = false; // По умолчанию оффлайн, можно добавить логику проверки онлайн статуса
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
