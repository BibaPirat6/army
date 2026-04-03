<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $commissariats = DB::table('commissariats')->pluck('id', 'name');

        if ($commissariats->count() < 2) {
            $this->command->error('❌ Необходимо создать 2 комиссариата перед отделами!');
            return;
        }

        $now = now();
        $departments = [];

        // Отделы для каждого комиссариата
        $deptNames = [
            'Отдел заготовки и призыва',
            'Финансовый отдел',
            'Отдел кадров и учёта',
        ];

        foreach ($commissariats as $commissariatName => $commissariatId) {
            foreach ($deptNames as $deptName) {
                $departments[] = [
                    'commissariat_id' => $commissariatId,
                    'name' => $deptName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('departments')->insert($departments);

        $this->command->info('✓ Отделы созданы: '.count($departments));
    }
}