<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Services\ServerStatusService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $serverStatusService;

    public function __construct(ServerStatusService $serverStatusService)
    {
        $this->serverStatusService = $serverStatusService;
    }

    /**
     * Display the home page.
     */
    public function index()
    {
        $latestNews = News::orderBy('is_important', 'desc')
            ->orderBy('post_date', 'desc')
            ->limit(4)
            ->get();

        $serverStatusData = $this->serverStatusService->getServerStatusData();

        return view('wow-home', array_merge(compact('latestNews'), $serverStatusData));
    }
}
