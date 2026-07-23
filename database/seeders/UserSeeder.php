<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin Timun Mas',
            'email' => 'admin@timunmas.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Sales
        User::create([
            'name' => 'Sales 1',
            'email' => 'sales1@timunmas.com',
            'password' => Hash::make('password'),
            'role' => 'sales',
        ]);

        User::create([
            'name' => 'Sales 2',
            'email' => 'sales2@timunmas.com',
            'password' => Hash::make('password'),
            'role' => 'sales',
        ]);
    }
}
