<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class EmployeePositionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'работает', 'color'=>'FFFFFF','occupies_rate'=>true],
            ['name' => 'отпуск', 'color'=>'F64A46','occupies_rate'=>false],
            ['name' => 'декрет', 'color'=>'F7943C','occupies_rate'=>false],
            ['name' => 'уволен', 'color'=>'FF0000','occupies_rate'=>false],
        ];

        $now = now();
        foreach ($statuses as &$status) {
            $status['created_at'] = $now;
            $status['updated_at'] = $now;
        }

        DB::table('employee_position_statuses')->insertOrIgnore($statuses);

        $this->command->info('✓ Статусы назначений созданы: '.count($statuses));
    }
}
