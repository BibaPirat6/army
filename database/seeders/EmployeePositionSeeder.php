<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeePositionSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // 1. Загружаем данные
        // =====================================================================
        $employees = DB::table('employees')->orderBy('id')->get();
        $positions = DB::table('positions')->get();
        $chiefTypes = DB::table('chief_types')->pluck('name', 'id');
        $commissariats = DB::table('commissariats')->get();
        $departments = DB::table('departments')->get()->groupBy('commissariat_id');
        $divisionsInDept = DB::table('divisions')
            ->whereNotNull('department_id')
            ->get()
            ->groupBy('department_id');
        $independentDivisions = DB::table('divisions')
            ->whereNull('department_id')
            ->get()
            ->groupBy('commissariat_id');

        if ($employees->count() < 60) {
            $this->command->error('❌ Необходимо минимум 60 сотрудников!');
            return;
        }

        // =====================================================================
        // 2. Группируем позиции по chief_type
        // =====================================================================
        $byChief = $positions->groupBy(fn($p) => $chiefTypes[$p->chief_type_id] ?? '');
        
        $commHeadPos = $byChief['начальник комиссариата']?->first();
        $deptHeadPos = $byChief['начальник отдела']?->values() ?? collect();
        $divHeadPos = $byChief['начальник отделения']?->values() ?? collect();
        $workerPos = $byChief['работник']?->values() ?? collect();

        // Проверка минимального набора
        if (!$commHeadPos) {
            $this->command->error('❌ Нет должности "начальник комиссариата" в PositionSeeder!');
            return;
        }
        if ($deptHeadPos->count() < 1) {
            $this->command->error('❌ Нет должности "начальник отдела" в PositionSeeder!');
            return;
        }
        if ($divHeadPos->count() < 1) {
            $this->command->error('❌ Нет должности "начальник отделения" в PositionSeeder!');
            return;
        }
        if ($workerPos->count() < 1) {
            $this->command->error('❌ Нет должности "работник" в PositionSeeder!');
            return;
        }

        $this->command->info("📊 Доступно позиций: работник={$workerPos->count()}, нач.отделов={$deptHeadPos->count()}, нач.отделений={$divHeadPos->count()}");

        // =====================================================================
        // 3. Формируем назначения (ровно 60 записей)
        // =====================================================================
        $records = [];
        $now = now();
        $empIdx = 0;

        // --- 3.1. Начальники комиссариатов: 2 записи ---
        foreach ($commissariats as $comm) {
            $records[] = $this->makeRecord(
                employeeId: $employees[$empIdx++]->id,
                commissariatId: $comm->id,
                departmentId: null,
                divisionId: null,
                positionId: $commHeadPos->id
            );
        }

        // --- 3.2. Начальники отделов: 6 записей ---
        $deptHeadList = $deptHeadPos->toArray();
        $deptHeadIdx = 0;
        
        foreach ($commissariats as $comm) {
            $commDepts = $departments[$comm->id] ?? collect();
            foreach ($commDepts->take(3) as $dept) {
                $pos = $deptHeadList[$deptHeadIdx % count($deptHeadList)];
                $records[] = $this->makeRecord(
                    employeeId: $employees[$empIdx++]->id,
                    commissariatId: $comm->id,
                    departmentId: $dept->id,
                    divisionId: null,
                    positionId: $pos->id
                );
                $deptHeadIdx++;
            }
        }

        // --- 3.3. Начальники отделений: 16 записей ---
        $divHeadList = $divHeadPos->toArray();
        $divHeadIdx = 0;
        
        foreach ($commissariats as $comm) {
            $commDepts = $departments[$comm->id] ?? collect();
            $assignedInComm = 0;
            
            // 3.3.1. Начальники отделений внутри отделов: 12 (по 6 на комиссариат)
            foreach ($commDepts as $dept) {
                $deptDivs = $divisionsInDept[$dept->id] ?? collect();
                foreach ($deptDivs->take(2) as $div) {
                    if ($assignedInComm < 6) {
                        $pos = $divHeadList[$divHeadIdx % count($divHeadList)];
                        $records[] = $this->makeRecord(
                            employeeId: $employees[$empIdx++]->id,
                            commissariatId: $comm->id,
                            departmentId: $dept->id,
                            divisionId: $div->id,
                            positionId: $pos->id
                        );
                        $divHeadIdx++;
                        $assignedInComm++;
                    }
                }
            }
            
            // 3.3.2. Начальники самостоятельных отделений: 4 (по 2 на комиссариат)
            $indDivs = $independentDivisions[$comm->id] ?? collect();
            foreach ($indDivs->take(2) as $div) {
                if ($assignedInComm < 8) {
                    $pos = $divHeadList[$divHeadIdx % count($divHeadList)];
                    $records[] = $this->makeRecord(
                        employeeId: $employees[$empIdx++]->id,
                        commissariatId: $comm->id,
                        departmentId: null,
                        divisionId: $div->id,
                        positionId: $pos->id,
                        isIndependent: true
                    );
                    $divHeadIdx++;
                    $assignedInComm++;
                }
            }
        }

        // --- 3.4. Работники напрямую в комиссариате: 8 записей (4 на каждый) ---
        $workerList = $workerPos->toArray();
        $workerIdx = 0;
        
        // Если работников мало, используем те что есть по циклу
        if (count($workerList) === 0) {
            $this->command->warn('⚠️ Нет позиций типа "работник", пропускаем этот блок');
        } else {
            foreach ($commissariats as $comm) {
                for ($i = 0; $i < 4; $i++) {
                    if (count($records) >= 60) break;
                    
                    $records[] = $this->makeRecord(
                        employeeId: $employees[$empIdx++]->id,
                        commissariatId: $comm->id,
                        departmentId: null,
                        divisionId: null,
                        positionId: $workerList[$workerIdx % count($workerList)]->id
                    );
                    $workerIdx++;
                }
            }
        }

        // --- 3.5. Остальные работники: дополняем до 60 ---
        while (count($records) < 60 && $empIdx < $employees->count()) {
            if (count($workerList) === 0) {
                $this->command->error('❌ Недостаточно позиций "работник" для заполнения 60 записей!');
                break;
            }

            foreach ($commissariats as $comm) {
                if (count($records) >= 60) break;
                
                $commDepts = $departments[$comm->id] ?? collect();
                
                // Работники в обычных отделениях
                foreach ($commDepts as $dept) {
                    if (count($records) >= 60) break;
                    $deptDivs = $divisionsInDept[$dept->id] ?? collect();
                    foreach ($deptDivs as $div) {
                        if (count($records) >= 60) break;
                        
                        $records[] = $this->makeRecord(
                            employeeId: $employees[$empIdx++]->id,
                            commissariatId: $comm->id,
                            departmentId: $dept->id,
                            divisionId: $div->id,
                            positionId: $workerList[$workerIdx % count($workerList)]->id
                        );
                        $workerIdx++;
                    }
                }
                
                // Работники в самостоятельных отделениях
                $indDivs = $independentDivisions[$comm->id] ?? collect();
                foreach ($indDivs as $div) {
                    if (count($records) >= 60) break;
                    
                    $records[] = $this->makeRecord(
                        employeeId: $employees[$empIdx++]->id,
                        commissariatId: $comm->id,
                        departmentId: null,
                        divisionId: $div->id,
                        positionId: $workerList[$workerIdx % count($workerList)]->id,
                        isIndependent: true
                    );
                    $workerIdx++;
                }
            }
        }

        // =====================================================================
        // 4. Вставка в БД
        // =====================================================================
        foreach ($records as $rec) {
            DB::table('employee_positions')->updateOrInsert(
                [
                    'employee_id' => $rec['employee_id'],
                    'commissariat_id' => $rec['commissariat_id'],
                    'department_id' => $rec['department_id'],
                    'division_id' => $rec['division_id'],
                    'position_id' => $rec['position_id'],
                ],
                [
                    'employee_position_rate_id' => $rec['employee_position_rate_id'],
                    'employee_position_status_id' => $rec['employee_position_status_id'],
                    'is_independent' => $rec['is_independent'],
                    'updated_at' => $now,
                ]
            );
        }

        // =====================================================================
        // 5. Статистика
        // =====================================================================
        $directCount = 0;   // department_id = null AND division_id = null
        $deptCount = 0;     // department_id != null AND division_id = null
        $divCount = 0;      // division_id != null
        
        foreach ($records as $r) {
            if ($r['department_id'] === null && $r['division_id'] === null) {
                $directCount++;
            } elseif ($r['division_id'] === null) {
                $deptCount++;
            } else {
                $divCount++;
            }
        }

        $this->command->info('✓ Назначения созданы: '.count($records));
        $this->command->info('  └─ Прямо в комиссариате (dept=null, div=null): '.$directCount);
        $this->command->info('  └─ В отделах (dept!=null, div=null): '.$deptCount);
        $this->command->info('  └─ В отделениях (div!=null): '.$divCount);
    }

    private function makeRecord(
        int $employeeId,
        int $commissariatId,
        ?int $departmentId,
        ?int $divisionId,
        int $positionId,
        bool $isIndependent = false
    ): array {
        return [
            'employee_id' => $employeeId,
            'commissariat_id' => $commissariatId,
            'department_id' => $departmentId,
            'division_id' => $divisionId,
            'position_id' => $positionId,
            'employee_position_rate_id' => 4,
            'employee_position_status_id' => 1,
            'is_independent' => $isIndependent,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}