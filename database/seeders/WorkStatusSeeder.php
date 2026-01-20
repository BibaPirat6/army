<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('work_statuses')->insert([
            [
                'name' => 'active',
                'description' => 'Работает',
                'created_at' => now(),
            ],
            [
                'name' => 'inactive',
                'description' => 'Не работает',
                'created_at' => now(),
            ],
            [
                'name' => 'on_leave',
                'description' => 'В отпуске',
                'created_at' => now(),
            ],
            [
                'name' => 'fired',
                'description' => 'Уволен',
                'created_at' => now(),
            ],
        ]);
    }
}
