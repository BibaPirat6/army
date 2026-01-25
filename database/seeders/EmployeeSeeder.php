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
                'user_id' => $userIds[0] ?? 1,
                'person_id' => $personIds[0],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds[1] ?? 2,
                'person_id' => $personIds[1],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userIds[2] ?? 3,
                'person_id' => $personIds[2],
                'work_status_id' => $inactiveStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'person_id' => $personIds[3],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'person_id' => $personIds[4],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'person_id' => $personIds[5],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'person_id' => $personIds[6],
                'work_status_id' => $inactiveStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'person_id' => $personIds[7],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'person_id' => $personIds[8],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'person_id' => $personIds[9],
                'work_status_id' => $activeStatus->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->insert($employee);
        }
    }
}
