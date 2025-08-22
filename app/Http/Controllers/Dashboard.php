<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseController
{
    public function landing_page()
    {
        return view('welcome');
    }

    public function dashboard_superadmin()
    {
        $module = 'Dashboard';
        return view('dashboard.superadmin', compact('module'));
    }

    public function dashboard_outlet()
    {
        $user = Auth::user();
        $nama_outlet = Outlet::where('uuid_user', $user->uuid)->first()->nama_outlet;
        $module = 'Dashboard Outlet ' . $nama_outlet;
        return view('dashboard.outlet', compact('module'));
    }
}
