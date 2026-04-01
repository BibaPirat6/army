@props(['division', 'commissariat', 'backUrl'])

<div class="unit">
    <div class="unit-title font-bold text-white mb-2">{{ $division->name }}</div>
    
    <x-structure-node 
        :route="route('divisions.show', ['id' => $division->id, 'back_url' => $backUrl])"
        :name="optional($division->getChiefAttribute())?->getFullNameAttribute() ?? 'Не назначен начальник'"
    />

    @if($division->employeePositions->count())
        <div class="employees">
            @foreach($division->employeePositions as $employeePosition)
                <x-employee-card 
                    :employee="$employeePosition->employee"
                    :backUrl="$backUrl"
                />
            @endforeach
            
            <x-add-button 
                :route="route('employees.create', [
                    'commissariat_id' => $commissariat->id, 
                    'division_id' => $division->id, 
                    'back_url' => $backUrl
                ])"
                text="+"
                class="inline-flex items-center justify-center w-10 h-10 bg-[#A60644] text-white rounded-lg hover:bg-[#A60644]/80"
            />
        </div>
    @else
        <x-add-button 
            :route="route('employees.create', [
                'commissariat_id' => $commissariat->id, 
                'division_id' => $division->id, 
                'back_url' => $backUrl
            ])"
            text="+ Добавить сотрудника"
            class="mt-2 w-full"
        />
    @endif
</div>