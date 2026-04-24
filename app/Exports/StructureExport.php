<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StructureExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
            
            // Комиссариат
            $rows->push((object)[
                'type' => 'КОМИССАРИАТ',
                'name' => $commissariat->name,
                'parent' => '',
                'chief' => $this->getChiefName($commissariat->chief),
                'employees_count' => $commissariat->employeesIndependent()->count()
            ]);
            
            // Отделы
            foreach ($commissariat->departments as $department) {
                $rows->push((object)[
                    'type' => '  ОТДЕЛ',
                    'name' => $department->name,
                    'parent' => $commissariat->name,
                    'chief' => $this->getChiefName($department->chief),
                    'employees_count' => $department->employees->count()
                ]);
                
                // Отделения в отделах
                foreach ($department->divisions as $division) {
                    $rows->push((object)[
                        'type' => '    ОТДЕЛЕНИЕ',
                        'name' => $division->name,
                        'parent' => $department->name,
                        'chief' => $this->getChiefName($division->chief),
                        'employees_count' => $division->employees->count()
                    ]);
                }
            }
            
            // Самостоятельные отделения
            foreach ($commissariat->divisions as $division) {
                if (!$division->department_id) {
                    $rows->push((object)[
                        'type' => '  ОТДЕЛЕНИЕ (самостоятельное)',
                        'name' => $division->name,
                        'parent' => $commissariat->name,
                        'chief' => $this->getChiefName($division->chief),
                        'employees_count' => $division->employees->count()
                    ]);
                }
            }
            
        } elseif ($this->type === 'department') {
            $department = $this->data;
            
            // Комиссариат (родитель)
            $rows->push((object)[
                'type' => 'КОМИССАРИАТ',
                'name' => $department->commissariat->name,
                'parent' => '',
                'chief' => $this->getChiefName($department->commissariat->chief),
                'employees_count' => '-'
            ]);
            
            // Отдел
            $rows->push((object)[
                'type' => '  ОТДЕЛ',
                'name' => $department->name,
                'parent' => $department->commissariat->name,
                'chief' => $this->getChiefName($department->chief),
                'employees_count' => $department->employees->count()
            ]);
            
            // Отделения в отделе
            foreach ($department->divisions as $division) {
                $rows->push((object)[
                    'type' => '    ОТДЕЛЕНИЕ',
                    'name' => $division->name,
                    'parent' => $department->name,
                    'chief' => $this->getChiefName($division->chief),
                    'employees_count' => $division->employees->count()
                ]);
            }
            
        } elseif ($this->type === 'division') {
            $division = $this->data;
            
            // Комиссариат
            $rows->push((object)[
                'type' => 'КОМИССАРИАТ',
                'name' => $division->commissariat->name,
                'parent' => '',
                'chief' => $this->getChiefName($division->commissariat->chief),
                'employees_count' => '-'
            ]);
            
            // Отдел (если есть)
            if ($division->department) {
                $rows->push((object)[
                    'type' => '  ОТДЕЛ',
                    'name' => $division->department->name,
                    'parent' => $division->commissariat->name,
                    'chief' => $this->getChiefName($division->department->chief),
                    'employees_count' => $division->department->employees->count()
                ]);
            }
            
            // Отделение
            $prefix = $division->department ? '    ' : '  ';
            $suffix = $division->department ? '' : ' (самостоятельное)';
            $rows->push((object)[
                'type' => $prefix . 'ОТДЕЛЕНИЕ' . $suffix,
                'name' => $division->name,
                'parent' => $division->department->name ?? $division->commissariat->name,
                'chief' => $this->getChiefName($division->chief),
                'employees_count' => $division->employees->count()
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
        return [
            'Уровень',
            'Название',
            'В составе',
            'Руководитель',
            'Кол-во сотрудников'
        ];
    }
    
    public function map($row): array
    {
        return [
            $row->type,
            $row->name,
            $row->parent,
            $row->chief,
            $row->employees_count
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(18);
        
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getStyle('1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('1')->getFill()->getStartColor()->setARGB('FFE0E0E0');
        
        return [1 => ['font' => ['bold' => true, 'size' => 11]]];
    }
    
    public function title(): string
    {
        return 'Структура_' . substr($this->name, 0, 20);
    }
}