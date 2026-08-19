<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, string $ability): ?bool {
            if ($user->hasRole('admin')) {
                return true;
            }

            return $user->hasPermission($ability) ? true : null;
        });

    }
}
