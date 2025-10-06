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
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware(\App\Http\Middleware\Localization::class);

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware(\App\Http\Middleware\Localization::class);
Route::post('/login', [LoginController::class, 'login']);

// Registration routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware(\App\Http\Middleware\Localization::class);
Route::post('/register', [RegisterController::class, 'register']);

// Activation routes
Route::get('/activate', [App\Http\Controllers\ActivationController::class, 'showActivationForm'])->name('activate')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/activate/confirm', [App\Http\Controllers\ActivationController::class, 'activate'])->name('activate.confirm')->middleware(\App\Http\Middleware\Localization::class);
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    // Account
    Route::get('/account', [AccountController::class, 'index'])->name('account')->middleware(\App\Http\Middleware\Localization::class);
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
Route::get('/news', [NewsController::class, 'index'])->name('news.index')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show')->middleware(\App\Http\Middleware\Localization::class);

// Дополнительные маршруты для WoW сайта
Route::get('/armory', [ArmoryController::class, 'index'])->name('armory')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/armory/arena-2v2', [ArmoryController::class, 'arena2v2'])->name('armory.arena-2v2')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/armory/arena-3v3', [ArmoryController::class, 'arena3v3'])->name('armory.arena-3v3')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/armory/arena-5v5', [ArmoryController::class, 'arena5v5'])->name('armory.arena-5v5')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/armory/solo-pvp', [ArmoryController::class, 'soloPvp'])->name('armory.solo-pvp')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/armory/arenateam', [ArmoryController::class, 'arenateam'])->name('armory.arenateam')->middleware(\App\Http\Middleware\Localization::class);

// Character routes
Route::get('/character', [CharacterController::class, 'show'])->name('character.show')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/character/guid/{guid}', [CharacterController::class, 'byGuid'])->name('character.show.guid')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/character/{name}', [CharacterController::class, 'showByName'])->name('character.show.name')->middleware(\App\Http\Middleware\Localization::class);


// Shop routes
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/shop/item/{id}', [ShopController::class, 'show'])->name('shop.show')->middleware(\App\Http\Middleware\Localization::class);
Route::post('/shop/buy', [ShopController::class, 'buy'])->name('shop.buy')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/shop/history', [ShopController::class, 'history'])->name('shop.history')->middleware(\App\Http\Middleware\Localization::class);

// AJAX routes for shop
Route::get('/shop/api/items', [ShopController::class, 'getItemsByCategory'])->name('shop.api.items')->middleware(\App\Http\Middleware\Localization::class);
Route::get('/shop/api/check-availability', [ShopController::class, 'checkAvailability'])->name('shop.api.check-availability')->middleware(\App\Http\Middleware\Localization::class);

Route::get('/how-to-play', [HowToPlayController::class, 'index'])->name('how-to-play')->middleware(\App\Http\Middleware\Localization::class);

// Маршрут для смены языка
Route::get('/language/{locale}', function ($locale) {
    \Log::info('Language switch requested:', ['locale' => $locale, 'current_locale' => app()->getLocale()]);
    
    if (in_array($locale, ['en', 'ru'])) {
        // Устанавливаем локаль
        app()->setLocale($locale);
        \Log::info('Locale set to:', ['new_locale' => app()->getLocale()]);
        
        // Возвращаемся обратно с cookie
        return redirect()->back()->withCookie(cookie('locale', $locale, 60 * 24 * 30)); // 30 дней
    }
    return redirect()->back();
})->name('language.switch');

// Online players page
Route::get('/online-players', [OnlinePlayersController::class, 'index'])->name('online-players')->middleware(\App\Http\Middleware\Localization::class);

// Статические страницы
Route::get('/bugtracker', function () {
    return view('bugtracker');
})->name('bugtracker')->middleware(\App\Http\Middleware\Localization::class);

Route::get('/stream', function () {
    return view('stream');
})->name('stream')->middleware(\App\Http\Middleware\Localization::class);

// Fallback для несуществующих маршрутов
Route::fallback(function () {
    return redirect()->route('home');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
         ->name('admin.dashboard');
    Route::get('/admin/settings', [AdminController::class, 'settings'])
         ->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])
         ->name('admin.settings.update');
    
    // Управление пользователями
    Route::get('/admin/users', [AdminController::class, 'users'])
         ->name('admin.users');
    Route::get('/admin/users/{id}', [AdminController::class, 'userDetails'])
         ->name('admin.user.details');
    Route::post('/admin/users/{id}/update', [AdminController::class, 'updateUser'])
         ->name('admin.user.update');
    Route::post('/admin/users/{id}/ban', [AdminController::class, 'banUser'])
         ->name('admin.user.ban');
    Route::post('/admin/users/{id}/unban', [AdminController::class, 'unbanUser'])
         ->name('admin.user.unban');
    
    // Управление товарами магазина
    Route::get('/admin/shop-items', [AdminController::class, 'shopItems'])
         ->name('admin.shop-items');
    Route::get('/admin/shop-items/create', [AdminController::class, 'createShopItem'])
         ->name('admin.shop-item.create');
    Route::post('/admin/shop-items', [AdminController::class, 'storeShopItem'])
         ->name('admin.shop-item.store');
    Route::get('/admin/shop-items/{id}/edit', [AdminController::class, 'editShopItem'])
         ->name('admin.shop-item.edit');
    Route::put('/admin/shop-items/{id}', [AdminController::class, 'updateShopItem'])
         ->name('admin.shop-item.update');
    Route::delete('/admin/shop-items/{id}', [AdminController::class, 'deleteShopItem'])
         ->name('admin.shop-item.delete');
    
    // Управление покупками
    Route::get('/admin/purchases', [AdminController::class, 'purchases'])
         ->name('admin.purchases');
    Route::get('/admin/purchases/{id}', [AdminController::class, 'purchaseDetails'])
         ->name('admin.purchase.details');
    Route::post('/admin/purchases/{id}/refund', [AdminController::class, 'refundPurchase'])
         ->name('admin.purchase.refund');
});


Route::post('/account/sessions/destroy', [AccountController::class, 'destroySessions'])
    ->name('account.sessions.destroy');