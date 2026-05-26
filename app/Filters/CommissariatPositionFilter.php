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

            'rate' => $this->data->rate,

            'positionType' => $this->data->positionTypeId,

            'chiefType' => $this->data->chiefTypeId,

            'commissariat' => $this->data->commissariatId,

            'department' => $this->data->departmentId,

            'division' => $this->data->divisionId,

            'employeePositionStatus' => $this->data
                ->employeePositionStatusId,

            'sort' => [
                'by' => $this->data->sortBy,
                'direction' => $this->data->sortDirection,
            ],
        ];
    }

    protected function search(
        Builder $query,
        string $value,
    ): void {

        $query->where(function (
            Builder $query,
        ) use ($value) {

            $query
                ->orWhereHas(
                    'position',
                    fn (Builder $query) => $query->where(
                        'name',
                        'like',
                        "%{$value}%"
                    )
                )
                ->orWhereHas(
                    'position.positionType',
                    fn (Builder $query) => $query->where(
                        'name',
                        'like',
                        "%{$value}%"
                    )
                )
                ->orWhereHas(
                    'position.chiefType',
                    fn (Builder $query) => $query->where(
                        'name',
                        'like',
                        "%{$value}%"
                    )
                )
                ->orWhereHas(
                    'department',
                    fn (Builder $query) => $query->where(
                        'name',
                        'like',
                        "%{$value}%"
                    )
                )
                ->orWhereHas(
                    'division',
                    fn (Builder $query) => $query->where(
                        'name',
                        'like',
                        "%{$value}%"
                    )
                )
                ->orWhereHas(
                    'employeePositions.employee.person',
                    function (
                        Builder $query,
                    ) use ($value) {

                        $query->whereRaw(
                            "CONCAT(
                        фамилия,
                        ' ',
                        имя,
                        ' ',
                        отчество
                    ) LIKE ?",
                            ["%{$value}%"]
                        );
                    }
                );
        });
    }

    protected function vacancyStatus(
        Builder $query,
        string $value,
    ): void {

        if ($value === 'vacant') {

            $query->doesntHave(
                'employeePositions'
            );
        }

        if ($value === 'staffed') {

            $query->has(
                'employeePositions'
            );
        }
    }

    protected function rate(
        Builder $query,
        float $value,
    ): void {

        $query->where(
            'rate_total',
            $value
        );
    }

    protected function positionType(
        Builder $query,
        int $value,
    ): void {

        $query->whereHas(
            'position',
            fn (Builder $query) => $query->where(
                'position_type_id',
                $value
            )
        );
    }

    protected function chiefType(
        Builder $query,
        int $value,
    ): void {

        $query->whereHas(
            'position',
            fn (Builder $query) => $query->where(
                'chief_type_id',
                $value
            )
        );
    }

    protected function commissariat(
        Builder $query,
        int $value,
    ): void {

        $query->where(
            'commissariat_id',
            $value
        );
    }

    protected function department(
        Builder $query,
        int $value,
    ): void {

        $query->where(
            'department_id',
            $value
        );
    }

    protected function division(
        Builder $query,
        int $value,
    ): void {

        $query->where(
            'division_id',
            $value
        );
    }

    protected function employeePositionStatus(
        Builder $query,
        int $value,
    ): void {

        $query->whereHas(
            'employeePositions',
            fn (Builder $query) => $query->where(
                'employee_position_status_id',
                $value
            )
        );
    }

    protected function sort(
        Builder $query,
        array $sort,
    ): void {

        $allowedSorts = [
            'id',
            'rate_total',
            'created_at',
        ];

        $sortBy = in_array(
            $sort['by'],
            $allowedSorts
        )
            ? $sort['by']
            : 'id';

        $direction =
            $sort['direction'] === 'asc'
                ? 'asc'
                : 'desc';

        $query->orderBy(
            $sortBy,
            $direction
        );
    }
}
