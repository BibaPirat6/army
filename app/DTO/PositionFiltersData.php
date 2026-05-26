<?php

namespace App\DTO;

use Illuminate\Http\Request;

readonly class PositionFiltersData
{
    public function __construct(
        public ?string $search,

        public ?int $positionTypeId,
        public ?int $chiefTypeId,

        public string $sortBy,
        public string $sortDirection,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string('search')->toString() ?: null,

            positionTypeId: $request->integer('position_type_id') ?: null,

            chiefTypeId: $request->integer('chief_type_id') ?: null,

            sortBy: $request->string('sort_by')
                ->toString() ?: 'id',

            sortDirection: $request->string('sort_direction')
                ->toString() ?: 'desc',
        );
    }
}