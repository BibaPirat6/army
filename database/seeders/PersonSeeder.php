<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('persons')->insert([
            [
                'first_name' => 'Иван',
                'last_name' => 'Иванов',
                'patronymic' => 'Иванович',
                'phone' => '79991234567',
                'email' => 'ivanov@example.com',
                'photo' => 'photos/ivanov.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Петр',
                'last_name' => 'Петров',
                'patronymic' => 'Петрович',
                'phone' => '79992345678',
                'email' => 'petrov@example.com',
                'photo' => 'photos/petrov.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Мария',
                'last_name' => 'Сидорова',
                'patronymic' => 'Сергеевна',
                'phone' => '79993456789',
                'email' => 'sidorova@example.com',
                'photo' => 'photos/sidorova.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Сергей',
                'last_name' => 'Смирнов',
                'patronymic' => 'Михайлович',
                'phone' => '79994567890',
                'email' => 'smirnov@example.com',
                'photo' => 'photos/smirnov.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Анна',
                'last_name' => 'Федорова',
                'patronymic' => 'Николаевна',
                'phone' => '79995678901',
                'email' => 'fedorova@example.com',
                'photo' => 'photos/fedorova.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Дмитрий',
                'last_name' => 'Кузнецов',
                'patronymic' => 'Алексеевич',
                'phone' => '79996789012',
                'email' => 'kuznetsov@example.com',
                'photo' => 'photos/kuznetsov.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Ольга',
                'last_name' => 'Волкова',
                'patronymic' => 'Игоревна',
                'phone' => '79997890123',
                'email' => 'volkova@example.com',
                'photo' => 'photos/volkova.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Виктор',
                'last_name' => 'Морозов',
                'patronymic' => 'Вячеславович',
                'phone' => '79998901234',
                'email' => 'morozov@example.com',
                'photo' => 'photos/morozov.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Екатерина',
                'last_name' => 'Соколова',
                'patronymic' => 'Андреевна',
                'phone' => '79999012345',
                'email' => 'sokolova@example.com',
                'photo' => 'photos/sokolova.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей',
                'last_name' => 'Лебедев',
                'patronymic' => 'Борисович',
                'phone' => '79990123456',
                'email' => 'lebedev@example.com',
                'photo' => 'photos/lebedev.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
