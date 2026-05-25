@extends('layouts.main')

@section('content')


    <div class="max-w-7xl mx-auto p-6">

        <a href="{{ route('calendar.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Назад к календарю
        </a>
        <a href="{{ route('calendar.matrix.index', $employee->employeePosition?->commissariatPosition?->commissariat_id ?? 1) }}"
            class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Назад к матрице
        </a>

        <div class="flex items-center gap-2 text-xs text-gray-500">
            <span>График сотрудника на день</span>
        </div>



        @include('admin.calendar.schedule.components.timeline.timeline-header', [
            'employee' => $employee,
            'date' => $date,
        ])

        <div
            class="
        bg-white
        rounded-3xl
        border
        shadow-sm
        overflow-hidden
        mt-6
    ">
            <div class="flex h-[900px] overflow-y-auto">

                @include('admin.calendar.schedule.components.timeline.timeline-hours')

                @include('admin.calendar.schedule.components.timeline.timeline-grid', [
                    'blocks' => $blocks,
                    'date' => $date,
                ])

            </div>
        </div>

    </div>
@endsection
