<?php

namespace App\Providers;

use App\Console\Commands\CheckSlaBreaches;
use App\Http\Middleware\EnsureRole;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindowanie serwisów (Dependency Injection)
        $this->app->singleton(\App\Services\TicketService::class);
        $this->app->singleton(\App\Services\ReportService::class);
    }

    public function boot(): void
    {
        // Middleware alias
        Route::aliasMiddleware('role', EnsureRole::class);

        // Scheduled tasks
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Sprawdzaj SLA co 15 minut
            $schedule->command('tickets:check-sla')
                ->everyFifteenMinutes()
                ->withoutOverlapping()
                ->runInBackground();

            // Wyczyść stare logi (codziennie o 02:00)
            $schedule->command('log:clear')
                ->dailyAt('02:00')
                ->environments(['production']);
        });
    }
}
