<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StructureExport implements WithMultipleSheets
{
    protected $data;
    protected $type;
    protected $name;
    
    public function __construct($data, string $type, string $name)
    {
        $this->data = $data;
        $this->type = $type;
        $this->name = $name;
    }
    
    public function sheets(): array
    {
        return [
            new StructureSheet($this->data, $this->type, $this->name),
            new PositionsSheet($this->data, $this->type, $this->name),
            new EmployeesSheet($this->data, $this->type, $this->name),
        ];
    }
}

// ========== ЛИСТ 1: СТРУКТУРА ==========
class StructureSheet implements \Maatwebsite\Excel\Concerns\FromCollection, 
                                \Maatwebsite\Excel\Concerns\WithHeadings, 
                                \Maatwebsite\Excel\Concerns\WithMapping,
                                \Maatwebsite\Excel\Concerns\WithTitle
{
    protected $data;
    protected $type;
    protected $name;
    
    public function __construct($data, string $type, string $name)
    {
        $this->data = $data;
        $this->type = $type;
        $this->name = $name;
    }
    
    public function collection()
    {
        $rows = collect();
        
        if ($this->type === 'commissariat') {
            $commissariat = $this->data;
            
            $rows->push((object)[
                'level' => 'КОМИССАРИАТ',
                'name' => $commissariat->name,
                'parent' => '-',
                'chief' => $this->getChiefName($commissariat->chief),
            ]);
            
            foreach ($commissariat->departments as $department) {
                $rows->push((object)[
                    'level' => '  ОТДЕЛ',
                    'name' => $department->name,
                    'parent' => $commissariat->name,
                    'chief' => $this->getChiefName($department->chief),
                ]);
                
                foreach ($department->divisions as $division) {
                    $rows->push((object)[
                        'level' => '    ОТДЕЛЕНИЕ',
                        'name' => $division->name,
                        'parent' => $department->name,
                        'chief' => $this->getChiefName($division->chief),
                    ]);
                }
            }
            
            foreach ($commissariat->divisions as $division) {
                if (!$division->department_id) {
                    $rows->push((object)[
                        'level' => '  ОТДЕЛЕНИЕ (самостоятельное)',
                        'name' => $division->name,
                        'parent' => $commissariat->name,
                        'chief' => $this->getChiefName($division->chief),
                    ]);
                }
            }
            
        } elseif ($this->type === 'department') {
            $department = $this->data;
            
            $rows->push((object)[
                'level' => 'КОМИССАРИАТ',
                'name' => $department->commissariat->name,
                'parent' => '-',
                'chief' => $this->getChiefName($department->commissariat->chief),
            ]);
            
            $rows->push((object)[
                'level' => '  ОТДЕЛ',
                'name' => $department->name,
                'parent' => $department->commissariat->name,
                'chief' => $this->getChiefName($department->chief),
            ]);
            
            foreach ($department->divisions as $division) {
                $rows->push((object)[
                    'level' => '    ОТДЕЛЕНИЕ',
                    'name' => $division->name,
                    'parent' => $department->name,
                    'chief' => $this->getChiefName($division->chief),
                ]);
            }
            
        } elseif ($this->type === 'division') {
            $division = $this->data;
            
            $rows->push((object)[
                'level' => 'КОМИССАРИАТ',
                'name' => $division->commissariat->name,
                'parent' => '-',
                'chief' => $this->getChiefName($division->commissariat->chief),
            ]);
            
            if ($division->department) {
                $rows->push((object)[
                    'level' => '  ОТДЕЛ',
                    'name' => $division->department->name,
                    'parent' => $division->commissariat->name,
                    'chief' => $this->getChiefName($division->department->chief),
                ]);
            }
            
            $prefix = $division->department ? '    ' : '  ';
            $suffix = $division->department ? '' : ' (самостоятельное)';
            $rows->push((object)[
                'level' => $prefix . 'ОТДЕЛЕНИЕ' . $suffix,
                'name' => $division->name,
                'parent' => $division->department->name ?? $division->commissariat->name,
                'chief' => $this->getChiefName($division->chief),
            ]);
        }
        
        return $rows;
    }
    
    private function getChiefName($chief): string
    {
        if (!$chief) return '-';
        return $chief->person->full_name ?? '-';
    }
    
    public function headings(): array
    {
        return ['Уровень', 'Название', 'В составе', 'Руководитель'];
    }
    
    public function map($row): array
    {
        return [$row->level, $row->name, $row->parent, $row->chief];
    }
    
    public function title(): string
    {
        return '1. Структура';
    }
}

// ========== ЛИСТ 2: ШТАТНЫЕ ДОЛЖНОСТИ ==========
class PositionsSheet implements \Maatwebsite\Excel\Concerns\FromCollection, 
                                \Maatwebsite\Excel\Concerns\WithHeadings, 
                                \Maatwebsite\Excel\Concerns\WithMapping,
                                \Maatwebsite\Excel\Concerns\WithTitle
{
    protected $data;
    protected $type;
    
    public function __construct($data, string $type, string $name)
    {
        $this->data = $data;
        $this->type = $type;
    }
    
    public function collection()
    {
        if ($this->type === 'commissariat') {
            return $this->data->commissariatPositions;
        } elseif ($this->type === 'department') {
            return $this->data->commissariatPositions;
        } elseif ($this->type === 'division') {
            return $this->data->commissariatPositions;
        }
        return collect();
    }
    
    public function headings(): array
    {
        return [
            'ID',
            'Уровень',
            'Подразделение',
            'Должность',
            'Общая ставка',
            'Занято ставок',
            'Свободно ставок',
            'Самостоятельная'
        ];
    }
    
    public function map($position): array
    {
        if ($position->division_id) {
            $level = 'Отделение';
            $subdivision = $position->division->name ?? '-';
        } elseif ($position->department_id) {
            $level = 'Отдел';
            $subdivision = $position->department->name ?? '-';
        } else {
            $level = 'Комиссариат';
            $subdivision = $position->commissariat->name ?? '-';
        }
        
        $totalRate = (float) ($position->position_rate ?? 1);
        $occupiedRate = 0;
        
        foreach ($position->employeePositions as $empPos) {
            if ($empPos->employee_position_status_id == 1) {
                $occupiedRate += (float) ($empPos->rate ?? $empPos->rate_percent / 100 ?? 0);
            }
        }
        
        $freeRate = $totalRate - $occupiedRate;
        
        return [
            $position->id,
            $level,
            $subdivision,
            $position->position->name ?? '-',
            $this->formatRate($totalRate),
            $this->formatRate($occupiedRate),
            $this->formatRate($freeRate),
            $position->is_independent ? 'Да' : 'Нет'
        ];
    }
    
    private function formatRate($rate): string
    {
        if ($rate == 0) return '0';
        if ($rate == floor($rate)) return (string) $rate;
        return number_format($rate, 2, '.', '');
    }
    
    public function title(): string
    {
        return '2. Штатные должности';
    }
}

// ========== ЛИСТ 3: СОТРУДНИКИ НА ДОЛЖНОСТЯХ ==========
class EmployeesSheet implements \Maatwebsite\Excel\Concerns\FromCollection, 
                                \Maatwebsite\Excel\Concerns\WithHeadings, 
                                \Maatwebsite\Excel\Concerns\WithMapping,
                                \Maatwebsite\Excel\Concerns\WithTitle
{
    protected $data;
    protected $type;
    
    public function __construct($data, string $type, string $name)
    {
        $this->data = $data;
        $this->type = $type;
    }
    
    public function collection()
    {
        $rows = collect();
        
        if ($this->type === 'commissariat') {
            $positions = $this->data->commissariatPositions;
        } elseif ($this->type === 'department') {
            $positions = $this->data->commissariatPositions;
        } elseif ($this->type === 'division') {
            $positions = $this->data->commissariatPositions;
        } else {
            $positions = collect();
        }
        
        foreach ($positions as $position) {
            if ($position->division_id) {
                $level = 'Отделение';
                $subdivision = $position->division->name ?? '-';
            } elseif ($position->department_id) {
                $level = 'Отдел';
                $subdivision = $position->department->name ?? '-';
            } else {
                $level = 'Комиссариат';
                $subdivision = $position->commissariat->name ?? '-';
            }
            
            if ($position->employeePositions->isNotEmpty()) {
                foreach ($position->employeePositions as $empPos) {
                    if ($empPos->employee_position_status_id == 1) {
                        $employee = $empPos->employee;
                        $rows->push((object)[
                            'position_name' => $position->position->name ?? '-',
                            'position_level' => $level,
                            'subdivision' => $subdivision,
                            'employee_id' => $employee->id ?? '-',
                            'employee_name' => $employee->person->full_name ?? '-',
                            'rate' => (float) ($empPos->rate ?? $empPos->rate_percent / 100 ?? 0),
                            'status' => $empPos->status->name ?? 'Работает',
                            'phone' => $employee->person->phone ?? '-',
                            'email' => $employee->user->email ?? '-',
                        ]);
                    }
                }
            } else {
                $rows->push((object)[
                    'position_name' => $position->position->name ?? '-',
                    'position_level' => $level,
                    'subdivision' => $subdivision,
                    'employee_id' => '-',
                    'employee_name' => 'Нет сотрудника',
                    'rate' => '-',
                    'status' => '-',
                    'phone' => '-',
                    'email' => '-',
                ]);
            }
        }
        
        return $rows;
    }
    
    public function headings(): array
    {
        return [
            'Должность',
            'Уровень',
            'Подразделение',
            'ID сотрудника',
            'ФИО сотрудника',
            'Ставка',
            'Статус',
            'Телефон',
            'Email'
        ];
    }
    
    public function map($row): array
    {
        return [
            $row->position_name,
            $row->position_level,
            $row->subdivision,
            $row->employee_id,
            $row->employee_name,
            is_numeric($row->rate) ? $this->formatRate($row->rate) : $row->rate,
            $row->status,
            $row->phone,
            $row->email,
        ];
    }
    
    private function formatRate($rate): string
    {
        if ($rate == 0) return '0';
        if ($rate == floor($rate)) return (string) $rate;
        return number_format($rate, 2, '.', '');
    }
    
    public function title(): string
    {
        return '3. Сотрудники';
    }
}