<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use App\Services\ServerStatusService;
use App\Services\FailedLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    protected $serverStatusService;

    public function __construct(
        ServerStatusService $serverStatusService,
        FailedLoginService $failedLoginService)
    {
        $this->serverStatusService = $serverStatusService;
        $this->failedLoginService = $failedLoginService;
    }

    /**
     * Display the home page.
     */
    public function index()
    {
        // Кэшируем новости на 30 минут
        $latestNews = Cache::remember('latest_news', 1800, function () {
            return News::orderBy('is_important', 'desc')
                ->orderBy('post_date', 'desc')
                ->limit(4)
                ->get();
        });

        // Получаем данные о статусе сервера
        $serverStatusData = $this->serverStatusService->getServerStatusData();

        // Получаем последнего зарегистрированного пользователя (кэш на 10 минут)
        $lastRegisteredUser = Cache::remember('last_registered_user', 600, function () {
            return User::getLastRegisteredUser();
        });

        // Получаем данные о блокировках и статистике
        $blockedIps = $this->failedLoginService->getAllBlockedIps();
        $loginStats = $this->failedLoginService->getStatistics();

        // Очистка устаревших записей
        $this->failedLoginService->cleanupOldRecords();

        return view('wow-home', array_merge(
            compact('latestNews'), 
            $serverStatusData,
            ['lastRegisteredUser' => $lastRegisteredUser],
            ['blockedIps' => $blockedIps],
            ['loginStats' => $loginStats]
        ));
    }
}
