<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkScheduleTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // 📅 5/2 Стандартный график (дневной)
            [
                'name' => 'Стандартный график 5/2 (09:00-18:00)',
                'pattern' => json_encode([
                    'monday' => [
                        'work_start' => '09:00',
                        'work_end' => '18:00',
                        'breaks' => [['start' => '13:00', 'end' => '14:00']],
                        'is_workday' => true,
                    ],
                    'tuesday' => [
                        'work_start' => '09:00',
                        'work_end' => '18:00',
                        'breaks' => [['start' => '13:00', 'end' => '14:00']],
                        'is_workday' => true,
                    ],
                    'wednesday' => [
                        'work_start' => '09:00',
                        'work_end' => '18:00',
                        'breaks' => [['start' => '13:00', 'end' => '14:00']],
                        'is_workday' => true,
                    ],
                    'thursday' => [
                        'work_start' => '09:00',
                        'work_end' => '18:00',
                        'breaks' => [['start' => '13:00', 'end' => '14:00']],
                        'is_workday' => true,
                    ],
                    'friday' => [
                        'work_start' => '09:00',
                        'work_end' => '18:00',
                        'breaks' => [['start' => '13:00', 'end' => '14:00']],
                        'is_workday' => true,
                    ],
                    'saturday' => ['is_workday' => false],
                    'sunday' => ['is_workday' => false],
                ], JSON_UNESCAPED_UNICODE), // ✅ Правильный флаг для кириллицы
                'is_default' => true,
            ],

            // 👩 5/2 Сокращённый график (для женщин)
            [
                'name' => 'График 5/2 для женщин (08:00-17:00)',
                'pattern' => json_encode([
                    'monday' => [
                        'work_start' => '08:00',
                        'work_end' => '17:00',
                        'breaks' => [['start' => '12:00', 'end' => '13:00']],
                        'is_workday' => true,
                    ],
                    'tuesday' => [
                        'work_start' => '08:00',
                        'work_end' => '17:00',
                        'breaks' => [['start' => '12:00', 'end' => '13:00']],
                        'is_workday' => true,
                    ],
                    'wednesday' => [
                        'work_start' => '08:00',
                        'work_end' => '17:00',
                        'breaks' => [['start' => '12:00', 'end' => '13:00']],
                        'is_workday' => true,
                    ],
                    'thursday' => [
                        'work_start' => '08:00',
                        'work_end' => '17:00',
                        'breaks' => [['start' => '12:00', 'end' => '13:00']],
                        'is_workday' => true,
                    ],
                    'friday' => [
                        'work_start' => '08:00',
                        'work_end' => '16:00',
                        'breaks' => [['start' => '12:00', 'end' => '13:00']],
                        'is_workday' => true,
                    ],
                    'saturday' => ['is_workday' => false],
                    'sunday' => ['is_workday' => false],
                ], JSON_UNESCAPED_UNICODE),
                'is_default' => false,
            ],

            // 🔄 График 2/2 (дневные смены)
            [
                'name' => 'График 2/2 (дневной, 08:00-20:00)',
                'pattern' => json_encode([
                    'monday' => [
                        'work_start' => '08:00',
                        'work_end' => '20:00',
                        'breaks' => [['start' => '12:00', 'end' => '13:00'], ['start' => '16:00', 'end' => '16:30']],
                        'is_workday' => true,
                        'shift_type' => 'day',
                    ],
                    'tuesday' => [
                        'work_start' => '08:00',
                        'work_end' => '20:00',
                        'breaks' => [['start' => '12:00', 'end' => '13:00'], ['start' => '16:00', 'end' => '16:30']],
                        'is_workday' => true,
                        'shift_type' => 'day',
                    ],
                    'wednesday' => ['is_workday' => false],
                    'thursday' => ['is_workday' => false],
                    'friday' => [
                        'work_start' => '08:00',
                        'work_end' => '20:00',
                        'breaks' => [['start' => '12:00', 'end' => '13:00'], ['start' => '16:00', 'end' => '16:30']],
                        'is_workday' => true,
                        'shift_type' => 'day',
                    ],
                    'saturday' => [
                        'work_start' => '08:00',
                        'work_end' => '20:00',
                        'breaks' => [['start' => '12:00', 'end' => '13:00'], ['start' => '16:00', 'end' => '16:30']],
                        'is_workday' => true,
                        'shift_type' => 'day',
                    ],
                    'sunday' => ['is_workday' => false],
                ], JSON_UNESCAPED_UNICODE),
                'is_default' => false,
            ],

            // 🌙 Ночной график (22:00-06:00)
            [
                'name' => 'Ночной график (22:00-06:00)',
                'pattern' => json_encode([
                    'monday' => [
                        'work_start' => '22:00',
                        'work_end' => '06:00',
                        'breaks' => [['start' => '02:00', 'end' => '02:30']],
                        'is_workday' => true,
                        'shift_type' => 'night',
                        'crosses_midnight' => true,
                    ],
                    'tuesday' => [
                        'work_start' => '22:00',
                        'work_end' => '06:00',
                        'breaks' => [['start' => '02:00', 'end' => '02:30']],
                        'is_workday' => true,
                        'shift_type' => 'night',
                        'crosses_midnight' => true,
                    ],
                    'wednesday' => ['is_workday' => false],
                    'thursday' => ['is_workday' => false],
                    'friday' => [
                        'work_start' => '22:00',
                        'work_end' => '06:00',
                        'breaks' => [['start' => '02:00', 'end' => '02:30']],
                        'is_workday' => true,
                        'shift_type' => 'night',
                        'crosses_midnight' => true,
                    ],
                    'saturday' => [
                        'work_start' => '22:00',
                        'work_end' => '06:00',
                        'breaks' => [['start' => '02:00', 'end' => '02:30']],
                        'is_workday' => true,
                        'shift_type' => 'night',
                        'crosses_midnight' => true,
                    ],
                    'sunday' => ['is_workday' => false],
                ], JSON_UNESCAPED_UNICODE),
                'is_default' => false,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('work_schedule_templates')->updateOrInsert(
                ['name' => $template['name']],
                [
                    'pattern' => $template['pattern'],
                    'is_default' => $template['is_default'] ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Шаблоны графиков работы успешно добавлены!');
    }
}