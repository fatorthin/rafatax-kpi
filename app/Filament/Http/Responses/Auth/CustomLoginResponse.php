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

        // Pastikan user sudah login
        if (!$user) {
            return new RedirectResponse(url('/login'));
        }

        // Redirect berdasarkan role
        if ($user->hasRole('admin')) {
            // Admin diarahkan ke admin panel
            return new RedirectResponse(url('/app'));
        } elseif ($user->hasRole('staff')) {
            // Staff diarahkan ke staff panel
            return new RedirectResponse(url('/staff'));
        } else {
            // Jika user tidak memiliki role, redirect ke admin sebagai default
            return new RedirectResponse(url('/app'));
        }
    }
}
