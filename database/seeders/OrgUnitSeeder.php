<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrgUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем комиссариат (корневой элемент)
        $commissariatId = DB::table('org_units')->insertGetId([
            'title' => 'Главный комиссариат',
            'parent_id' => null,
            'type' => 'commissariat',
            'is_independent' => true,
            'level' => 1,
        ]);

        // Создаем отделы
        $department1Id = DB::table('org_units')->insertGetId([
            'title' => 'Отдел кадров',
            'parent_id' => $commissariatId,
            'type' => 'department',
            'is_independent' => false,
            'level' => 2,
        ]);

        $department2Id = DB::table('org_units')->insertGetId([
            'title' => 'Отдел финансов',
            'parent_id' => $commissariatId,
            'type' => 'department',
            'is_independent' => false,
            'level' => 2,
        ]);

        // Создаем филиалы
        $branch1Id = DB::table('org_units')->insertGetId([
            'title' => 'Филиал №1',
            'parent_id' => $department1Id,
            'type' => 'branch',
            'is_independent' => false,
            'level' => 3,
        ]);

        $branch2Id = DB::table('org_units')->insertGetId([
            'title' => 'Филиал №2',
            'parent_id' => $department2Id,
            'type' => 'branch',
            'is_independent' => false,
            'level' => 3,
        ]);

        // Создаем должности
        DB::table('org_units')->insert([
            [
                'title' => 'Начальник отдела',
                'parent_id' => $department1Id,
                'type' => 'position',
                'is_independent' => false,
                'level' => 3,
            ],
            [
                'title' => 'Специалист',
                'parent_id' => $branch1Id,
                'type' => 'position',
                'is_independent' => false,
                'level' => 4,
            ],
        ]);
    }
}
