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
                'phones' => json_encode(['79991234567', "74239432432"]),
                'emails' => json_encode(['ivanov@example.com', "ivanov77@example.com"]),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Петр',
                'last_name' => 'Петров',
                'patronymic' => 'Петрович',
                'phones' => json_encode(['79992345678']),
                'emails' => json_encode(['petrov@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Мария',
                'last_name' => 'Сидорова',
                'patronymic' => 'Сергеевна',
                'phones' => json_encode(['79993456789']),
                'emails' => json_encode(['sidorova@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Сергей',
                'last_name' => 'Смирнов',
                'patronymic' => 'Михайлович',
                'phones' => json_encode(['79994567890']),
                'emails' => json_encode(['smirnov@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Анна',
                'last_name' => 'Федорова',
                'patronymic' => 'Николаевна',
                'phones' => json_encode(['79995678901']),
                'emails' => json_encode(['fedorova@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Дмитрий',
                'last_name' => 'Кузнецов',
                'patronymic' => 'Алексеевич',
                'phones' => json_encode(['79996789012']),
                'emails' => json_encode(['kuznetsov@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Ольга',
                'last_name' => 'Волкова',
                'patronymic' => 'Игоревна',
                'phones' => json_encode(['79997890123']),
                'emails' => json_encode(['volkova@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Виктор',
                'last_name' => 'Морозов',
                'patronymic' => 'Вячеславович',
                'phones' => json_encode(['79998901234']),
                'emails' => json_encode(['morozov@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Екатерина',
                'last_name' => 'Соколова',
                'patronymic' => 'Андреевна',
                'phones' => json_encode(['79999012345']),
                'emails' => json_encode(['sokolova@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей',
                'last_name' => 'Лебедев',
                'patronymic' => 'Борисович',
                'phones' => json_encode(['79990123456']),
                'emails' => json_encode(['lebedev@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей2',
                'last_name' => 'Лебедев2',
                'patronymic' => 'Борисович2',
                'phones' => json_encode(['799901234562']),
                'emails' => json_encode(['lebedev2@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей3',
                'last_name' => 'Лебедев3',
                'patronymic' => 'Борисович3',
                'phones' => json_encode(['799901234563']),
                'emails' => json_encode(['lebedev3@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей4',
                'last_name' => 'Лебедев4',
                'patronymic' => 'Борисович4',
                'phones' => json_encode(['799901244564']),
                'emails' => json_encode(['lebedev4@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей5',
                'last_name' => 'Лебедев5',
                'patronymic' => 'Борисович5',
                'phones' => json_encode(['799901254565']),
                'emails' => json_encode(['lebedev5@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей6',
                'last_name' => 'Лебедев6',
                'patronymic' => 'Борисович6',
                'phones' => json_encode(['799901264566']),
                'emails' => json_encode(['lebedev6@example.com']),
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
