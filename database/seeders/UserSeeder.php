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
        // Получаем ID ролей
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $userRole  = DB::table('roles')->where('name', 'user')->first();

        if (!$adminRole || !$userRole) {
            throw new \RuntimeException('Роли не найдены. Убедитесь, что RoleSeeder был запущен.');
        }

        $users = [];

        // 1. Добавляем 3 администраторов
        $admins = [
            ['login' => 'admin',   'password' => 'admin123'],
            ['login' => 'ivan', 'password' => 'ivan123'],
            ['login' => 'oleg',   'password' => 'oleg123'],
        ];

        foreach ($admins as $admin) {
            $users[] = [
                'login'         => $admin['login'],
                'password_hash' => Hash::make($admin['password']),
                'role_id'       => $adminRole->id,
                'created_at'    => now(),
            ];
        }

        // 2. Генерируем 57 обычных пользователей
        for ($i = 1; $i <= 57; $i++) {
            $users[] = [
                'login'         => "user{$i}",
                'password_hash' => Hash::make('user123'),
                'role_id'       => $userRole->id,
                'created_at'    => now(),
            ];
        }

        // Вставляем все записи одним запросом
        DB::table('users')->insert($users);
    }
}