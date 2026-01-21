<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrgLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commissariat = DB::table('commissariats')->orderBy('id')->first();
        $departments = DB::table('departments')->pluck('id', 'name')->toArray();
        $divisions = DB::table('divisions')->pluck('id', 'name')->toArray();
        $employeeIds = DB::table('employees')->pluck('id')->toArray();
        $positionIds = DB::table('positions')->pluck('id', 'name')->toArray();

        if (!$commissariat) {
            return;
        }

        $now = now();

        $links = [];

        // commissariat -> departments
        foreach (['Финансовый отдел', 'Отдел кадров'] as $deptName) {
            if (!isset($departments[$deptName])) {
                continue;
            }

            $links[] = [
                'parent_type' => 'commissariat',
                'parent_id' => $commissariat->id,
                'child_type' => 'department',
                'child_id' => $departments[$deptName],
                'is_independent' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // department -> division
        if (isset($departments['Финансовый отдел'], $divisions['Строевое отделение'])) {
            $links[] = [
                'parent_type' => 'department',
                'parent_id' => $departments['Финансовый отдел'],
                'child_type' => 'division',
                'child_id' => $divisions['Строевое отделение'],
                'is_independent' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (isset($departments['Отдел кадров'], $divisions['Отделение по борьбе с наркотиками'])) {
            $links[] = [
                'parent_type' => 'department',
                'parent_id' => $departments['Отдел кадров'],
                'child_type' => 'division',
                'child_id' => $divisions['Отделение по борьбе с наркотиками'],
                'is_independent' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // department -> employees (пример)
        if (isset($departments['Финансовый отдел'], $employeeIds[0])) {
            $links[] = [
                'parent_type' => 'department',
                'parent_id' => $departments['Финансовый отдел'],
                'child_type' => 'employee',
                'child_id' => $employeeIds[0],
                'is_independent' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (isset($departments['Отдел кадров'], $employeeIds[1])) {
            $links[] = [
                'parent_type' => 'department',
                'parent_id' => $departments['Отдел кадров'],
                'child_type' => 'employee',
                'child_id' => $employeeIds[1],
                'is_independent' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // department -> positions (пример)
        if (isset($departments['Финансовый отдел'], $positionIds['Менеджер'])) {
            $links[] = [
                'parent_type' => 'department',
                'parent_id' => $departments['Финансовый отдел'],
                'child_type' => 'position',
                'child_id' => $positionIds['Менеджер'],
                'is_independent' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($links as $link) {
            DB::table('org_links')->updateOrInsert(
                [
                    'parent_type' => $link['parent_type'],
                    'parent_id' => $link['parent_id'],
                    'child_type' => $link['child_type'],
                    'child_id' => $link['child_id'],
                ],
                [
                    'is_independent' => $link['is_independent'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
