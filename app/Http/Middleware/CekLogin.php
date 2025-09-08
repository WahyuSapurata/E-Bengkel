<?php

namespace App\Http\Middleware;

use App\Models\HakAkses;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // Kalau belum login, redirect ke halaman login
            return redirect()->route('login.login-akun');
        }

        // === Inject hak akses ke session ===
        if (!session()->has('hak_akses')) {
            $hakAkses = HakAkses::where('uuid_user', Auth::user()->uuid)
                ->get()
                ->groupBy('menu'); // group by nama menu, misal "Kategori", "Jasa", dll

            session(['hak_akses' => $hakAkses]);
        }

        // Kalau sudah login, lanjutkan request
        return $next($request);
    }
}
