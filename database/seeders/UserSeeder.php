<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $email = "user$i@example.com";

            // Skip if user already exists
            if (DB::table('users')->where('email', $email)->exists()) {
                continue;
            }

            DB::table('users')->insert([
                'name' => "Test User $i",
                'email' => $email,
                'password' => bcrypt('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('user_balances')->insert([
                'user_id' => DB::getPdo()->lastInsertId(),
                'balance' => 1000.0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}