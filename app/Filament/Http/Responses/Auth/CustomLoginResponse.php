<?php

namespace App\Filament\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        try {
            $roles = $user->getRoleNames()->toArray();
        } catch (\Throwable $e) {
            $roles = ['unable_to_get_roles'];
        }

        // Tentukan target redirect berdasarkan role (prioritaskan staff)
        if ($user->hasRole('staff')) {
            $target = url('/staff');
        } elseif ($user->hasRole('admin')) {
            $target = url('/app');
        } else {
            $target = url('/app');
        }

        // Pastikan nilai intended/session sebelumnya tidak mengoverride redirect kita.
        try {
            session()->put('url.intended', $target);
            session()->forget('_previous');
        } catch (\Exception $e) {
            // ignore session write failures in response (non-fatal)
        }

        // Jika request berasal dari Livewire / AJAX, instruksi redirect dikirim
        // melalui header khusus supaya client-side Livewire melakukan redirect
        // secara langsung tanpa perlu refresh manual.
        $isLivewire = $request->header('X-Livewire') || $request->header('X-Requested-With') === 'XMLHttpRequest' || $request->wantsJson();

        if ($isLivewire) {
            $resp = new RedirectResponse($target);
            $resp->headers->set('X-Livewire-Location', $target);
            return $resp;
        }

        return new RedirectResponse($target);
    }
}
