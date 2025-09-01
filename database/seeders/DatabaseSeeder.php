<?php

namespace Database\Seeders;

use App\Models\Coa;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'uuid' => Uuid::uuid4()->toString(),
                'nama' => 'Super Admin',
                'role' => 'superadmin',
                'password_hash' => '<>password',
                'password' => Hash::make('<>password'),
            ]
        );

        $coas = [
            // Aset
            ['kode' => '101', 'nama' => 'Kas', 'tipe' => 'aset'],
            ['kode' => '102', 'nama' => 'Bank Mandiri', 'tipe' => 'aset'],
            ['kode' => '103', 'nama' => 'Bank BNI', 'tipe' => 'aset'],
            ['kode' => '104', 'nama' => 'Bank BCA', 'tipe' => 'aset'],
            ['kode' => '105', 'nama' => 'Bank BSI', 'tipe' => 'aset'],
            ['kode' => '106', 'nama' => 'Bank BRI', 'tipe' => 'aset'],
            ['kode' => '107', 'nama' => 'Persediaan Sparepart', 'tipe' => 'aset'],
            ['kode' => '108', 'nama' => 'Kas Outlet', 'tipe' => 'aset'],

            // Kewajiban
            ['kode' => '201', 'nama' => 'Hutang Usaha', 'tipe' => 'kewajiban'],

            // Modal
            ['kode' => '301', 'nama' => 'Modal Pemilik', 'tipe' => 'modal'],

            // Pendapatan
            ['kode' => '401', 'nama' => 'Pendapatan Jasa Service', 'tipe' => 'pendapatan'],
            ['kode' => '402', 'nama' => 'Pendapatan Penjualan Sparepart', 'tipe' => 'pendapatan'],

            // Beban
            ['kode' => '501', 'nama' => 'Beban Listrik & Utilitas', 'tipe' => 'beban'],
            ['kode' => '502', 'nama' => 'Beban Gaji Karyawan', 'tipe' => 'beban'],
            ['kode' => '503', 'nama' => 'Beban Operasional Lainnya', 'tipe' => 'beban'],
            ['kode' => '504', 'nama' => 'Beban Selisih Persediaan / HPP', 'tipe' => 'beban'],
        ];

        foreach ($coas as $coa) {
            Coa::updateOrCreate(
                ['kode' => $coa['kode']], // cari berdasarkan kode (unique)
                [
                    'nama' => $coa['nama'],
                    'tipe' => $coa['tipe'],
                ]
            );
        }
    }
}
