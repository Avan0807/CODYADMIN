<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
    */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // ✅ Đăng ký component Blade x-agent.card
        Blade::component('agent.card', \App\View\Components\Agent\Card::class);
    }
}
