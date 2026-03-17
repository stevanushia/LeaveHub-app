<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        User::create([
            'name' => 'Admin HR',
            'email' => 'admin@energeek.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }
}
