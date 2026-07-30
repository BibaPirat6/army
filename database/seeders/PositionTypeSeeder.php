<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $types = [
            // Основные направления
            ['name' => 'Управленческие'],
            ['name' => 'Операционные / Призывные'],
            ['name' => 'Кадровая работа'],
            ['name' => 'Финансово-экономические'],
            ['name' => 'Юридические'],
            
            // Технические и обеспечивающие
            ['name' => 'Информационные технологии'],
            ['name' => 'Связь и коммуникации'],
            ['name' => 'Документооборот и архив'],
            ['name' => 'Материально-техническое обеспечение'],
            ['name' => 'Транспорт и логистика'],
            
            // Специализированные
            ['name' => 'Медицинские / ВВЭК'],
            ['name' => 'Безопасность и режим'],
            ['name' => 'Учебно-методические'],
            ['name' => 'Общие / Вспомогательные'],
        ];

        foreach ($types as $row) {
            DB::table('position_types')->updateOrInsert(
                ['name' => $row['name']],
                ['name' => $row['name'], 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $this->command->info('✓ Position types seeded: '.count($types));
    }
}