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
                'name' => 'Военный комиссариат г. Северодвинск',
                'longitude' => 29,
                'latitude' => 74,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Военный комиссариат г. Архангельск',
                'longitude' => 36,
                'latitude' => 73,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('commissariats')->insert($commissariats);

        $this->command->info('✓ Комиссариаты созданы: '.count($commissariats));
    }
}