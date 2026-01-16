<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'login' => 'admin',
                'password_hash' => Hash::make('admin123'),
            ],
            [
                'login' => 'user1',
                'password_hash' => Hash::make('user123'),
            ],
            [
                'login' => 'user2',
                'password_hash' => Hash::make('user123'),
            ],
        ]);
    }
}
