<?php
// app/DTO/EmployeeFilterDTO.php

namespace App\DTO;

class EmployeeFilterDTO
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $employeeStatus = null,
        public readonly ?string $userRole = null,
        public readonly ?int $commissariatId = null,
        public readonly ?int $departmentId = null,
        public readonly ?int $divisionId = null,
        public readonly ?float $rateMin = null,     // минимальная ставка
        public readonly ?float $rateMax = null,     // максимальная ставка
        public readonly ?string $sortBy = 'id',
        public readonly ?string $sortDirection = 'desc',
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            search: $request->input('search'),
            employeeStatus: $request->input('employee_status'),
            userRole: $request->input('user_role'),
            commissariatId: $request->input('commissariat_id') ? (int)$request->input('commissariat_id') : null,
            departmentId: $request->input('department_id') ? (int)$request->input('department_id') : null,
            divisionId: $request->input('division_id') ? (int)$request->input('division_id') : null,
            rateMin: $request->input('rate_min') ? (float)$request->input('rate_min') : null,
            rateMax: $request->input('rate_max') ? (float)$request->input('rate_max') : null,
            sortBy: $request->input('sort_by', 'id'),
            sortDirection: $request->input('sort_direction', 'desc'),
        );
    }
}