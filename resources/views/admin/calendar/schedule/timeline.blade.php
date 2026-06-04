@extends('layouts.main')




<style>
    /* Плавные переходы для всех интерактивных элементов */
.timeline-block {
    @apply transition-all duration-200 ease-in-out;
}

.timeline-block:hover {
    @apply shadow-xl -translate-y-1;
    filter: brightness(1.1);
}

/* Анимация появления блоков */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.horizontal-timeline .absolute {
    animation: slideIn 0.3s ease-out forwards;
}

/* Стили для линии текущего времени */
.current-time-line {
    @apply absolute w-0.5 bg-red-500 z-20;
    box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
}

.current-time-line::before {
    content: '';
    @apply absolute -left-1 -top-1 w-2.5 h-2.5 bg-red-500 rounded-full;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
}
</style>

@section('content')
<div class="max-w-full mx-auto p-6">

    {{-- Навигация --}}
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('calendar.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Календарь
        </a>
        <a href="{{ route('calendar.schedule.employee', $employee->id) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            График сотрудника
        </a>
    </div>

    @include('admin.calendar.schedule.components.timeline.timeline-header', [
        'employee' => $employee,
        'date' => $date,
    ])

    @include('admin.calendar.schedule.components.timeline.horizontal-timeline', [
        'timeline' => $timeline,
        'date' => $date,
    ])

</div>
@endsection

@push('styles')
<style>
    .timeline-scroll::-webkit-scrollbar {
        height: 8px;
    }
    .timeline-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 8px;
    }
    .timeline-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 8px;
    }
    .timeline-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush