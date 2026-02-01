<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $types = DB::table('position_types')->pluck('id', 'name');

        $positions = [
            ['name' => 'Менеджер', 'position_type_id' => $types['Операционные'] ?? null],
            ['name' => 'Специалист', 'position_type_id' => $types['Операционные'] ?? null],
            ['name' => 'Оператор', 'position_type_id' => $types['Управленческие'] ?? null],

            ['name' => 'Начальник комиссариата', 'position_type_id' => $types['Управленческие'] ?? null],
            ['name' => 'Начальник отдела', 'position_type_id' => $types['Управленческие'] ?? null],
            ['name' => 'Начальник отделения', 'position_type_id' => $types['Управленческие'] ?? null],
        ];

        foreach ($positions as $row) {
            DB::table('positions')->updateOrInsert(
                ['name' => $row['name'], 'position_type_id' => $row['position_type_id']],
                ['name' => $row['name'], 'position_type_id' => $row['position_type_id'], 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
