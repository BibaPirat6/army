<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $commissariats = DB::table('commissariats')->get()->keyBy('id');
        $departments = DB::table('departments')
            ->get()
            ->groupBy('commissariat_id');

        if ($commissariats->count() < 2) {
            $this->command->error('❌ Необходимо создать 2 комиссариата!');
            return;
        }

        $now = now();
        $divisions = [];

        // Отделения внутри отделов (по 2 в каждом отделе = 12)
        $divisionNamesByDept = [
            'Отдел заготовки и призыва' => [
                'Отделение призыва',
                'Отделение учёта призывников',
            ],
            'Финансовый отдел' => [
                'Отделение бухгалтерии',
                'Отделение финансового планирования',
            ],
            'Отдел кадров и учёта' => [
                'Отделение кадров',
                'Отделение воинского учёта',
            ],
        ];

        foreach ($departments as $commissariatId => $deptList) {
            foreach ($deptList as $dept) {
                $divisionsForDept = $divisionNamesByDept[$dept->name] ?? [];

                foreach ($divisionsForDept as $divisionName) {
                    $divisions[] = [
                        'commissariat_id' => $commissariatId,
                        'department_id' => $dept->id,
                        'name' => $divisionName,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Самостоятельные отделения комиссариата (по 2 в каждом = 4)
            $independentDivisions = [
                'Отделение безопасности и режима',
                'Отделение связи и ИТ',
            ];

            foreach ($independentDivisions as $divisionName) {
                $divisions[] = [
                    'commissariat_id' => $commissariatId,
                    'department_id' => null, // самостоятельное
                    'name' => $divisionName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('divisions')->insert($divisions);

        $this->command->info('✓ Отделения созданы: '.count($divisions));
        $this->command->info('  └─ В отделах: 12');
        $this->command->info('  └─ Самостоятельные: 4');
    }
}