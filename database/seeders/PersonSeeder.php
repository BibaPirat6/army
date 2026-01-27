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
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Петр',
                'last_name' => 'Петров',
                'patronymic' => 'Петрович',
                'phone' => '79992345678',
                'email' => 'petrov@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Мария',
                'last_name' => 'Сидорова',
                'patronymic' => 'Сергеевна',
                'phone' => '79993456789',
                'email' => 'sidorova@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Сергей',
                'last_name' => 'Смирнов',
                'patronymic' => 'Михайлович',
                'phone' => '79994567890',
                'email' => 'smirnov@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Анна',
                'last_name' => 'Федорова',
                'patronymic' => 'Николаевна',
                'phone' => '79995678901',
                'email' => 'fedorova@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Дмитрий',
                'last_name' => 'Кузнецов',
                'patronymic' => 'Алексеевич',
                'phone' => '79996789012',
                'email' => 'kuznetsov@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Ольга',
                'last_name' => 'Волкова',
                'patronymic' => 'Игоревна',
                'phone' => '79997890123',
                'email' => 'volkova@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Виктор',
                'last_name' => 'Морозов',
                'patronymic' => 'Вячеславович',
                'phone' => '79998901234',
                'email' => 'morozov@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Екатерина',
                'last_name' => 'Соколова',
                'patronymic' => 'Андреевна',
                'phone' => '79999012345',
                'email' => 'sokolova@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей',
                'last_name' => 'Лебедев',
                'patronymic' => 'Борисович',
                'phone' => '79990123456',
                'email' => 'lebedev@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // 
            [
                'first_name' => 'Алексей2',
                'last_name' => 'Лебедев2',
                'patronymic' => 'Борисович2',
                'phone' => '799901234562',
                'email' => 'lebedev2@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей3',
                'last_name' => 'Лебедев3',
                'patronymic' => 'Борисович3',
                'phone' => '799901234563',
                'email' => 'lebedev3@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей4',
                'last_name' => 'Лебедев4',
                'patronymic' => 'Борисович4',
                'phone' => '799901244564',
                'email' => 'lebedev4@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей5',
                'last_name' => 'Лебедев5',
                'patronymic' => 'Борисович5',
                'phone' => '799901254565',
                'email' => 'lebedev5@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей6',
                'last_name' => 'Лебедев6',
                'patronymic' => 'Борисович6',
                'phone' => '799901264566',
                'email' => 'lebedev6@example.com',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
