<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CommissariatPositionFilter extends BaseFilter
{
    protected function filters(): array
    {
        return [
            'search' => $this->data->search,
            'vacancyStatus' => $this->data->vacancyStatus,
            'rateRange' => [
                'min' => $this->data->rateMin,
                'max' => $this->data->rateMax,
            ],
            'department' => $this->data->departmentId,
            'division' => $this->data->divisionId,
            'employeeStatus' => $this->data->employeeStatus,
            'sort' => [
                'by' => $this->data->sortBy,
                'direction' => $this->data->sortDirection,
            ],
        ];
    }

    protected function rateRange(Builder $query, array $range): void
    {
        $min = $range['min'] ?? 0.25;
        $max = $range['max'] ?? 2;

        $query->whereHas('employeePositions', function (Builder $q) use ($min, $max) {
            $q->whereBetween('rate', [$min, $max])
                ->where('employee_position_status_id', 1); // Только работающие
        });
    }

    protected function search(Builder $query, string $value): void
    {
        $query->where(function (Builder $query) use ($value) {
            $query
                ->orWhereHas('position', fn (Builder $q) => $q->where('name', 'like', "%{$value}%"))
                ->orWhereHas('position.positionType', fn (Builder $q) => $q->where('name', 'like', "%{$value}%"))
                ->orWhereHas('position.chiefType', fn (Builder $q) => $q->where('name', 'like', "%{$value}%"))
                ->orWhereHas('department', fn (Builder $q) => $q->where('name', 'like', "%{$value}%"))
                ->orWhereHas('division', fn (Builder $q) => $q->where('name', 'like', "%{$value}%"))
                ->orWhereHas('employeePositions.employee.person', function (Builder $q) use ($value) {
                    $q->whereRaw("CONCAT(фамилия, ' ', имя, ' ', отчество) LIKE ?", ["%{$value}%"]);
                });
        });
    }

    protected function vacancyStatus(Builder $query, string $value): void
    {
        // Фильтрация в контроллере
    }

    /**
     * Фильтр по диапазону ставок СОТРУДНИКОВ
     */
    protected function rateMin(Builder $query, float $value): void
    {
        $query->whereHas('employeePositions', function (Builder $q) use ($value) {
            $q->where('rate', '>=', $value)
                ->where('employee_position_status_id', 1); // Только работающие
        });
    }

    protected function rateMax(Builder $query, float $value): void
    {
        $query->whereHas('employeePositions', function (Builder $q) use ($value) {
            $q->where('rate', '<=', $value)
                ->where('employee_position_status_id', 1); // Только работающие
        });
    }

    protected function department(Builder $query, ?int $value): void
    {
        if ($value) {
            $query->where('department_id', $value);
        }
    }

    protected function division(Builder $query, ?int $value): void
    {
        if ($value) {
            $query->where('division_id', $value);
        }
    }

    protected function employeeStatus(Builder $query, ?string $value): void
    {
        // Фильтрация в контроллере
    }

    protected function sort(Builder $query, array $sort): void
    {
        $sortBy = $sort['by'];
        $direction = $sort['direction'] === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['id', 'rate_total', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $direction);
        }
    }
}

