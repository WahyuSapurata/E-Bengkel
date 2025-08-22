<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user()->role;
            if ($user === 'superadmin') {
                return redirect()->route('superadmin.dashboard-superadmin');
            } else if ($user === 'outlet') {
                return redirect()->route('outlet.dashboard-outlet');
            } else if ($user === 'kasir') {
                return redirect()->route('kasir.dashboard-kasir');
            }
        }


        return $next($request);
    }
}
