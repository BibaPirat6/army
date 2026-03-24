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

        DB::table('users')->insert([
            [
                'login' => 'admin',
                'password_hash' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'created_at' => now(),
            ]
        ]);
    }
}
