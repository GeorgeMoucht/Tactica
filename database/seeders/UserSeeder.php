<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@tactica.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('admin123'),
                'role'      => 'admin'
            ]
        );

        // Teacher user
        User::firstOrCreate(
            ['email' => 'teacher@tactica.com'],
            [
                'name'      => 'Default Teacher',
                'password'  => Hash::make('teacher123'),
                'role'      => 'teacher',
            ]
        );
    }
}