<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PositionTypeFilter extends BaseFilter
{
    protected function filters(): array
    {
        return [
            'search' => $this->data->search,
            'sort' => [
                'by' => $this->data->sortBy,
                'direction' => $this->data->sortDirection,
            ],
        ];
    }

    protected function search(Builder $query, string $value): void
    {
        $query->where(function (Builder $query) use ($value) {
            $query->where('id', $value)
                ->orWhere('name', 'like', "%{$value}%");
        });
    }

    protected function sort(Builder $query, array $sort): void
    {
        $allowedSorts = [
            'id',
            'name',
            'created_at',
        ];

        $sortBy = in_array($sort['by'], $allowedSorts)
            ? $sort['by']
            : 'id';

        $direction = $sort['direction'] === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sortBy, $direction);
    }
}