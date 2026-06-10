<?php

namespace App\DTO;

use Illuminate\Http\Request;

readonly class CommissariatPositionFiltersData
{
    public function __construct(
        public ?string $search,
        public ?string $vacancyStatus,
        public ?float $rateMin,
        public ?float $rateMax,
        public ?int $positionTypeId,
        public ?int $chiefTypeId,
        public ?int $commissariatId,
        public ?int $departmentId,
        public ?int $divisionId,
        public ?string $employeeStatus,
        public string $sortBy,
        public string $sortDirection,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string('search')->toString() ?: null,
            vacancyStatus: $request->string('vacancy_status')->toString() ?: null,
            // Устанавливаем значения по умолчанию только если фильтр активен
            rateMin: $request->filled('rate_min') ? (float) $request->rate_min : null,
            rateMax: $request->filled('rate_max') ? (float) $request->rate_max : null,
            positionTypeId: $request->integer('position_type_id') ?: null,
            chiefTypeId: $request->integer('chief_type_id') ?: null,
            commissariatId: $request->integer('commissariat_id') ?: null,
            departmentId: $request->integer('department_id') ?: null,
            divisionId: $request->integer('division_id') ?: null,
            employeeStatus: $request->string('employee_status')->toString() ?: null,
            sortBy: $request->string('sort_by')->toString() ?: 'id',
            sortDirection: $request->string('sort_direction')->toString() ?: 'desc',
        );
    }
}