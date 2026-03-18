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
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Петр',
                'last_name' => 'Петров',
                'patronymic' => 'Петрович',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Мария',
                'last_name' => 'Сидорова',
                'patronymic' => 'Сергеевна',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Сергей',
                'last_name' => 'Смирнов',
                'patronymic' => 'Михайлович',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Анна',
                'last_name' => 'Федорова',
                'patronymic' => 'Николаевна',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Дмитрий',
                'last_name' => 'Кузнецов',
                'patronymic' => 'Алексеевич',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Ольга',
                'last_name' => 'Волкова',
                'patronymic' => 'Игоревна',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Виктор',
                'last_name' => 'Морозов',
                'patronymic' => 'Вячеславович',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Екатерина',
                'last_name' => 'Соколова',
                'patronymic' => 'Андреевна',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей',
                'last_name' => 'Лебедев',
                'patronymic' => 'Борисович',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей2',
                'last_name' => 'Лебедев2',
                'patronymic' => 'Борисович2',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей3',
                'last_name' => 'Лебедев3',
                'patronymic' => 'Борисович3',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей4',
                'last_name' => 'Лебедев4',
                'patronymic' => 'Борисович4',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей5',
                'last_name' => 'Лебедев5',
                'patronymic' => 'Борисович5',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Алексей6',
                'last_name' => 'Лебедев6',
                'patronymic' => 'Борисович6',
                'photo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
