<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Employee::with(['person', 'user.role'])->get();
    }

    public function headings(): array
    {
        return [
            'ID сотрудника',
            'Логин',
            'Роль',
            'Фамилия',
            'Имя',
            'Отчество',
            'ФИО полностью',
            'Участие в боевых действиях',
            'Дата рождения',
            'Возраст (полных лет)',
            'Наличие среднего образования',
            'Наличие высшего образования',
        ];
    }

    public function map($employee): array
    {
        // Получаем данные человека
        $person = $employee->person;
        
        // Фамилия, имя, отчество
        $lastName = $person->фамилия ?? '-';
        $firstName = $person->имя ?? '-';
        $middleName = $person->отчество ?? '-';
        
        // ФИО полностью
        $fullName = trim(implode(' ', [$lastName, $firstName, $middleName]));
        $fullName = $fullName !== '' ? $fullName : '-';
        
        // Дата рождения и возраст
        $birthDate = '-';
        $age = '-';
        
        if (!empty($person->дата_рождения)) {
            try {
                $birthDateObj = Carbon::parse($person->дата_рождения);
                $birthDate = $birthDateObj->format('d.m.Y');
                
                // Расчет возраста с учетом дня рождения в текущем году
                $age = $birthDateObj->age;
                
                // Альтернативный расчет (более точный)
                // $age = $birthDateObj->diffInYears(Carbon::now());
            } catch (\Exception $e) {
                $birthDate = '-';
                $age = '-';
            }
        }
        
        // Участие в боевых действиях
        $combatParticipation = $this->boolToYesNo($person->участие_в_боевых_действиях ?? null);
        
        // Образование
        $secondaryEducation = $this->boolToYesNo($person->наличие_среднего_образования ?? null);
        $higherEducation = $this->boolToYesNo($person->наличие_высшего_образования ?? null);
        
        return [
            $employee->id,
            $employee->user->login ?? '-',
            $employee->user->role->description ?? $employee->user->role->name ?? '-',
            $lastName,
            $firstName,
            $middleName,
            $fullName,
            $combatParticipation,
            $birthDate,
            $age,
            $secondaryEducation,
            $higherEducation,
        ];
    }
    
    /**
     * Преобразует булево значение в 'Да'/'Нет'
     */
    private function boolToYesNo($value): string
    {
        if ($value === null) {
            return '-';
        }
        
        // Если значение приходит как булево или число (0/1)
        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }
        
        // Если приходит как строка или число
        if (in_array($value, [1, '1', 'true', 'yes', 'on'], true)) {
            return 'Да';
        }
        
        if (in_array($value, [0, '0', 'false', 'no', 'off'], true)) {
            return 'Нет';
        }
        
        return '-';
    }
}