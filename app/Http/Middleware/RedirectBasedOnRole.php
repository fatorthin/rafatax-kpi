<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Jika user sudah login dan mengakses halaman login, redirect berdasarkan role
            if ($request->is('login') || $request->is('admin/login') || $request->is('staff/login')) {
                if ($user->hasRole('admin')) {
                    return redirect()->route('filament.admin.pages.dashboard');
                } elseif ($user->hasRole('staff')) {
                    return redirect()->route('filament.staff.pages.dashboard');
                }
            }
        }

        return $next($request);
    }
}
