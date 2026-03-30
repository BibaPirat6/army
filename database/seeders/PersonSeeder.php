<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $persons = [
            [
                'last_name' => 'Иванов',
                'first_name' => 'Иван',
                'patronymic' => 'Иванович',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'last_name' => 'Димончик',
                'first_name' => 'Иван',
                'patronymic' => 'Иванович',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'last_name' => 'Дениска',
                'first_name' => 'Иван',
                'patronymic' => 'Иванович',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($persons as $person) {
            Person::create($person);
        }
    }
}
