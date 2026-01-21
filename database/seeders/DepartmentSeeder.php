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

        foreach ([
            ['name' => 'Отдел кадров', 'is_active' => true],
            ['name' => 'Финансовый отдел', 'is_active' => true],
        ] as $row) {
            DB::table('departments')->updateOrInsert(
                ['name' => $row['name']],
                ['name' => $row['name'], 'is_active' => $row['is_active'], 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
