<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Staff\Pages\Auth\Login;
use Filament\Navigation\MenuItem;
use Illuminate\Support\Facades\Auth;

class StaffPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('staff')
            ->favicon(asset('images/favicon.png'))
            ->path('staff')
            ->brandName('Rafatax KPI Staff')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Cyan,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Staff/Resources'), for: 'App\\Filament\\Staff\\Resources')
            ->discoverPages(in: app_path('Filament/Staff/Pages'), for: 'App\\Filament\\Staff\\Pages')
            ->resources([
                \App\Filament\Resources\LogBookResource::class,
                \App\Filament\Resources\ClientReportResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Staff/Widgets'), for: 'App\\Filament\\Staff\\Widgets')
            ->widgets([
                \App\Filament\Staff\Widgets\AdminNavigationWidget::class,
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Admin Panel')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(url('/app'))
                    ->visible(function (): bool {
                        $userId = Auth::id();
                        if (!$userId) {
                            return false;
                        }
                        return \App\Models\User::query()
                            ->whereKey($userId)
                            ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
                            ->exists();
                    }),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                \App\Http\Middleware\AdminCanAccessStaff::class,
            ]);
    }
}
