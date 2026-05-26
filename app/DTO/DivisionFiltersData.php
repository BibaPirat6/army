<?php

namespace App\DTO;

use Illuminate\Http\Request;

readonly class DivisionFiltersData
{
    public function __construct(
        public ?string $search,

        public ?int $commissariatId,

        public ?int $departmentId,

        public string $sortBy,

        public string $sortDirection,
    ) {}

    public static function fromRequest(
        Request $request,
    ): self {
        return new self(
            search: $request->string('search')
                ->toString() ?: null,

            commissariatId: $request->integer(
                'commissariat_id'
            ) ?: null,

            departmentId: $request->integer(
                'department_id'
            ) ?: null,

            sortBy: $request->string('sort_by')
                ->toString() ?: 'id',

            sortDirection: $request->string(
                'sort_direction'
            )->toString() ?: 'desc',
        );
    }
}