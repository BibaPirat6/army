<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class ChiefTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chiefTypes = [
            ['name' => 'работник'],                    // обычный сотрудник
            ['name' => 'начальник отдела'],            // начальник отдела
            ['name' => 'начальник отделения'],         // начальник отделения
            ['name' => 'начальник комиссариата'],      // начальник комиссариата
        ];

        // Используем insertOrIgnore для идемпотентности (можно запускать многократно)
        foreach ($chiefTypes as &$type) {
            $type['created_at'] = now();
            $type['updated_at'] = now();
        }

        DB::table('chief_types')->insertOrIgnore($chiefTypes);

        $this->command->info('✓ Chief types seeded: '.count($chiefTypes));
    }
}
