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
            ['kode' => '102', 'nama' => 'Bank', 'tipe' => 'aset'],
            ['kode' => '103', 'nama' => 'Persediaan Sparepart', 'tipe' => 'aset'],

            // Kewajiban
            ['kode' => '201', 'nama' => 'Hutang Usaha', 'tipe' => 'kewajiban'],

            // Modal
            ['kode' => '301', 'nama' => 'Modal Pemilik', 'tipe' => 'modal'],

            // Pendapatan
            ['kode' => '401', 'nama' => 'Pendapatan Service', 'tipe' => 'pendapatan'],
            ['kode' => '402', 'nama' => 'Pendapatan Penjualan Sparepart', 'tipe' => 'pendapatan'],

            // Beban
            ['kode' => '501', 'nama' => 'Beban Listrik', 'tipe' => 'beban'],
            ['kode' => '502', 'nama' => 'Beban Gaji', 'tipe' => 'beban'],
            ['kode' => '503', 'nama' => 'Beban Operasional', 'tipe' => 'beban'],
            ['kode' => '504', 'nama' => 'Beban Selisih Persediaan', 'tipe' => 'beban'],
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
