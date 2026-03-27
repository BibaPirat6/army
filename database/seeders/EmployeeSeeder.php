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
          // Получаем администратора
        $adminUser = User::where('login', 'admin')->first();
        

        // Получаем существующих персон
        $persons = Person::all();


        // Создаём сотрудников
        $employees = [
            [
                'user_id' => $adminUser->id,
                'person_id' => $persons->firstWhere('last_name', 'Иванов')?->id,
            ],
        ];

        foreach ($employees as $employeeData) {
            Employee::create($employeeData);
        }
        
    }
}
