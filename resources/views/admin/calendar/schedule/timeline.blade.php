@extends('layouts.main')

@section('content')

<div class="max-w-7xl mx-auto p-6">

    <div class="mb-6 flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Таймлайн сотрудника
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                {{ $employee->full_name }}
            </p>
        </div>

        <div class="flex items-center gap-3">

            <a
                href="?date={{ $date->copy()->subDay()->toDateString() }}"
                class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50"
            >
                ←
            </a>

            <div class="font-semibold text-gray-700">
                {{ $date->format('d.m.Y') }}
            </div>

            <a
                href="?date={{ $date->copy()->addDay()->toDateString() }}"
                class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50"
            >
                →
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">

        <div class="flex h-[900px] overflow-y-auto">

            {{-- TIME COLUMN --}}
            <div class="w-24 shrink-0 border-r bg-gray-50">

                @for($hour = 0; $hour < 24; $hour++)

                    <div
                        class="h-[120px] border-b relative text-xs text-gray-500"
                    >
                        <div class="absolute top-0 left-3">
                            {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                        </div>
                    </div>

                @endfor
            </div>

            {{-- TIMELINE --}}
            <div class="relative flex-1">

                {{-- GRID --}}
                @for($hour = 0; $hour < 24; $hour++)

                    <div
                        class="h-[120px] border-b border-gray-100"
                    ></div>

                @endfor

                {{-- BLOCKS --}}
                @foreach($blocks as $block)

                    @if($block['type'] === 'task')

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

                    @endif

                    @if($block['type'] === 'break')

                        <div
                            class="
                                absolute
                                left-4
                                right-4
                                rounded-2xl
                                border
                            "

                            style="
                                top: {{ $block['top'] }}px;
                                height: {{ $block['height'] }}px;

                                background-image:
                                repeating-linear-gradient(
                                    45deg,
                                    #fef3c7,
                                    #fef3c7 10px,
                                    #fde68a 10px,
                                    #fde68a 20px
                                );
                            "
                        >
                            <div
                                class="
                                    text-xs
                                    font-medium
                                    text-amber-800
                                    p-2
                                "
                            >
                                Перерыв
                            </div>
                        </div>

                    @endif

                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection