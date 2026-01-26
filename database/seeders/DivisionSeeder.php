<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = DB::table("employees")->get();
        $commissariats = DB::table("commissariats")->get();
        $departments = DB::table("departments")->get();

        DB::table("divisions")->insert([
            [
                "name" => "Отделение А",
                "commissariat_id" => $commissariats[0]->id,
                "department_id" => $departments[0]->id,
                "chief_employee_id" => $employees[4]->id,
                "created_at" => now(),
            ],
            [
                "name" => "Отделение B",
                "commissariat_id" => $commissariats[0]->id,
                "department_id" => $departments[0]->id,
                "chief_employee_id" => $employees[5]->id,
                "created_at" => now(),
            ],
            [
                "name" => "Отделение G",
                "commissariat_id" => $commissariats[0]->id,
                "department_id" => $departments[1]->id,
                "chief_employee_id" => $employees[6]->id,
                "created_at" => now(),
            ],
            [
                "name" => "Отделение D",
                "commissariat_id" => $commissariats[0]->id,
                "department_id" => $departments[1]->id,
                "chief_employee_id" => $employees[7]->id,
                "created_at" => now(),
            ],
            [
                "name" => "Отделение IVANOVKA",
                "commissariat_id" => $commissariats[0]->id,
                "department_id" => null,
                "chief_employee_id" => $employees[8]->id,
                "created_at" => now(),
            ],
        ]);
    }
}
