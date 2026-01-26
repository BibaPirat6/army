<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = DB::table("employees")->get();
        $commissariats = DB::table("commissariats")->get();

        DB::table("departments")->insert([
            [
                "name" => "Отдел А",
                "commissariat_id" => $commissariats[0]->id,
                "chief_employee_id" => $employees[2]->id,
                "created_at" => now(),
            ],
            [
                "name" => "Отдел Б",
                "commissariat_id" => $commissariats[0]->id,
                "chief_employee_id" => $employees[3]->id,
                "created_at" => now(),
            ],
        ]);
    }
}
