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
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $userRole = DB::table('roles')->where('name', 'user')->first();

        DB::table('users')->insert([
            [
                'login' => 'admin',
                'password_hash' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'created_at' => now(),
            ],
            [
                'login' => 'user1',
                'password_hash' => Hash::make('user123'),
                'role_id' => $userRole->id,
                'created_at' => now(),
            ],
            [
                'login' => 'user2',
                'password_hash' => Hash::make('user123'),
                'role_id' => $userRole->id,
                'created_at' => now(),
            ],

        
        ]);
    }
}
