<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Директива для отображения статуса онлайн/оффлайн
        Blade::directive('onlineStatus', function ($expression) {
            return "<?php echo \App\Services\BladeDirectivesService::onlineStatus($expression); ?>";
        });

        // Директива для отображения статуса аккаунта (бан/разбан)
        Blade::directive('accountStatus', function ($expression) {
            return "<?php echo \App\Services\BladeDirectivesService::accountStatus($expression); ?>";
        });
    }
}
