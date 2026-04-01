@props(['employee', 'backUrl'])

<div class="employee relative group">
    <a href="{{ route('employees.show', ['id' => $employee->id, 'back_url' => $backUrl]) }}" class="block">
        <div class="employee__data">
            <div class="employee__fio text-white text-xs">
                <p>{{ $employee->getFullNameAttribute() ?? 'Нет данных' }}</p>
            </div>
        </div>
    </a>
</div>