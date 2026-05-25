<?php

namespace App\DTO\PositionType;

readonly class PositionTypeFiltersData
{
    public function __construct(
        public ?string $search = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
        public int $perPage = 20,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            sort: $data['sort'] ?? 'created_at',
            direction: $data['direction'] ?? 'desc',
            perPage: (int) ($data['per_page'] ?? 20),
        );
    }
}