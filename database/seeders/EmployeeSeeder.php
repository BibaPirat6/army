<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->toArray();
        $personIds = DB::table('persons')->pluck('id')->toArray();
        
        $activeStatus = DB::table('work_statuses')->where('name', 'active')->first();
        $inactiveStatus = DB::table('work_statuses')->where('name', 'inactive')->first();

        $employees = [
            [
                'user_id' => $userIds[0],
                'person_id' => $personIds[0],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
            ],
            [
                'user_id' => $userIds[1],
                'person_id' => $personIds[1],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
            ],
            [
                'user_id' => $userIds[2],
                'person_id' => $personIds[2],
                'work_status_id' => $inactiveStatus->id,
                'created_at' => now(),
            ],
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->updateOrInsert(
                ['user_id' => $employee['user_id']],
                $employee
            );
        }
    }
}
