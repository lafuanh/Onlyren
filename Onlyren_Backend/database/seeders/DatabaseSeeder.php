<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
       /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123445678'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create some test users
        User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Test Renter',
            'email' => 'renter@test.com',
            'password' => Hash::make('password'),
            'role' => 'renter',
            'email_verified_at' => now(),
        ]);
    }
    /**
     * Seed the application's database.
     */
    
}
