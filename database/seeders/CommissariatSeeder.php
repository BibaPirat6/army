<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommissariatSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $commissariats = [
            [
                'name' => 'Военный комиссариат г. Москвы',
                'longitude' => 37615560, // * 1e6 для integer
                'latitude' => 55751244,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Военный комиссариат г. Санкт-Петербурга',
                'longitude' => 30315466,
                'latitude' => 59938632,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('commissariats')->insert($commissariats);

        $this->command->info('✓ Комиссариаты созданы: '.count($commissariats));
    }
}