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
            ['name' => 'занят', 'color'=>'#ffffff'],
            ['name' => 'вакант', 'color'=>'#66FF00'],
            ['name' => 'отпуск', 'color'=>'#F64A46'],
            ['name' => 'декрет', 'color'=>'#F7943C'],
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
