<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter
{
    public function __construct(
        protected readonly object $data,
    ) {}

    public function apply(Builder $query): Builder
    {
        foreach ($this->filters() as $method => $value) {
            if ($this->shouldSkip($value)) {
                continue;
            }

            if (! method_exists($this, $method)) {
                continue;
            }

            $this->{$method}($query, $value);
        }

        return $query;
    }

    abstract protected function filters(): array;

    protected function shouldSkip(mixed $value): bool
    {
        return $value === null
            || $value === '';
    }
}