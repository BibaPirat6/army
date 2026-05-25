@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto p-6">

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

                @include(
    'admin.calendar.schedule.components.timeline.timeline-hours'
)

              @include(
    'admin.calendar.schedule.components.timeline.timeline-grid',
    [
        'blocks' => $blocks,
        'date' => $date,
    ]
)

            </div>
        </div>

    </div>
@endsection
