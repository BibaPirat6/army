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
                'phone' => '+7 (999) 123-45-67',
                'email' => 'ivanov@example.com',
            ],
            [
                'first_name' => 'Петр',
                'last_name' => 'Петров',
                'patronymic' => 'Петрович',
                'phone' => '+7 (999) 234-56-78',
                'email' => 'petrov@example.com',
            ],
            [
                'first_name' => 'Мария',
                'last_name' => 'Сидорова',
                'patronymic' => 'Сергеевна',
                'phone' => '+7 (999) 345-67-89',
                'email' => 'sidorova@example.com',
            ],
        ]);
    }
}
