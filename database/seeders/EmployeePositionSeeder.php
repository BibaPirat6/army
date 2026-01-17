<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeePositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем ID сотрудников и организационных единиц
        $employeeIds = DB::table('employees')->pluck('id')->toArray();
        $orgUnitIds = DB::table('org_units')->where('type', 'position')->pluck('id')->toArray();

        // Проверяем, что есть достаточно записей
        if (empty($employeeIds) || empty($orgUnitIds)) {
            $this->command->warn('Недостаточно сотрудников или должностей для создания связей');
            return;
        }

        // Создаем связи сотрудников с должностями, проверяя существование
        $positions = [
            [
                'employee_id' => $employeeIds[0],
                'org_unit_id' => $orgUnitIds[0],
                'rate' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employeeIds[1] ?? $employeeIds[0],
                'org_unit_id' => $orgUnitIds[1] ?? $orgUnitIds[0],
                'rate' => 0.75,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employeeIds[2] ?? $employeeIds[0],
                'org_unit_id' => $orgUnitIds[0],
                'rate' => 0.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($positions as $position) {
            DB::table('employee_positions')->updateOrInsert(
                [
                    'employee_id' => $position['employee_id'],
                    'org_unit_id' => $position['org_unit_id'],
                ],
                $position
            );
        }
    }
}
