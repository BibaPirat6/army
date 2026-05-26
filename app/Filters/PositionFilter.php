<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PositionFilter extends BaseFilter
{
    protected function filters(): array
    {
        return [
            'search' => $this->data->search,

            'positionType' => $this->data->positionTypeId,

            'chiefType' => $this->data->chiefTypeId,

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
        $query->where(function (Builder $query) use ($value) {

            $query->where('positions.id', $value)

                ->orWhere('positions.name', 'like', "%{$value}%")

                ->orWhereHas('positionType', function (
                    Builder $query,
                ) use ($value) {
                    $query->where(
                        'name',
                        'like',
                        "%{$value}%"
                    );
                })

                ->orWhereHas('chiefType', function (
                    Builder $query,
                ) use ($value) {
                    $query->where(
                        'name',
                        'like',
                        "%{$value}%"
                    );
                });
        });
    }

    protected function positionType(
        Builder $query,
        int $value,
    ): void {
        $query->where('position_type_id', $value);
    }

    protected function chiefType(
        Builder $query,
        int $value,
    ): void {
        $query->where('chief_type_id', $value);
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

        $query->orderBy($sortBy, $direction);
    }
}