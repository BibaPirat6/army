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
        $employeeIds = DB::table('employees')->pluck('id')->toArray();
        $positionIds = DB::table('positions')->pluck('id', 'name')->toArray();

        if (count($employeeIds) === 0 || count($positionIds) === 0) {
            return;
        }

        $now = now();

        $rows = [
            [
                'employee_id' => $employeeIds[0],
                'position_id' => $positionIds['Менеджер'] ?? array_values($positionIds)[0],
                'rate' => 1.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        if (isset($employeeIds[1]) && isset($positionIds['Специалист'])) {
            $rows[] = [
                'employee_id' => $employeeIds[1],
                'position_id' => $positionIds['Специалист'],
                'rate' => 1.00,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (isset($employeeIds[2]) && isset($positionIds['Оператор'])) {
            $rows[] = [
                'employee_id' => $employeeIds[2],
                'position_id' => $positionIds['Оператор'],
                'rate' => 0.50,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('employee_positions')->upsert(
            $rows,
            ['employee_id', 'position_id'],
            ['rate', 'updated_at']
        );
    }
}
