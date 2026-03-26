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
            ['name' => 'занят'],
            ['name' => 'вакант'],
            ['name' => 'отпуск'],
            ['name' => 'декрет'],
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
