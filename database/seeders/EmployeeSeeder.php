<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем ID пользователей и персон
        $userIds = DB::table('users')->pluck('id')->toArray();
        $personIds = DB::table('persons')->pluck('id')->toArray();

        // Проверяем, что есть достаточно записей
        if (count($userIds) < 3 || count($personIds) < 3) {
            $this->command->warn('Недостаточно пользователей или персон для создания сотрудников');
            return;
        }

        // Создаем сотрудников, проверяя существование
        $employees = [
            [
                'user_id' => $userIds[0],
                'person_id' => $personIds[0],
                'role' => 'admin',
                'work_status' => 'active',
            ],
            [
                'user_id' => $userIds[1],
                'person_id' => $personIds[1],
                'role' => 'user',
                'work_status' => 'active',
            ],
            [
                'user_id' => $userIds[2],
                'person_id' => $personIds[2],
                'role' => 'user',
                'work_status' => 'active',
            ],
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->updateOrInsert(
                ['user_id' => $employee['user_id']],
                $employee
            );
        }
    }
}
