<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        foreach ([
            ['name' => 'Управленческие'],
            ['name' => 'Операционные'],
            ['name' => 'Обеспечение делопроизводства и логистики'],
            ['name' => 'Творческие, поддержка'],
        ] as $row) {
            DB::table('position_types')->updateOrInsert(
                ['name' => $row['name']],
                ['name' => $row['name'], 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
