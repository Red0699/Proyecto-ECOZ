<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Support\AlertsBuilder;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
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
        //
        Paginator::useBootstrapFive();

        View::composer([
            'layouts.navbar.navbar',
            'layouts/navbar/navbar',
            'layouts.*.navbar',              // p.ej. layouts/sections/navbar
            'layouts.sections.navbar',       // si existe
            'layouts.sections.navbar.*',
        ], function ($view) {
            $user = Auth::user();
            $navAlerts = AlertsBuilder::forUserNavbar($user);
            $view->with('navAlerts', $navAlerts);
            $view->with('navAlertsCount', count($navAlerts));
        });
    }
}
