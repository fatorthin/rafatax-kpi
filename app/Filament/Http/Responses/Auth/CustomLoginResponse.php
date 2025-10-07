<?php

namespace App\Filament\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Redirect berdasarkan role
        if ($user && $user->hasRole('admin')) {
            // Admin diarahkan ke halaman dashboard khusus yang memungkinkan akses ke kedua panel
            return new RedirectResponse(route('filament.admin.pages.dashboard'));
        } elseif ($user && $user->hasRole('staff')) {
            return new RedirectResponse(route('filament.staff.pages.dashboard'));
        } else {
            // Jika user tidak memiliki role, redirect ke admin sebagai default
            return new RedirectResponse(route('filament.admin.pages.dashboard'));
        }
    }
}
