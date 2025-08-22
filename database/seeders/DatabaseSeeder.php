<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
    }
}
