<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommissariatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        foreach ([
            ['name' => 'Архангельский комиссариат №1', 'is_active' => true],
            ['name' => 'Северодвинский комиссариат №1', 'is_active' => true],
        ] as $row) {
            DB::table('commissariats')->updateOrInsert(
                ['name' => $row['name']],
                ['name' => $row['name'], 'is_active' => $row['is_active'], 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
