<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ArmoryController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\OnlinePlayersController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\HowToPlayController;

// Главная страница
Route::get('/', [HomeController::class, 'index'])->name('home');

// Маршруты аутентификации (временные заглушки)
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

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
