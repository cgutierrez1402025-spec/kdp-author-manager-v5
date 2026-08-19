<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('kdp:sync-publications')->daily()->at('01:00')->withoutOverlapping();
        $schedule->command('promotions:check-expiring')->daily()->at('09:00')->withoutOverlapping();
        $schedule->command('tasks:check-overdue')->daily()->at('08:00')->withoutOverlapping();
    })
    ->create();
