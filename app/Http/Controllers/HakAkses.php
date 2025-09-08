<?php

namespace App\Http\Controllers;

use App\Models\HakAkses as ModelsHakAkses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HakAkses extends Controller
{
    public function index($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $module = 'Hak Akses Untuk ' . $user->nama;

        $defaultMenus = [
            'Setup' => ['Data Pengguna'],
            'Master Data' => ['Kategori', 'Sub Kategori', 'Suplayer', 'Jasa', 'Produk', 'Customer', 'Karyawan', 'Outlet'],
            'Transaksi'   => ['Pembelian', 'Hutang', 'PO', 'PO Outlet', 'Pengiriman Barang'],
            'Accounting'  => ['Daftar Akun', 'Gaji Karyawan', 'Biaya Lain-lain', 'Jurnal Umum', 'Buku Besar', 'Neraca', 'Laba Rugi'],
        ];

        // Ambil hak akses user
        $hakAkses = ModelsHakAkses::where('uuid_user', $uuid)->get()->groupBy('menu');
        return view('pages.hakakses.index', compact('module', 'defaultMenus', 'hakAkses', 'user'));
    }

    public function update(Request $request, $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        if ($request->has('menus')) {
            foreach ($request->menus as $group => $menus) {
                foreach ($menus as $menu => $permissions) {
                    ModelsHakAkses::updateOrCreate(
                        ['uuid_user' => $user->uuid, 'group' => $group, 'menu' => $menu],
                        [
                            'view'   => $permissions['view'] ?? 0,
                            'create' => $permissions['create'] ?? 0,
                            'edit'   => $permissions['edit'] ?? 0,
                            'delete' => $permissions['delete'] ?? 0,
                        ]
                    );
                }
            }
        }

        // === Refresh session hak_akses jika user yang diupdate adalah user yang sedang login ===
        if ($user->uuid === Auth::user()->uuid) {
            $hakAkses = ModelsHakAkses::where('uuid_user', $user->uuid)
                ->get()
                ->groupBy('menu');

            session(['hak_akses' => $hakAkses]);
        }

        return redirect()->route('superadmin.data-pengguna')
            ->with('success', 'Hak akses berhasil diperbarui');
    }
}
