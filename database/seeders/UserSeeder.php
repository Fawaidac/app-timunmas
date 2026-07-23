<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin Timun Mas',
            'email' => 'admin@timunmas.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'area' => null,
            'phone' => '081234567890',
        ]);

        // Sales user
        User::create([
            'name' => 'Andi Salesman',
            'email' => 'sales@timunmas.com',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'area' => 'Jakarta Selatan',
            'phone' => '081234567891',
        ]);

        // Additional sales users for testing
        User::create([
            'name' => 'Budi Sales',
            'email' => 'budi@timunmas.com',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'area' => 'Jakarta Pusat',
            'phone' => '081234567892',
        ]);
    }
}
