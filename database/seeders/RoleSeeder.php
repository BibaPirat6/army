<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'admin',
                'description' => 'Администратор',
                'created_at' => now(),
            ],
            [
                'name' => 'user',
                'description' => 'Пользователь',
                'created_at' => now(),
            ]
        ]);
    }
}
