<?php

namespace App\Filament\Staff\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AdminNavigationWidget extends Widget
{
    protected static string $view = 'filament.staff.widgets.admin-navigation-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Hanya tampil jika user adalah admin
        return Auth::check() && $user && $user->hasRole('admin');
    }
}
