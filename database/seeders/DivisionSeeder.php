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

        foreach ([
            ['name' => 'Строевое отделение', 'specialization' => 'Связь и сети', 'is_active' => true],
            ['name' => 'Отделение по борьбе с наркотиками', 'specialization' => null, 'is_active' => true],
        ] as $row) {
            DB::table('divisions')->updateOrInsert(
                ['name' => $row['name']],
                [
                    'name' => $row['name'],
                    'specialization' => $row['specialization'],
                    'is_active' => $row['is_active'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
