<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Person;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskInstance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_shows_assigned_tasks_for_current_employee(): void
    {
        $role = Role::create([
            'name' => 'employee',
            'description' => 'Сотрудник',
        ]);

        $user = User::create([
            'login' => 'profile-user',
            'password_hash' => bcrypt('password'),
            'role_id' => $role->id,
        ]);

        $person = Person::create([
            'имя' => 'Иван',
            'фамилия' => 'Петров',
            'отчество' => 'Сергеевич',
        ]);

        $employee = Employee::create([
            'user_id' => $user->id,
            'person_id' => $person->id,
        ]);

        $task = Task::create([
            'title' => 'Проверка профиля',
            'description' => 'Описание задачи для профиля',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ]);

        TaskAssignment::create([
            'task_id' => $task->id,
            'employee_id' => $employee->id,
            'quota' => 7,
            'priority' => 2,
            'completed_count' => 3,
        ]);

        TaskInstance::create([
            'task_id' => $task->id,
            'date' => now()->toDateString(),
            'daily_quota' => 4,
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200)
            ->assertSee('Мои задачи')
            ->assertSee('Проверка профиля')
            ->assertSee('Описание задачи для профиля')
            ->assertSee('7')
            ->assertSee('3')
            ->assertSee('4')
            ->assertSee('Средний');
    }
}
