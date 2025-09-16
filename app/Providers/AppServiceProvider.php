<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Hanya paksa HTTPS di production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }


        // === Directive untuk cek hak akses ===
        Blade::if('canView', function ($menu) {
            $akses = session('hak_akses');
            // dd($akses);
            // dd($akses[$menu][0]->view);
            if (!$akses || !isset($akses[$menu])) return false;
            return $akses[$menu][0]->view ?? false;
        });

        Blade::if('canCreate', function ($menu) {
            $akses = session('hak_akses');
            if (!$akses || !isset($akses[$menu])) return false;
            return $akses[$menu][0]->create ?? false;
        });

        Blade::if('canEdit', function ($menu) {
            $akses = session('hak_akses');
            if (!$akses || !isset($akses[$menu])) return false;
            return $akses[$menu][0]->edit ?? false;
        });

        Blade::if('canDelete', function ($menu) {
            $akses = session('hak_akses');
            if (!$akses || !isset($akses[$menu])) return false;
            return $akses[$menu][0]->delete ?? false;
        });
    }
}
