<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $commissariats = DB::table('commissariats')->pluck('id')->toArray();
        $commissariatId = $commissariats[0] ?? null;
        $departments = DB::table('departments')->pluck('id', 'name')->toArray();

        $divisionData = [
            ['name' => 'Строевое отделение', 'department_name' => 'Финансовый отдел'],
            ['name' => 'Отделение по борьбе с наркотиками', 'department_name' => 'Отдел кадров'],
        ];

        foreach ($divisionData as $row) {
            $departmentId = isset($departments[$row['department_name']]) ? $departments[$row['department_name']] : null;

            DB::table('divisions')->updateOrInsert(
                ['name' => $row['name']],
                [
                    'name' => $row['name'],
                    'commissariat_id' => $commissariatId,
                    'department_id' => $departmentId,
                    'chief_employee_id' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
