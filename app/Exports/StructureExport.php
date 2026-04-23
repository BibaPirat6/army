<?php

namespace App\Exports;

use App\Models\Commissariat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StructureExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $commissariat;
    
    public function __construct(Commissariat $commissariat)
    {
        $this->commissariat = $commissariat;
    }
    
    public function collection()
    {
        $rows = collect();
        
        // 1. Информация о комиссариате
        $rows->push((object)[
            'type' => 'КОМИССАРИАТ',
            'name' => $this->commissariat->name,
            'parent' => '',
            'chief' => $this->commissariat->chief->person->full_name ?? '-',
            'employees_count' => $this->commissariat->employeesIndependent()->count()
        ]);
        
        // 2. Отделы
        foreach ($this->commissariat->departments as $department) {
            $rows->push((object)[
                'type' => '  ОТДЕЛ',
                'name' => $department->name,
                'parent' => $this->commissariat->name,
                'chief' => $department->chief->person->full_name ?? '-',
                'employees_count' => $department->employees->count()
            ]);
            
            // 3. Отделения в отделах
            foreach ($department->divisions as $division) {
                $rows->push((object)[
                    'type' => '    ОТДЕЛЕНИЕ',
                    'name' => $division->name,
                    'parent' => $department->name,
                    'chief' => $division->chief->person->full_name ?? '-',
                    'employees_count' => $division->employees->count()
                ]);
            }
        }
        
        // 4. Самостоятельные отделения (без отдела)
        foreach ($this->commissariat->divisionsIndependent()->get() as $division) {
            $rows->push((object)[
                'type' => '  ОТДЕЛЕНИЕ (самостоятельное)',
                'name' => $division->name,
                'parent' => $this->commissariat->name,
                'chief' => $division->chief->person->full_name ?? '-',
                'employees_count' => $division->employees->count()
            ]);
        }
        
        return $rows;
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
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(15);
        
        return [1 => ['font' => ['bold' => true, 'size' => 11]]];
    }
    
    public function title(): string
    {
        return 'Структура_' . substr($this->commissariat->name, 0, 20);
    }
}