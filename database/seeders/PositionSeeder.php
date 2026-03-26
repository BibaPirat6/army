<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**Z
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Загружаем справочники в память (ключ = название, значение = ID)
        $positionTypes = DB::table('position_types')->pluck('id', 'name');
        $chiefTypes = DB::table('chief_types')->pluck('id', 'name');

        // 2. Данные должностей
        $positions = [
            // Операционные должности (рядовые сотрудники)
            [
                'name' => 'Менеджер',
                'position_type' => 'Операционные',
                'chief_type' => 'работник',
            ],
            [
                'name' => 'Специалист',
                'position_type' => 'Операционные',
                'chief_type' => 'работник',
            ],
            [
                'name' => 'Оператор',
                'position_type' => 'Операционные',
                'chief_type' => 'работник',
            ],

            // Управленческие должности
            [
                'name' => 'Начальник комиссариата',
                'position_type' => 'Управленческие',
                'chief_type' => 'начальник комиссариата', // 🔗 совпадает с chief_types.name
            ],
            [
                'name' => 'Начальник отдела',
                'position_type' => 'Управленческие',
                'chief_type' => 'начальник отдела',
            ],
            [
                'name' => 'Начальник отделения',
                'position_type' => 'Управленческие',
                'chief_type' => 'начальник отделения',
            ],
            [
                'name' => 'Старший специалист',
                'position_type' => 'Управленческие',
                'chief_type' => 'работник',
            ],
        ];

        $now = now();
        $inserted = 0;

        foreach ($positions as $pos) {
            // Получаем ID типов, проверяем существование
            $positionTypeId = $positionTypes[$pos['position_type']] ?? null;
            $chiefTypeId = $chiefTypes[$pos['chief_type']] ?? null;

            // Пропускаем, если справочник не найден (с предупреждением)
            if (! $positionTypeId) {
                $this->command->warn("⚠️ Не найден тип должности: {$pos['position_type']}");

                continue;
            }
            if (! $chiefTypeId) {
                $this->command->warn("⚠️ Не найден chief_type: {$pos['chief_type']}");

                continue;
            }

            // Вставляем запись (insertOrIgnore защитит от дублей по unique('name'))
            $inserted += DB::table('positions')->insertOrIgnore([
                'name' => $pos['name'],
                'position_type_id' => $positionTypeId,
                'chief_type_id' => $chiefTypeId,
                'created_at' => $now,
                'updated_at' => $now,
            ]) ? 1 : 0;
        }

        $this->command->info("✓ Должности созданы: {$inserted} из ".count($positions));
    }
}
