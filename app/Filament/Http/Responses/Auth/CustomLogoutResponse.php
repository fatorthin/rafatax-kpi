<?php

namespace App\Filament\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;

class CustomLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        // Redirect ke halaman login custom setelah logout
        return redirect()->route('login');
    }
}

