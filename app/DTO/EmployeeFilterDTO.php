<?php
// app/DTO/EmployeeFilterDTO.php

namespace App\DTO;

class EmployeeFilterDTO
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $employeeStatus = null,      // статус сотрудника (работает/отпуск/декрет)
        public readonly ?string $userRole = null,            // роль пользователя (admin/user)
        public readonly ?int $commissariatId = null,         // комиссариат
        public readonly ?int $departmentId = null,
        public readonly ?int $divisionId = null,
        public readonly ?float $rate = null,                 // конкретная ставка (0.25, 0.5, 1, 1.5, 2)
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
            rate: $request->input('rate') ? (float)$request->input('rate') : null,
            sortBy: $request->input('sort_by', 'id'),
            sortDirection: $request->input('sort_direction', 'desc'),
        );
    }
}