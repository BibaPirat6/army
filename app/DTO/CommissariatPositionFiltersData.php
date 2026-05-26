<?php

namespace App\DTO;

use Illuminate\Http\Request;

readonly class CommissariatPositionFiltersData
{
    public function __construct(
        public ?string $search,

        public ?string $vacancyStatus,

        public ?float $rate,

        public ?int $positionTypeId,

        public ?int $chiefTypeId,

        public ?int $commissariatId,

        public ?int $departmentId,

        public ?int $divisionId,

        public ?int $employeePositionStatusId,

        public string $sortBy,

        public string $sortDirection,
    ) {}

    public static function fromRequest(
        Request $request,
    ): self {

        return new self(
            search: $request->string('search')
                ->toString() ?: null,

            vacancyStatus: $request->string(
                'vacancy_status'
            )->toString() ?: null,

            rate: $request->filled('rate')
                ? (float) $request->rate
                : null,

            positionTypeId: $request->integer(
                'position_type_id'
            ) ?: null,

            chiefTypeId: $request->integer(
                'chief_type_id'
            ) ?: null,

            commissariatId: $request->integer(
                'commissariat_id'
            ) ?: null,

            departmentId: $request->integer(
                'department_id'
            ) ?: null,

            divisionId: $request->integer(
                'division_id'
            ) ?: null,

            employeePositionStatusId: $request->integer(
                'employee_position_status_id'
            ) ?: null,

            sortBy: $request->string('sort_by')
                ->toString() ?: 'id',

            sortDirection: $request->string(
                'sort_direction'
            )->toString() ?: 'desc',
        );
    }
}