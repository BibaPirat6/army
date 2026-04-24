<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StructureExport implements FromArray, WithHeadings, WithTitle
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

    public function array(): array
    {
        $rows = [];

        if ($this->type === 'commissariat') {
            $commissariat = $this->data;
            
            // Заголовок комиссариата
            $rows[] = ['КОМИССАРИАТ', $commissariat->name, '', $this->getChiefName($commissariat->chief), ''];
            
            // Отделы
            foreach ($commissariat->departments as $department) {
                $rows[] = ['  ОТДЕЛ', $department->name, $commissariat->name, $this->getChiefName($department->chief), ''];
                
                // Отделения в отделах
                foreach ($department->divisions as $division) {
                    $rows[] = ['    ОТДЕЛЕНИЕ', $division->name, $department->name, $this->getChiefName($division->chief), ''];
                }
            }
            
            // Самостоятельные отделения
            $independentDivisions = $commissariat->divisions->filter(fn($d) => !$d->department_id);
            foreach ($independentDivisions as $division) {
                $rows[] = ['  ОТДЕЛЕНИЕ (самостоятельное)', $division->name, $commissariat->name, $this->getChiefName($division->chief), ''];
            }

        } elseif ($this->type === 'department') {
            $department = $this->data;
            
            // Комиссариат
            $rows[] = ['КОМИССАРИАТ', $department->commissariat->name, '', $this->getChiefName($department->commissariat->chief), ''];
            
            // Отдел
            $rows[] = ['  ОТДЕЛ', $department->name, $department->commissariat->name, $this->getChiefName($department->chief), ''];
            
            // Отделения
            foreach ($department->divisions as $division) {
                $rows[] = ['    ОТДЕЛЕНИЕ', $division->name, $department->name, $this->getChiefName($division->chief), ''];
            }

        } elseif ($this->type === 'division') {
            $division = $this->data;
            
            // Комиссариат
            $rows[] = ['КОМИССАРИАТ', $division->commissariat->name, '', $this->getChiefName($division->commissariat->chief), ''];
            
            // Отдел (если есть)
            if ($division->department) {
                $rows[] = ['  ОТДЕЛ', $division->department->name, $division->commissariat->name, $this->getChiefName($division->department->chief), ''];
            }
            
            // Отделение
            $prefix = $division->department ? '    ' : '  ';
            $suffix = $division->department ? '' : ' (самостоятельное)';
            $rows[] = [$prefix . 'ОТДЕЛЕНИЕ' . $suffix, $division->name, $division->department->name ?? $division->commissariat->name, $this->getChiefName($division->chief), ''];
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
        return ['Уровень', 'Название', 'В составе', 'Руководитель', 'Кол-во сотрудников'];
    }

    public function title(): string
    {
        return 'Структура_' . substr($this->name, 0, 20);
    }
}