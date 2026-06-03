<?php
// app/Filters/EmployeeFilter.php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use App\Models\EmployeePosition;
use App\Models\Person;
use App\Models\User;
use App\Models\Commissariat;

class EmployeeFilter extends BaseFilter
{
    protected function filters(): array
    {
        return [
            'search' => $this->data->search,
            'employeeStatus' => $this->data->employeeStatus,
            'userRole' => $this->data->userRole,
            'commissariat' => $this->data->commissariatId,
            'department' => $this->data->departmentId,
            'division' => $this->data->divisionId,
            'rate' => $this->data->rate,
            'sort' => [
                'by' => $this->data->sortBy,
                'direction' => $this->data->sortDirection,
            ],
        ];
    }

    /**
     * Универсальный поиск по:
     * - ФИО (Person: фамилия, имя, отчество)
     * - Логину (User: login)
     * - Должности (Position: name)
     */
    protected function search(Builder $query, string $value): void
    {
        $query->where(function (Builder $q) use ($value) {
            // Поиск по ФИО (Person)
            $q->whereHas('person', function (Builder $sq) use ($value) {
                $sq->where('фамилия', 'like', "%{$value}%")
                    ->orWhere('имя', 'like', "%{$value}%")
                    ->orWhere('отчество', 'like', "%{$value}%")
                    ->orWhereRaw("CONCAT(фамилия, ' ', имя, ' ', отчество) LIKE ?", ["%{$value}%"]);
            })
            // Поиск по логину (User)
            ->orWhereHas('user', function (Builder $sq) use ($value) {
                $sq->where('login', 'like', "%{$value}%");
            })
            // Поиск по должности (Position)
            ->orWhereHas('employeePositions.commissariatPosition.position', function (Builder $sq) use ($value) {
                $sq->where('name', 'like', "%{$value}%");
            });
        });
    }

    /**
     * Статус сотрудника (через должность)
     */
    protected function employeeStatus(Builder $query, string $value): void
    {
        $statusMap = [
            'working' => 1,    // Работает
            'vacation' => 2,   // Отпуск
            'maternity' => 3,  // Декрет
        ];
        
        $statusId = $statusMap[$value] ?? null;
        
        if ($statusId) {
            $query->whereHas('employeePositions', function (Builder $q) use ($statusId) {
                $q->where('employee_position_status_id', $statusId);
            });
        }
    }

    /**
     * Фильтр по роли пользователя (admin/user)
     */
    protected function userRole(Builder $query, string $value): void
    {
        $roleIds = [
            'admin' => 1,
            'user' => 2,
        ];
        
        $roleId = $roleIds[$value] ?? null;
        
        if ($roleId) {
            $query->whereHas('user', function (Builder $q) use ($roleId) {
                $q->where('role_id', $roleId);
            });
        }
    }

    /**
     * Фильтр по комиссариату
     */
    protected function commissariat(Builder $query, ?int $value): void
    {
        if ($value) {
            $query->where('commissariat_id', $value);
        }
    }

    /**
     * Фильтр по отделу
     */
    protected function department(Builder $query, ?int $value): void
    {
        if ($value) {
            $query->where('department_id', $value);
        }
    }

    /**
     * Фильтр по отделению
     */
    protected function division(Builder $query, ?int $value): void
    {
        if ($value) {
            $query->where('division_id', $value);
        }
    }

    /**
     * Фильтр по конкретной ставке
     */
    protected function rate(Builder $query, ?float $value): void
    {
        if ($value !== null) {
            $query->whereHas('employeePositions', function (Builder $q) use ($value) {
                $q->where('rate', $value);
            });
        }
    }

    /**
     * Сортировка результатов
     */
    protected function sort(Builder $query, array $sort): void
    {
        $sortBy = $sort['by'];
        $direction = $sort['direction'] === 'asc' ? 'asc' : 'desc';

        // Сортировка по общей ставке
        if ($sortBy === 'rate_total') {
            $query->withSum('employeePositions as total_rate', 'rate')
                  ->orderBy('total_rate', $direction);
        }
        // Сортировка по занятым ставкам
        elseif ($sortBy === 'occupied_rate') {
            $query->withSum('employeePositions as occupied_rate', 'rate')
                  ->orderBy('occupied_rate', $direction);
        }
        // Сортировка по свободным ставкам (2 - занятая ставка)
        elseif ($sortBy === 'available_rate') {
            $query->withSum('employeePositions as occupied_rate', 'rate')
                  ->orderByRaw("(2 - COALESCE(occupied_rate, 0)) $direction");
        }
        // Сортировка по ФИО
        elseif ($sortBy === 'full_name') {
            $query->orderBy(
                Person::select('фамилия')
                    ->whereColumn('persons.id', 'employees.person_id'),
                $direction
            );
        }
        // Сортировка по роли пользователя
        elseif ($sortBy === 'user_role') {
            $query->orderBy(
                User::select('role_id')
                    ->whereColumn('users.id', 'employees.user_id'),
                $direction
            );
        }
        // Сортировка по ID
        else {
            $query->orderBy('employees.id', $direction);
        }
    }
}