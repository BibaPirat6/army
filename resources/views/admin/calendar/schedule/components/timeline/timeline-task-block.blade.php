<button
    onclick="openModal(
        {{ $block['assignment_id'] }},
        '{{ $date->toDateString() }}'
    )"

    class="
        absolute
        left-4
        right-4
        rounded-2xl
        shadow-sm
        border
        transition-all
        hover:scale-[1.01]
        hover:z-20
        text-left
        p-3
        overflow-hidden
    "

    style="
        top: {{ $block['top'] }}px;
        height: {{ $block['height'] }}px;
        background: {{ $block['color'] }};
        border-color: {{ $block['color'] }};
    "
>
    <div class="text-white">

        <div class="font-semibold text-sm">
            {{ $block['title'] }}
        </div>

        @if($block['height'] > 50)

            <div class="text-xs opacity-90 mt-1">

                {{ $block['start'] }}
                —
                {{ $block['end'] }}

            </div>

        @endif

    </div>
</button>