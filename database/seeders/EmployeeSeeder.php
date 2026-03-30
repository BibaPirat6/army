<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех пользователей
        $adminUser = User::all();

        // Получаем существующих персон
        $persons = Person::all();

        // Создаём сотрудников
        $employees = [
            [
                'user_id' => $adminUser[0]->id,  // добавляем ->id
                'person_id' => $persons[0]->id,  // добавляем ->id
                'created_at' => now(),
            ],
            [
                'user_id' => $adminUser[1]->id,  // добавляем ->id
                'person_id' => $persons[1]->id,  // добавляем ->id
                'created_at' => now(),
            ],
            [
                'user_id' => $adminUser[2]->id,  // добавляем ->id
                'person_id' => $persons[2]->id,  // добавляем ->id
                'created_at' => now(),
            ],
        ];

        foreach ($employees as $employeeData) {
            Employee::create($employeeData);
        }
    }
}