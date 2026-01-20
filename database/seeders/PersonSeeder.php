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
        ]);
    }
}
