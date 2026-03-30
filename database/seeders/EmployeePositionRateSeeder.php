<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class EmployeePositionRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [];

        for ($rate = 0.25; $rate <= 2.00; $rate += 0.25) {
            $rates[] = [
                'rate' => number_format($rate, 2, '.', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('employee_position_rates')->insertOrIgnore($rates);
    }
}
