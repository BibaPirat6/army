<div class="relative flex-1">

    {{-- GRID --}}

    @for ($hour = 0; $hour < 24; $hour++)
        <div class="
            h-[120px]
            border-b
            border-gray-100
        "></div>
    @endfor

    {{-- BLOCKS --}}

    @foreach ($blocks as $block)
        @if ($block['type'] === 'task')
            @include('admin.calendar.schedule.components.timeline.timeline-task-block', [
                'block' => $block,
                'date' => $date,
            ])
        @endif

        @if ($block['type'] === 'break')
            @include('admin.calendar.schedule.components.timeline.timeline-break-block', [
                'block' => $block,
            ])
        @endif
    @endforeach

</div>
