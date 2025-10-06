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

    /**
     * Управление персонажами: список
     */
    public function characters(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $perPage = (int) $request->input('per_page', 20);
        $search = trim((string) $request->input('search', ''));
        $levelFilter = $request->input('level_filter', '');
        $classFilter = $request->input('class_filter', '');
        $onlineFilter = $request->input('online_filter', '');

        $query = DB::connection('mysql_char')->table('characters')
            ->select(
                'characters.guid',
                'characters.name',
                'characters.race',
                'characters.class',
                'characters.level',
                'characters.online',
                'characters.totaltime',
                'characters.money',
                'characters.position_x',
                'characters.position_y',
                'characters.position_z',
                'characters.map',
                'characters.zone',
                'characters.account'
            )
            ->orderBy('characters.level', 'desc');

        if ($search !== '') {
            $query->where('characters.name', 'like', "%{$search}%");
        }

        if ($levelFilter !== '') {
            $query->where('characters.level', '>=', (int) $levelFilter);
        }

        if ($classFilter !== '') {
            $query->where('characters.class', $classFilter);
        }

        if ($onlineFilter !== '') {
            $query->where('characters.online', $onlineFilter === 'online' ? 1 : 0);
        }

        $characters = $query->paginate($perPage);

        // Получаем информацию об аккаунтах
        $accountIds = $characters->pluck('account')->unique();
        $accounts = DB::connection('mysql_auth')->table('account')
            ->whereIn('id', $accountIds)
            ->pluck('username', 'id');

        // Добавляем информацию об аккаунтах к персонажам
        $characters->getCollection()->transform(function ($character) use ($accounts) {
            $character->account_name = $accounts[$character->account] ?? 'Unknown';
            return $character;
        });

        // Статистика
        $stats = [
            'total_characters' => DB::connection('mysql_char')->table('characters')->count(),
            'online_characters' => DB::connection('mysql_char')->table('characters')->where('online', 1)->count(),
            'max_level' => DB::connection('mysql_char')->table('characters')->max('level'),
            'total_money' => DB::connection('mysql_char')->table('characters')->sum('money'),
        ];

        return View::make('admin.characters', compact(
            'characters', 'stats', 'perPage', 'search', 'levelFilter', 'classFilter', 'onlineFilter'
        ));
    }

    /**
     * Детали персонажа
     */
    public function characterDetails($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $character = DB::connection('mysql_char')->table('characters')
            ->where('characters.guid', $id)
            ->first();

        if ($character) {
            // Получаем информацию об аккаунте
            $account = DB::connection('mysql_auth')->table('account')
                ->where('id', $character->account)
                ->first();
            
            if ($account) {
                $character->account_name = $account->username;
                $character->email = $account->email;
            } else {
                $character->account_name = 'Unknown';
                $character->email = 'Unknown';
            }
        }

        if (!$character) {
            return redirect()->route('admin.characters')
                ->with('error', 'Персонаж не найден');
        }

        return View::make('admin.character-details', compact('character'));
    }

    /**
     * Телепорт персонажа
     */
    public function teleportCharacter(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $request->validate([
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'z' => 'required|numeric',
            'map' => 'required|integer',
            'zone' => 'nullable|integer',
        ]);

        try {
            DB::connection('mysql_char')->table('characters')
                ->where('guid', $id)
                ->update([
                    'position_x' => $request->x,
                    'position_y' => $request->y,
                    'position_z' => $request->z,
                    'map' => $request->map,
                    'zone' => $request->zone ?? 0,
                ]);

            return redirect()->route('admin.character.details', $id)
                ->with('success', 'Персонаж телепортирован');

        } catch (\Exception $e) {
            \Log::error('Character teleport error: ' . $e->getMessage());
            return redirect()->route('admin.character.details', $id)
                ->with('error', 'Ошибка при телепорте персонажа');
        }
    }

    /**
     * Кик персонажа (отключение)
     */
    public function kickCharacter(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $request->validate([
            'kick_type' => 'required|in:soft,hard,force',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            $character = DB::connection('mysql_char')->table('characters')
                ->where('guid', $id)
                ->where('online', 1)
                ->first();

            if (!$character) {
                return redirect()->route('admin.character.details', $id)
                    ->with('error', 'Персонаж не найден или не в игре');
            }

            $kickType = $request->input('kick_type');
            $reason = $request->input('reason', 'Admin kick');

            switch ($kickType) {
                case 'soft':
                    // Мягкий кик - только обновляем статус в базе
                    DB::connection('mysql_char')->table('characters')
                        ->where('guid', $id)
                        ->update(['online' => 0]);
                    
                    $message = 'Мягкий кик применен (только статус в базе данных)';
                    break;

                case 'hard':
                    // Жесткий кик - обновляем статус + устанавливаем время выхода
                    DB::connection('mysql_char')->table('characters')
                        ->where('guid', $id)
                        ->update([
                            'online' => 0,
                            'logout_time' => time()
                        ]);
                    
                    $message = 'Жесткий кик применен (статус + время выхода)';
                    break;

                case 'force':
                    // Принудительный кик - агрессивные методы
                    DB::connection('mysql_char')->table('characters')
                        ->where('guid', $id)
                        ->update([
                            'online' => 0,
                            'logout_time' => time(),
                            'last_login' => time() - 3600, // Устанавливаем время последнего входа на час назад
                            'totaltime' => 0 // Сбрасываем время игры
                        ]);
                    
                    // Дополнительные методы принудительного кика
                    $this->forceKickCharacter($character, $reason);
                    
                    $message = 'Принудительный кик применен (агрессивные методы)';
                    break;
            }

            // Логируем действие
            \Log::info("Character kick: {$character->name} (GUID: {$id}) by admin " . Auth::user()->username . 
                      " - Type: {$kickType}, Reason: {$reason}");

            return redirect()->route('admin.character.details', $id)
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Character kick error: ' . $e->getMessage());
            return redirect()->route('admin.character.details', $id)
                ->with('error', 'Ошибка при отключении персонажа');
        }
    }

    /**
     * Принудительный кик персонажа (агрессивные методы)
     */
    private function forceKickCharacter($character, $reason)
    {
        try {
            // Метод 1: Попытка SOAP (если настроен)
            $soapResult = $this->sendKickCommand($character->name, $reason);
            
            // Метод 2: Обновление сессии в базе данных
            $this->invalidateCharacterSession($character->guid);
            
            // Метод 3: Сброс позиции персонажа (телепорт в безопасное место)
            $this->teleportToSafeLocation($character->guid);
            
            // Метод 4: Очистка кэша персонажа
            $this->clearCharacterCache($character->guid);
            
            // Метод 5: Принудительное отключение аккаунта
            $this->forceDisconnectAccount($character->account);
            
            // Метод 6: Временный бан аккаунта (на 1 минуту)
            $this->temporaryBanAccount($character->account, $reason);
            
            \Log::info("Force kick applied to character {$character->name} (GUID: {$character->guid}) - Reason: {$reason}");
            
        } catch (\Exception $e) {
            \Log::error('Force kick error: ' . $e->getMessage());
        }
    }

    /**
     * Инвалидация сессии персонажа
     */
    private function invalidateCharacterSession($characterGuid)
    {
        try {
            // Обновляем время последнего входа
            DB::connection('mysql_char')->table('characters')
                ->where('guid', $characterGuid)
                ->update([
                    'last_login' => time() - 3600,
                    'logout_time' => time(),
                    'online' => 0
                ]);
                
            // Если есть таблица сессий, очищаем её
            if (DB::connection('mysql_char')->getSchemaBuilder()->hasTable('character_sessions')) {
                DB::connection('mysql_char')->table('character_sessions')
                    ->where('character_guid', $characterGuid)
                    ->delete();
            }
                    
        } catch (\Exception $e) {
            \Log::error('Session invalidation error: ' . $e->getMessage());
        }
    }

    /**
     * Телепорт персонажа в безопасное место
     */
    private function teleportToSafeLocation($characterGuid)
    {
        try {
            // Телепорт в Оргриммар (безопасная зона)
            DB::connection('mysql_char')->table('characters')
                ->where('guid', $characterGuid)
                ->update([
                    'position_x' => 1676.21,
                    'position_y' => -4315.29,
                    'position_z' => 61.52,
                    'map' => 1, // Kalimdor
                    'zone' => 1637, // Orgrimmar
                    'orientation' => 1.58
                ]);
                    
        } catch (\Exception $e) {
            \Log::error('Safe teleport error: ' . $e->getMessage());
        }
    }

    /**
     * Очистка кэша персонажа
     */
    private function clearCharacterCache($characterGuid)
    {
        try {
            // Очищаем кэш в Redis (если используется)
            if (config('cache.default') === 'redis') {
                $redis = app('redis');
                $redis->del("character:{$characterGuid}");
                $redis->del("character:{$characterGuid}:*");
            }
                    
        } catch (\Exception $e) {
            \Log::error('Cache clear error: ' . $e->getMessage());
        }
    }

    /**
     * Принудительное отключение аккаунта
     */
    private function forceDisconnectAccount($accountId)
    {
        try {
            // Обновляем время последнего входа аккаунта
            DB::connection('mysql_auth')->table('account')
                ->where('id', $accountId)
                ->update([
                    'last_login' => time() - 3600,
                    'last_ip' => '127.0.0.1' // Сбрасываем IP
                ]);
                
            // Если есть таблица сессий аккаунта, очищаем её
            if (DB::connection('mysql_auth')->getSchemaBuilder()->hasTable('account_sessions')) {
                DB::connection('mysql_auth')->table('account_sessions')
                    ->where('account_id', $accountId)
                    ->delete();
            }
            
            // Обновляем все персонажи этого аккаунта
            DB::connection('mysql_char')->table('characters')
                ->where('account', $accountId)
                ->update([
                    'online' => 0,
                    'logout_time' => time()
                ]);
                    
        } catch (\Exception $e) {
            \Log::error('Account disconnect error: ' . $e->getMessage());
        }
    }

    /**
     * Временный бан аккаунта для принудительного отключения
     */
    private function temporaryBanAccount($accountId, $reason)
    {
        try {
            // Создаем временный бан на 1 минуту
            $banTime = time() + 60; // 1 минута
            
            DB::connection('mysql_auth')->table('account_banned')->insert([
                'id' => $accountId,
                'bandate' => time(),
                'unbandate' => $banTime,
                'bannedby' => 'Admin Panel',
                'banreason' => "Force kick: {$reason}",
                'active' => 1
            ]);
            
            // Планируем автоматическое снятие бана через 1 минуту
            $this->scheduleUnban($accountId, $banTime);
                    
        } catch (\Exception $e) {
            \Log::error('Temporary ban error: ' . $e->getMessage());
        }
    }

    /**
     * Планирование автоматического снятия бана
     */
    private function scheduleUnban($accountId, $unbanTime)
    {
        try {
            // Добавляем задачу в очередь (если используется)
            if (config('queue.default') !== 'sync') {
                \App\Jobs\UnbanAccount::dispatch($accountId)->delay(now()->addSeconds(60));
            } else {
                // Если очередь не настроена, создаем задачу в базе данных
                DB::connection('mysql')->table('scheduled_tasks')->insert([
                    'task' => 'unban_account',
                    'data' => json_encode(['account_id' => $accountId]),
                    'scheduled_at' => date('Y-m-d H:i:s', $unbanTime),
                    'created_at' => now(),
                    'status' => 'pending'
                ]);
            }
                    
        } catch (\Exception $e) {
            \Log::error('Schedule unban error: ' . $e->getMessage());
        }
    }

    /**
     * Отправить команду кика на игровой сервер через SOAP
     */
    private function sendKickCommand($characterName, $reason)
    {
        try {
            $soapService = app(\App\Services\SoapService::class);
            $result = $soapService->kickPlayer($characterName, $reason);
            
            \Log::info("SOAP kick command sent: {$characterName} - Result: " . json_encode($result));
            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to send SOAP kick command: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверить статус SOAP соединения
     */
    public function checkSoapConnection()
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            $soapService = app(\App\Services\SoapService::class);
            $result = $soapService->checkConnection();
            
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка соединения: ' . $e->getMessage(),
                'response' => null
            ]);
        }
    }

    /**
     * Забанить аккаунт через SOAP
     */
    public function banAccountViaSoap(Request $request, $accountId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $request->validate([
            'duration' => 'required|string',
            'reason' => 'required|string|max:255'
        ]);

        try {
            $account = DB::connection('mysql_auth')->table('account')
                ->where('id', $accountId)
                ->first();

            if (!$account) {
                return redirect()->back()->with('error', 'Аккаунт не найден');
            }

            $soapService = app(\App\Services\SoapService::class);
            $result = $soapService->banAccount($account->username, $request->duration, $request->reason);

            \Log::info("SOAP ban account: {$account->username} - Result: " . json_encode($result));

            return redirect()->back()->with('success', 'Аккаунт забанен через SOAP');

        } catch (\Exception $e) {
            \Log::error('SOAP ban account error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при бане аккаунта: ' . $e->getMessage());
        }
    }

    /**
     * Разбанить аккаунт через SOAP
     */
    public function unbanAccountViaSoap($accountId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        try {
            $account = DB::connection('mysql_auth')->table('account')
                ->where('id', $accountId)
                ->first();

            if (!$account) {
                return redirect()->back()->with('error', 'Аккаунт не найден');
            }

            $soapService = app(\App\Services\SoapService::class);
            $result = $soapService->unbanAccount($account->username);

            \Log::info("SOAP unban account: {$account->username} - Result: " . json_encode($result));

            return redirect()->back()->with('success', 'Аккаунт разбанен через SOAP');

        } catch (\Exception $e) {
            \Log::error('SOAP unban account error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при разбане аккаунта: ' . $e->getMessage());
        }
    }

    /**
     * Отправить объявление через SOAP
     */
    public function announceViaSoap(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin(Auth::id())) {
            return redirect('/login');
        }

        $request->validate([
            'message' => 'required|string|max:255'
        ]);

        try {
            $soapService = app(\App\Services\SoapService::class);
            $result = $soapService->announce($request->message);

            \Log::info("SOAP announce: {$request->message} - Result: " . json_encode($result));

            return redirect()->back()->with('success', 'Объявление отправлено через SOAP');

        } catch (\Exception $e) {
            \Log::error('SOAP announce error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при отправке объявления: ' . $e->getMessage());
        }
    }

    /**
     * Конвертировать деньги из меди в золото
     */
    public static function formatMoney($copper)
    {
        $gold = floor($copper / 10000);
        $silver = floor(($copper % 10000) / 100);
        $copper = $copper % 100;
        
        $result = '';
        if ($gold > 0) {
            $result .= $gold . 'g';
        }
        if ($silver > 0) {
            $result .= ($result ? ' ' : '') . $silver . 's';
        }
        if ($copper > 0) {
            $result .= ($result ? ' ' : '') . $copper . 'c';
        }
        
        return $result ?: '0c';
    }

    /**
     * Получить название расы по ID
     */
    public static function getRaceName($raceId)
    {
        $raceKeys = [
            1 => 'human', 2 => 'orc', 3 => 'dwarf', 4 => 'night_elf',
            5 => 'undead', 6 => 'tauren', 7 => 'gnome', 8 => 'troll',
            10 => 'blood_elf', 11 => 'draenei'
        ];
        
        $key = $raceKeys[$raceId] ?? null;
        return $key ? __('races.' . $key) : 'Unknown';
    }

    /**
     * Получить название класса по ID
     */
    public static function getClassName($classId)
    {
        $classKeys = [
            1 => 'warrior', 2 => 'paladin', 3 => 'hunter', 4 => 'rogue',
            5 => 'priest', 6 => 'death_knight', 7 => 'shaman', 8 => 'mage',
            9 => 'warlock', 10 => 'monk', 11 => 'druid', 12 => 'demon_hunter'
        ];
        
        $key = $classKeys[$classId] ?? null;
        return $key ? __('classes.' . $key) : 'Unknown';
    }

    /**
     * Получить название карты по ID
     */
    public static function getMapName($mapId)
    {
        return __('maps.' . $mapId, [], 'Map ID: ' . $mapId);
    }

    /**
     * Форматировать время игры
     */
    public static function formatPlaytime($seconds)
    {
        if (!$seconds) return '0m';
        
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
}
