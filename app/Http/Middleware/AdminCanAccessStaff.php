<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminCanAccessStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Jika user sudah login dan memiliki role admin, izinkan akses
        if (Auth::check() && $user && $user->hasRole('admin')) {
            return $next($request);
        }

        // Jika user memiliki role staff, izinkan akses
        if (Auth::check() && $user && $user->hasRole('staff')) {
            return $next($request);
        }

        // Jika tidak ada role yang sesuai, redirect ke login
        return redirect()->route('filament.staff.auth.login');
    }
}
