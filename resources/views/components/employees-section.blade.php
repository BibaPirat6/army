@props(['employees', 'commissariat', 'backUrl', 'title', 'isIndependent' => false])

<div class="bg-[#BFBFBF] p-5 rounded-lg flex-shrink-0">
    <h3 class="text-lg font-semibold mb-3">{{ $title }}</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($employees as $employee)
            <x-employee-card :employee="$employee" :backUrl="$backUrl" />
        @endforeach
        
        <x-add-button 
            :route="route('employees.create', array_merge(['commissariat_id' => $commissariat->id, 'back_url' => $backUrl], $isIndependent ? ['is_independent' => 1] : []))"
            text="Добавить сотрудника"
        />
    </div>
</div>