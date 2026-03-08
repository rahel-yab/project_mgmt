<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\User::create([
        'name' => 'Manager User',
        'email' => 'manager@test.com',
        'password' => bcrypt('password'),
        'role' => 'manager',
    ]);

    \App\Models\User::create([
        'name' => 'Dev User',
        'email' => 'dev@test.com',
        'password' => bcrypt('password'),
        'role' => 'developer',
    ]);

    \App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
        'role' => 'admin', 
    ]);
    }
}
