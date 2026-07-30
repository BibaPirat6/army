<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех пользователей и персон, отсортированных по ID
        $users = User::orderBy('id')->get();
        $persons = Person::orderBy('id')->get();

        // Проверка: должно быть минимум 60 записей в каждой таблице
        if ($users->count() < 60 || $persons->count() < 60) {
            throw new \RuntimeException(
                "Недостаточно данных: пользователей — {$users->count()}, персон — {$persons->count()}. " .
                "Убедитесь, что UserSeeder и PersonSeeder отработали корректно."
            );
        }

        // Формируем массив для вставки: 1 пользователь → 1 персона (по индексу)
        $employees = [];
        $now = now();

        for ($i = 0; $i < 60; $i++) {
            $employees[] = [
                'user_id'   => $users[$i]->id,
                'person_id' => $persons[$i]->id,
                'created_at'=> $now,
                'updated_at'=> $now, // на случай, если в миграции есть timestamps()
            ];
        }

        // Массовая вставка (один запрос вместо 60)
        Employee::insert($employees);
    }
}