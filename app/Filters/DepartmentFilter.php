<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class DepartmentFilter extends BaseFilter
{
    protected function filters(): array
    {
        return [
            'search' => $this->data->search,

            'commissariat' => $this->data->commissariatId,

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

            $query->where(
                'departments.id',
                $value
            )

            ->orWhere(
                'departments.name',
                'like',
                "%{$value}%"
            )

            ->orWhereHas(
                'commissariat',
                function (
                    Builder $query,
                ) use ($value) {

                    $query->where(
                        'name',
                        'like',
                        "%{$value}%"
                    );
                }
            );
        });
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

    protected function sort(
        Builder $query,
        array $sort,
    ): void {

        $allowedSorts = [
            'id',
            'name',
            'created_at',
        ];

        $sortBy = in_array(
            $sort['by'],
            $allowedSorts
        )
            ? $sort['by']
            : 'id';

        $direction = $sort['direction'] === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy(
            $sortBy,
            $direction
        );
    }
}