<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ArmoryController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\OnlinePlayersController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\HowToPlayController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VoteController;

// Главная страница
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Registration routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Activation routes
Route::get('/activate', [App\Http\Controllers\ActivationController::class, 'showActivationForm'])->name('activate');
Route::get('/activate/confirm', [App\Http\Controllers\ActivationController::class, 'activate'])->name('activate.confirm');
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    // Account
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::post('/account/email', [AccountController::class, 'updateEmail'])->name('account.email');
    Route::post('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
    Route::post('/account/avatar', [AccountController::class, 'changeAvatar'])->name('account.avatar');
    Route::post('/account/teleport', [AccountController::class, 'teleportCharacter'])->name('account.teleport');
    
    // Voting
    Route::get('/vote', [VoteController::class, 'redirectToVote'])->name('vote.redirect');
    Route::post('/vote/check', [VoteController::class, 'checkVote'])->name('vote.check');
    Route::get('/vote/info', [VoteController::class, 'getVoteInfo'])->name('vote.info');
});

Route::get('/download', function () {
    return view('download');
})->name('download');

// Маршруты для новостей
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

// Дополнительные маршруты для WoW сайта
Route::get('/armory', [ArmoryController::class, 'index'])->name('armory');
Route::get('/armory/arena-2v2', [ArmoryController::class, 'arena2v2'])->name('armory.arena-2v2');
Route::get('/armory/arena-3v3', [ArmoryController::class, 'arena3v3'])->name('armory.arena-3v3');
Route::get('/armory/arena-5v5', [ArmoryController::class, 'arena5v5'])->name('armory.arena-5v5');
Route::get('/armory/solo-pvp', [ArmoryController::class, 'soloPvp'])->name('armory.solo-pvp');
Route::get('/armory/arenateam', [ArmoryController::class, 'arenateam'])->name('armory.arenateam');

// Character routes
Route::get('/character', [CharacterController::class, 'show'])->name('character.show');
Route::get('/character/guid/{guid}', [CharacterController::class, 'byGuid'])->name('character.show.guid');
Route::get('/character/{name}', [CharacterController::class, 'showByName'])->name('character.show.name');


Route::get('/shop', function () {
    return view('shop');
})->name('shop');

Route::get('/how-to-play', [HowToPlayController::class, 'index'])->name('how-to-play');

// Маршрут для смены языка
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ru'])) {
        return redirect()->back()->withCookie(cookie('locale', $locale, 60 * 24 * 30)); // 30 дней
    }
    return redirect()->back();
})->name('language.switch');

// Online players page
Route::get('/online-players', [OnlinePlayersController::class, 'index'])->name('online-players');

// Статические страницы
Route::get('/bugtracker', function () {
    return view('bugtracker');
})->name('bugtracker');

Route::get('/stream', function () {
    return view('stream');
})->name('stream');

// Fallback для несуществующих маршрутов
Route::fallback(function () {
    return redirect()->route('home');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
         ->name('admin.dashboard');
});


Route::post('/account/sessions/destroy', [AccountController::class, 'destroySessions'])
    ->name('account.sessions.destroy');