<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Account Admin
        User::firstOrCreate(
            ['email' => 'admin@bengkel.com'],
            [
                'name' => 'Administrator Bengkel',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN,
            ]
        );

        // Account Kasir
        User::firstOrCreate(
            ['email' => 'kasir@bengkel.com'],
            [
                'name' => 'Kasir Bengkel',
                'password' => Hash::make('password'),
                'role' => UserRole::KASIR,
            ]
        );
    }
}
