<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommissariatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = DB::table("employees")->get();

        DB::table("commissariats")->insert([
            [
                "name"=> "Военкомат 1",
                "chief_employee_id"=> $employees[0]->id,
                "created_at"=> now(),
            ],
            [
                "name"=> "Военкомат 2",
                "chief_employee_id"=> $employees[1]->id,
                "created_at"=> now(),
            ]
        ]);
    }
}
