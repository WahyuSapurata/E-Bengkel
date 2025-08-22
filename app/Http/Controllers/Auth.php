<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Auth as RequestsAuth;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class Auth extends BaseController
{
    public function show()
    {
        $module = 'Login';
        return view('auth.login', compact('module'));
    }

    public function login_proses(RequestsAuth $authRequest)
    {
        $credential = $authRequest->getCredentials();

        if (!FacadesAuth::attempt($credential)) {
            return redirect()->route('login.login-akun')->with('failed', 'Username atau Password salah')->withInput($authRequest->only('username'));
        } else {
            return $this->authenticated();
        }
    }

    public function authenticated()
    {
        if (FacadesAuth::user()->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard-superadmin');
        } else if (FacadesAuth::user()->role === 'outlet') {
            return redirect()->route('outlet.dashboard-outlet');
        } else if (FacadesAuth::user()->role === 'kasir') {
            return redirect()->route('kasir.dashboard-kasir');
        } else {
            return redirect()->route('login.login-akun')->with('failed', 'Role tidak dikenali');
        }
    }

    public function logout()
    {
        FacadesAuth::logout();
        return redirect()->route('login.login-akun')->with('success', 'Berhasil Logout');
    }
}
