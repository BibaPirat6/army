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
        $commissariats = DB::table('commissariats')->pluck('id')->toArray();
        $departments = DB::table('departments')->pluck('id', 'name')->toArray();
        $divisions = DB::table('divisions')->pluck('id', 'name')->toArray();

        if (count($employeeIds) === 0 || count($positionIds) === 0) {
            return;
        }

        $now = now();
        $commissariatId = $commissariats[0] ?? null;
        $departmentId = $departments['Финансовый отдел'] ?? null;
        $divisionId = $divisions['Строевое отделение'] ?? null;

        $rows = [
            [
                'employee_id' => $employeeIds[0],
                'position_id' => $positionIds['Менеджер'] ?? array_values($positionIds)[0],
                'commissariat_id' => $commissariatId,
                'department_id' => $departmentId,
                'division_id' => $divisionId,
                'rate' => 1.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        if (isset($employeeIds[1]) && isset($positionIds['Специалист'])) {
            $rows[] = [
                'employee_id' => $employeeIds[1],
                'position_id' => $positionIds['Специалист'],
                'commissariat_id' => $commissariatId,
                'department_id' => $departments['Отдел кадров'] ?? null,
                'division_id' => $divisions['Отделение по борьбе с наркотиками'] ?? null,
                'rate' => 1.00,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (isset($employeeIds[2]) && isset($positionIds['Оператор'])) {
            $rows[] = [
                'employee_id' => $employeeIds[2],
                'position_id' => $positionIds['Оператор'],
                'commissariat_id' => $commissariatId,
                'department_id' => $departmentId,
                'division_id' => null,
                'rate' => 0.50,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('employee_positions')->upsert(
            $rows,
            ['employee_id', 'position_id'],
            ['rate', 'commissariat_id', 'department_id', 'division_id', 'updated_at']
        );
    }
}
