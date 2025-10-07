<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use App\Filament\Http\Responses\Auth\CustomLoginResponse;
use App\Filament\Http\Responses\Auth\CustomLogoutResponse;

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
        // Register custom login response
        $this->app->bind(LoginResponse::class, CustomLoginResponse::class);
        
        // Register custom logout response
        $this->app->bind(LogoutResponse::class, CustomLogoutResponse::class);
    }
}
