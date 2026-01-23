<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $commissariats = DB::table('commissariats')->pluck('id')->toArray();
        $commissariatId = $commissariats[0] ?? null;

        foreach ([
            ['name' => 'Отдел кадров'],
            ['name' => 'Финансовый отдел'],
        ] as $row) {
            DB::table('departments')->updateOrInsert(
                ['name' => $row['name']],
                [
                    'name' => $row['name'],
                    'commissariat_id' => $commissariatId,
                    'chief_employee_id' => null,
                    'updated_at' => $now,
                    'created_at' => $now
                ]
            );
        }
    }
}
