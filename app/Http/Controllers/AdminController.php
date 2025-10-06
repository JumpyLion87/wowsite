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
        
        // Дополнительная статистика
        $onlineUsers = $this->getOnlineUsers();
        $recentPurchases = $this->getRecentPurchases();
        $serverStatus = $this->getServerStatus();
        $dailyStats = $this->getDailyStats();

        // Обработка фильтров
        $searchUsername = $request->input('search_username', '');
        $searchEmail = $request->input('search_email', '');
        $roleFilter = $request->input('role_filter', '');

        // Получение пользователей (отдельные запросы + объединение)
        $users = $this->getFilteredUsers($searchUsername ?? '', $searchEmail ?? '', $roleFilter);

        // Получение банов
        $bans = $this->getActiveBans();

        // Передача данных в шаблон
        return View::make('admin.dashboard', compact(
            'totalUsers', 'totalAccounts', 'totalChars', 'totalBans',
            'onlineUsers', 'recentPurchases', 'serverStatus', 'dailyStats',
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
     * Получить количество онлайн пользователей
     */
    protected function getOnlineUsers(): int
    {
        return DB::connection('mysql_char')->table('characters')
            ->where('online', 1)
            ->count();
    }

    /**
     * Получить последние покупки
     */
    protected function getRecentPurchases()
    {
        return DB::connection('mysql')->table('purchases')
            ->join('shop_items', 'purchases.item_id', '=', 'shop_items.item_id')
            ->join('user_currencies', 'purchases.account_id', '=', 'user_currencies.account_id')
            ->select('purchases.*', 'shop_items.name as item_name', 'user_currencies.email')
            ->orderBy('purchases.purchase_date', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Получить статус сервера
     */
    protected function getServerStatus()
    {
        try {
            // Получаем количество онлайн игроков напрямую из базы
            $onlinePlayers = DB::connection('mysql_char')->table('characters')
                ->where('online', 1)
                ->count();
            
            // Получаем время работы сервера (время последнего входа игрока)
            $lastLogin = DB::connection('mysql_char')->table('characters')
                ->where('online', 1)
                ->max('totaltime');
            
            $uptime = 'Unknown';
            if ($lastLogin) {
                $uptime = $this->formatUptime($lastLogin);
            }
            
            return [
                'online' => $onlinePlayers > 0,
                'players' => $onlinePlayers,
                'uptime' => $uptime
            ];
        } catch (\Exception $e) {
            \Log::error('Server status error: ' . $e->getMessage());
            
            return [
                'online' => false,
                'players' => 0,
                'uptime' => 'Unknown'
            ];
        }
    }
    
    /**
     * Форматировать время работы сервера
     */
    private function formatUptime($seconds)
    {
        if (!$seconds) return 'Unknown';
        
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $result = [];
        
        if ($days > 0) {
            $result[] = $days . 'd';
        }
        if ($hours > 0) {
            $result[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $result[] = $minutes . 'm';
        }
        
        return empty($result) ? '0m' : implode(' ', $result);
    }

    /**
     * Получить дневную статистику
     */
    protected function getDailyStats()
    {
        $today = now()->format('Y-m-d');
        
        return [
            'new_users' => DB::connection('mysql_auth')->table('account')
                ->whereDate('joindate', $today)
                ->count(),
            'new_purchases' => DB::connection('mysql')->table('purchases')
                ->whereDate('purchase_date', $today)
                ->count(),
            'total_revenue' => DB::connection('mysql')->table('purchases')
                ->whereDate('purchase_date', $today)
                ->sum(DB::raw('point_cost + token_cost')),
        ];
    }

    /**
     * Получить отфильтрованных пользователей (admin/moderator)
     */
    protected function getFilteredUsers(?string $searchUsername, ?string $searchEmail, ?string $roleFilter)
    {
        // 1. Получаем пользователей из user_currencies
        $userCurrencies = DB::connection('mysql')->table('user_currencies');

        if ($searchUsername && !empty(trim($searchUsername))) {
            $userCurrencies->where('username', 'like', "%{$searchUsername}%");
        }
        if ($roleFilter && !empty(trim($roleFilter))) {
            $userCurrencies->where('role', $roleFilter);
        }

        $userCurrencies = $userCurrencies->whereIn('role', ['admin', 'moderator'])
            ->orderBy('last_updated', 'desc')
            ->limit(5)
            ->get();

        // 2. Получаем почты из account (mysql_auth)
        $accountQuery = DB::connection('mysql_auth')->table('account')
            ->whereIn('id', $userCurrencies->pluck('account_id'));
            
        // Добавляем фильтр по email если указан
        if ($searchEmail && !empty(trim($searchEmail))) {
            $accountQuery->where('email', 'like', "%{$searchEmail}%");
        }
        
        $accounts = $accountQuery->pluck('email', 'id');

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

    /**
     * Страница управления пользователями
     */
    public function users(Request $request)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        // Параметры поиска и фильтрации
        $searchUsername = $request->input('search_username', '');
        $searchEmail = $request->input('search_email', '');
        $roleFilter = $request->input('role_filter', '');
        $statusFilter = $request->input('status_filter', '');
        $perPage = $request->input('per_page', 20);

        // Получение пользователей с фильтрацией
        $users = $this->getUsersWithFilters($searchUsername, $searchEmail, $roleFilter, $statusFilter, $perPage);

        // Статистика
        $stats = [
            'total_users' => $this->getTotalUsers(),
            'online_users' => $this->getOnlineUsers(),
            'banned_users' => $this->getTotalBans(),
            'admin_users' => $this->getAdminUsersCount(),
        ];

        return View::make('admin.users', compact(
            'users', 'stats', 'searchUsername', 'searchEmail', 
            'roleFilter', 'statusFilter', 'perPage'
        ));
    }

    /**
     * Детальная информация о пользователе
     */
    public function userDetails($id)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        // Получение информации о пользователе
        $user = $this->getUserDetails($id);
        
        if (!$user) {
            return redirect()->route('admin.users')
                ->with('error', 'Пользователь не найден');
        }

        // Получение персонажей пользователя
        $characters = $this->getUserCharacters($id);
        
        // Получение истории покупок
        $purchases = $this->getUserPurchases($id);
        
        // Получение истории банов
        $banHistory = $this->getUserBanHistory($id);

        return View::make('admin.user-details', compact(
            'user', 'characters', 'purchases', 'banHistory'
        ));
    }

    /**
     * Обновление пользователя
     */
    public function updateUser(Request $request, $id)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:user,moderator,admin',
            'points' => 'required|integer|min:0',
            'tokens' => 'required|integer|min:0',
        ]);

        try {
            // Обновление в user_currencies
            DB::connection('mysql')->table('user_currencies')
                ->where('account_id', $id)
                ->update([
                    'username' => $request->username,
                    'role' => $request->role,
                    'points' => $request->points,
                    'tokens' => $request->tokens,
                    'last_updated' => now(),
                ]);

            // Обновление в account
            DB::connection('mysql_auth')->table('account')
                ->where('id', $id)
                ->update([
                    'email' => $request->email,
                ]);

            return redirect()->route('admin.user.details', $id)
                ->with('success', 'Пользователь успешно обновлен');

        } catch (\Exception $e) {
            \Log::error('User update error: ' . $e->getMessage());
            return redirect()->route('admin.user.details', $id)
                ->with('error', 'Ошибка при обновлении пользователя');
        }
    }

    /**
     * Бан пользователя
     */
    public function banUser(Request $request, $id)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $request->validate([
            'ban_reason' => 'required|string|max:500',
            'ban_duration' => 'nullable|integer|min:1',
        ]);

        try {
            $banDate = now();
            $unbanDate = $request->ban_duration ? 
                $banDate->copy()->addDays($request->ban_duration) : null;

            // Добавление бана
            DB::connection('mysql_auth')->table('account_banned')->insert([
                'id' => $id,
                'bandate' => $banDate->timestamp,
                'unbandate' => $unbanDate ? $unbanDate->timestamp : null,
                'bannedby' => Auth::id(),
                'banreason' => $request->ban_reason,
                'active' => 1,
            ]);

            return redirect()->route('admin.user.details', $id)
                ->with('success', 'Пользователь забанен');

        } catch (\Exception $e) {
            \Log::error('User ban error: ' . $e->getMessage());
            return redirect()->route('admin.user.details', $id)
                ->with('error', 'Ошибка при бане пользователя');
        }
    }

    /**
     * Разбан пользователя
     */
    public function unbanUser($id)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        try {
            // Деактивация всех активных банов
            DB::connection('mysql_auth')->table('account_banned')
                ->where('id', $id)
                ->where('active', 1)
                ->update(['active' => 0]);

            return redirect()->route('admin.user.details', $id)
                ->with('success', 'Пользователь разбанен');

        } catch (\Exception $e) {
            \Log::error('User unban error: ' . $e->getMessage());
            return redirect()->route('admin.user.details', $id)
                ->with('error', 'Ошибка при разбане пользователя');
        }
    }

    /**
     * Получить пользователей с фильтрацией
     */
    protected function getUsersWithFilters($searchUsername, $searchEmail, $roleFilter, $statusFilter, $perPage)
    {
        // Получаем пользователей из user_currencies
        $userCurrencies = DB::connection('mysql')->table('user_currencies');

        // Фильтры
        if ($searchUsername) {
            $userCurrencies->where('username', 'like', "%{$searchUsername}%");
        }
        if ($roleFilter) {
            $userCurrencies->where('role', $roleFilter);
        }

        $userCurrencies = $userCurrencies->orderBy('last_updated', 'desc')
            ->paginate($perPage);

        // Получаем email и joindate из account (mysql_auth)
        $accountIds = $userCurrencies->pluck('account_id');
        $accounts = DB::connection('mysql_auth')->table('account')
            ->whereIn('id', $accountIds)
            ->select('id', 'email', 'joindate')
            ->get()
            ->keyBy('id');

        // Получаем информацию о банах
        $bans = DB::connection('mysql_auth')->table('account_banned')
            ->whereIn('id', $accountIds)
            ->where('active', 1)
            ->get()
            ->keyBy('id');

        // Фильтрация по email если указан
        if ($searchEmail) {
            $filteredAccountIds = DB::connection('mysql_auth')->table('account')
                ->whereIn('id', $accountIds)
                ->where('email', 'like', "%{$searchEmail}%")
                ->pluck('id');
            
            $userCurrencies = $userCurrencies->filter(function($user) use ($filteredAccountIds) {
                return $filteredAccountIds->contains($user->account_id);
            });
        }

        // Объединяем данные
        $userCurrencies->getCollection()->transform(function ($user) use ($accounts, $bans) {
            $account = $accounts[$user->account_id] ?? null;
            $ban = $bans[$user->account_id] ?? null;
            
            $user->email = $account->email ?? 'Не найдено';
            $user->joindate = $account->joindate ?? null;
            $user->bandate = $ban->bandate ?? null;
            $user->banreason = $ban->banreason ?? null;
            return $user;
        });

        return $userCurrencies;
    }

    /**
     * Получить детальную информацию о пользователе
     */
    protected function getUserDetails($id)
    {
        // Получаем пользователя из user_currencies
        $user = DB::connection('mysql')->table('user_currencies')
            ->where('account_id', $id)
            ->first();

        if (!$user) {
            return null;
        }

        // Получаем email и joindate из account (mysql_auth)
        $account = DB::connection('mysql_auth')->table('account')
            ->where('id', $id)
            ->first();

        // Получаем информацию о банах
        $ban = DB::connection('mysql_auth')->table('account_banned')
            ->where('id', $id)
            ->where('active', 1)
            ->first();

        // Получаем информацию о роли игрового аккаунта
        $accountAccess = DB::connection('mysql_auth')->table('account_access')
            ->where('id', $id)
            ->first();

        // Объединяем данные
        $user->email = $account->email ?? null;
        $user->joindate = $account->joindate ?? null;
        $user->bandate = $ban->bandate ?? null;
        $user->banreason = $ban->banreason ?? null;
        $user->gmlevel = $accountAccess->gmlevel ?? 0;
        $user->gmcomment = $accountAccess->comment ?? null;

        return $user;
    }

    /**
     * Получить персонажей пользователя
     */
    protected function getUserCharacters($accountId)
    {
        return DB::connection('mysql_char')->table('characters')
            ->where('account', $accountId)
            ->select('guid', 'name', 'race', 'class', 'level', 'online', 'totaltime')
            ->orderBy('level', 'desc')
            ->get();
    }

    /**
     * Получить покупки пользователя
     */
    protected function getUserPurchases($accountId)
    {
        return DB::connection('mysql')->table('purchases')
            ->join('shop_items', 'purchases.item_id', '=', 'shop_items.item_id')
            ->select('purchases.*', 'shop_items.name as item_name')
            ->where('purchases.account_id', $accountId)
            ->orderBy('purchases.purchase_date', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Получить историю банов пользователя
     */
    protected function getUserBanHistory($accountId)
    {
        return DB::connection('mysql_auth')->table('account_banned')
            ->where('id', $accountId)
            ->orderBy('bandate', 'desc')
            ->get();
    }

    /**
     * Получить количество админов
     */
    protected function getAdminUsersCount()
    {
        return DB::connection('mysql')->table('user_currencies')
            ->whereIn('role', ['admin', 'moderator'])
            ->count();
    }

    /**
     * Получить название роли игрового аккаунта по уровню GM
     */
    protected function getGameRoleName($gmlevel)
    {
        switch ($gmlevel) {
            case 0:
                return 'Игрок';
            case 1:
                return 'Модератор (Уровень 1)';
            case 2:
                return 'ГМ (Уровень 2)';
            case 3:
                return 'ГМ (Уровень 3)';
            case 4:
                return 'ГМ (Уровень 4)';
            case 5:
                return 'ГМ (Уровень 5)';
            case 6:
                return 'ГМ (Уровень 6)';
            default:
                return 'Неизвестная роль';
        }
    }

    /**
     * Получить CSS класс для роли игрового аккаунта
     */
    protected function getGameRoleClass($gmlevel)
    {
        switch ($gmlevel) {
            case 0:
                return 'game-role-player';
            case 1:
                return 'game-role-moderator';
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                return 'game-role-gm';
            default:
                return 'game-role-unknown';
        }
    }

    /**
     * Страница управления товарами магазина
     */
    public function shopItems(Request $request)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        // Параметры поиска и фильтрации
        $searchName = $request->input('search_name', '');
        $categoryFilter = $request->input('category_filter', '');
        $perPage = $request->input('per_page', 20);

        // Получение товаров с фильтрацией
        $items = $this->getShopItemsWithFilters($searchName, $categoryFilter, $perPage);

        // Статистика
        $stats = [
            'total_items' => $this->getTotalShopItems(),
            'total_categories' => $this->getTotalCategories(),
            'total_purchases' => $this->getTotalPurchases(),
            'total_revenue' => $this->getTotalRevenue(),
        ];

        return View::make('admin.shop-items', compact(
            'items', 'stats', 'searchName', 'categoryFilter', 'perPage'
        ));
    }

    /**
     * Страница создания товара
     */
    public function createShopItem()
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        return View::make('admin.shop-item-create');
    }

    /**
     * Сохранение нового товара
     */
    public function storeShopItem(Request $request)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'point_cost' => 'required|integer|min:0',
            'token_cost' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'entry' => 'nullable|integer',
            'gold_amount' => 'nullable|integer|min:0',
            'level_boost' => 'nullable|integer|min:1|max:80',
            'image' => 'nullable|string|max:255',
        ]);

        try {
            $itemId = DB::connection('mysql')->table('shop_items')->insertGetId([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'point_cost' => $request->point_cost,
                'token_cost' => $request->token_cost,
                'stock' => $request->stock,
                'entry' => $request->entry,
                'gold_amount' => $request->gold_amount,
                'level_boost' => $request->level_boost,
                'image' => $request->image,
                'last_updated' => now(),
            ]);

            return redirect()->route('admin.shop-items')
                ->with('success', 'Товар успешно создан');

        } catch (\Exception $e) {
            \Log::error('Shop item creation error: ' . $e->getMessage());
            return redirect()->route('admin.shop-item.create')
                ->with('error', 'Ошибка при создании товара');
        }
    }

    /**
     * Страница редактирования товара
     */
    public function editShopItem($id)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $item = DB::connection('mysql')->table('shop_items')
            ->where('item_id', $id)
            ->first();

        if (!$item) {
            return redirect()->route('admin.shop-items')
                ->with('error', 'Товар не найден');
        }

        return View::make('admin.shop-item-edit', compact('item'));
    }

    /**
     * Обновление товара
     */
    public function updateShopItem(Request $request, $id)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'point_cost' => 'required|integer|min:0',
            'token_cost' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'entry' => 'nullable|integer',
            'gold_amount' => 'nullable|integer|min:0',
            'level_boost' => 'nullable|integer|min:1|max:80',
            'image' => 'nullable|string|max:255',
        ]);

        try {
            DB::connection('mysql')->table('shop_items')
                ->where('item_id', $id)
                ->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'category' => $request->category,
                    'point_cost' => $request->point_cost,
                    'token_cost' => $request->token_cost,
                    'stock' => $request->stock,
                    'entry' => $request->entry,
                    'gold_amount' => $request->gold_amount,
                    'level_boost' => $request->level_boost,
                    'image' => $request->image,
                    'last_updated' => now(),
                ]);

            return redirect()->route('admin.shop-items')
                ->with('success', 'Товар успешно обновлен');

        } catch (\Exception $e) {
            \Log::error('Shop item update error: ' . $e->getMessage());
            return redirect()->route('admin.shop-item.edit', $id)
                ->with('error', 'Ошибка при обновлении товара');
        }
    }

    /**
     * Удаление товара
     */
    public function deleteShopItem($id)
    {
        // Проверка авторизации
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        try {
            DB::connection('mysql')->table('shop_items')
                ->where('item_id', $id)
                ->delete();

            return redirect()->route('admin.shop-items')
                ->with('success', 'Товар успешно удален');

        } catch (\Exception $e) {
            \Log::error('Shop item deletion error: ' . $e->getMessage());
            return redirect()->route('admin.shop-items')
                ->with('error', 'Ошибка при удалении товара');
        }
    }

    /**
     * Получить товары с фильтрацией
     */
    protected function getShopItemsWithFilters($searchName, $categoryFilter, $perPage)
    {
        $query = DB::connection('mysql')->table('shop_items');

        // Фильтры
        if ($searchName) {
            $query->where('name', 'like', "%{$searchName}%");
        }
        if ($categoryFilter) {
            $query->where('category', $categoryFilter);
        }

        return $query->orderBy('last_updated', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить общее количество товаров
     */
    protected function getTotalShopItems()
    {
        return DB::connection('mysql')->table('shop_items')->count();
    }

    /**
     * Получить количество категорий
     */
    protected function getTotalCategories()
    {
        return DB::connection('mysql')->table('shop_items')
            ->distinct('category')
            ->count('category');
    }

    /**
     * Получить общее количество покупок
     */
    protected function getTotalPurchases()
    {
        return DB::connection('mysql')->table('purchases')->count();
    }

    /**
     * Получить общий доход
     */
    protected function getTotalRevenue()
    {
        return DB::connection('mysql')->table('purchases')
            ->sum(DB::raw('point_cost + token_cost'));
    }

    /**
     * Управление покупками: список
     */
    public function purchases(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $perPage = (int) $request->input('per_page', 20);
        $search = trim((string) $request->input('search', ''));

        $query = DB::connection('mysql')->table('purchases')
            ->join('shop_items', 'purchases.item_id', '=', 'shop_items.item_id')
            ->join('user_currencies', 'purchases.account_id', '=', 'user_currencies.account_id')
            ->select(
                'purchases.*',
                'shop_items.name as item_name',
                'user_currencies.username',
                'user_currencies.email'
            )
            ->orderBy('purchases.purchase_date', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('shop_items.name', 'like', "%{$search}%")
                  ->orWhere('user_currencies.username', 'like', "%{$search}%")
                  ->orWhere('user_currencies.email', 'like', "%{$search}%");
            });
        }

        $purchases = $query->paginate($perPage);

        return View::make('admin.purchases', compact('purchases', 'perPage', 'search'));
    }

    /**
     * Детали покупки
     */
    public function purchaseDetails($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $purchase = DB::connection('mysql')->table('purchases')
            ->where('purchase_id', $id)
            ->join('shop_items', 'purchases.item_id', '=', 'shop_items.item_id')
            ->join('user_currencies', 'purchases.account_id', '=', 'user_currencies.account_id')
            ->select('purchases.*', 'shop_items.name as item_name', 'shop_items.category', 'user_currencies.username', 'user_currencies.email')
            ->first();

        if (!$purchase) {
            return redirect()->route('admin.purchases')
                ->with('error', 'Покупка не найдена');
        }

        return View::make('admin.purchase-details', compact('purchase'));
    }

    /**
     * Возврат покупки (простой вариант): вернуть очки/токены и удалить запись покупки
     */
    public function refundPurchase($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        // Загружаем покупку
        $purchase = DB::connection('mysql')->table('purchases')
            ->where('purchase_id', $id)
            ->first();

        if (!$purchase) {
            return redirect()->route('admin.purchases')
                ->with('error', 'Покупка не найдена');
        }

        try {
            // Простой возврат: вернуть валюту пользователю и удалить запись о покупке
            DB::connection('mysql')->transaction(function () use ($purchase) {
                // Вернуть очки и токены
                DB::connection('mysql')->table('user_currencies')
                    ->where('account_id', $purchase->account_id)
                    ->update([
                        'points' => DB::raw('points + ' . (int) $purchase->point_cost),
                        'tokens' => DB::raw('tokens + ' . (int) $purchase->token_cost),
                        'last_updated' => now(),
                    ]);

                // Удалить запись о покупке
                DB::connection('mysql')->table('purchases')
                    ->where('purchase_id', $purchase->purchase_id)
                    ->delete();
            });

            return redirect()->route('admin.purchases')
                ->with('success', 'Покупка возвращена, средства зачислены пользователю');

        } catch (\Exception $e) {
            \Log::error('Refund error: ' . $e->getMessage());
            return redirect()->route('admin.purchase.details', $id)
                ->with('error', 'Ошибка при возврате покупки');
        }
    }
}
