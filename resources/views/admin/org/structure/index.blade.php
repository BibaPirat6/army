@extends('layouts.main')

@section('header-title')
    Комиссариаты
@endsection

@section('vite-resources')
    @vite(['resources/css/map.css'])
@endsection

@section('content')
    <div class="w-full px-4 md:px-6 py-4 md:py-6">
        <div class="flex flex-col xl:flex-row gap-6 w-full">

            <!-- ЛЕВАЯ КОЛОНКА: СПИСОК КОМИССАРИАТОВ -->
            <div class="xl:w-1/3 w-full">
                <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden sticky top-6">
                    <div class="p-5 md:p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h1 class="text-xl md:text-2xl font-bold text-[#060606]">Комиссариаты</h1>
                            <span class="bg-[#A60644] text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                                {{ $commissariats?->count() ?? 0 }}
                            </span>
                        </div>

                        @if ($commissariats?->count() > 0)
                            <div class="space-y-2 max-h-[calc(100vh-200px)] overflow-y-auto pr-1 custom-scrollbar">
                                @foreach ($commissariats as $commissariat)
                                    <div class="group bg-white rounded-xl border border-[#BFBFBF] hover:border-[#A60644] hover:bg-[#A60644]/5 transition-all duration-200 cursor-pointer"
                                        title="{{ $commissariat->name }} (координаты: X={{ $commissariat->longitude }}, Y={{ $commissariat->latitude }})"
                                        onclick="window.location.href='{{ route('structure.show', $commissariat->id) }}'">
                                        <div class="p-4 flex items-center justify-between">
                                            <div class="flex items-center flex-1 min-w-0">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-[#A60644]/10 flex items-center justify-center mr-3 group-hover:bg-[#A60644]/20 transition-colors">
                                                    <svg class="w-4 h-4 text-[#A60644]" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <span
                                                    class="text-[#060606] font-medium truncate group-hover:text-[#A60644] transition-colors">
                                                    {{ $commissariat->name }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 ml-2">
                                                <span
                                                    class="text-xs text-gray-400 font-mono bg-gray-100 px-2 py-1 rounded group-hover:bg-[#A60644]/10 group-hover:text-[#A60644] transition-colors"
                                                    title="ID комиссариата">
                                                    #{{ $commissariat->id }}
                                                </span>
                                                <svg class="w-4 h-4 text-gray-400 group-hover:text-[#A60644] transition-colors"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <!-- Дополнительная информация при наведении -->
                                        <div
                                            class="px-4 pb-3 pt-0 text-xs text-[#7F7F7F] border-t border-[#BFBFBF]/0 group-hover:border-[#BFBFBF]/30 transition-all duration-200 opacity-0 group-hover:opacity-100 max-h-0 group-hover:max-h-10 overflow-hidden">
                                            📍 Координаты: X={{ $commissariat->longitude }}, Y={{ $commissariat->latitude }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <p class="text-[#565A5B] text-lg font-medium">Нет доступных комиссариатов</p>
                                    <p class="text-[#7F7F7F] mt-1">Создайте первый комиссариат для начала работы</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ПРАВАЯ КОЛОНКА: КАРТА -->
            <div class="xl:w-2/3 w-full">
                <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
                    <div class="p-5 md:p-6">
                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                            <h2 class="text-lg font-semibold text-[#060606]">Карта комиссариатов</h2>
                            <div class="text-xs text-[#7F7F7F] bg-white px-3 py-1 rounded-full shadow-sm">
                                🖱️ Кликните по клетке для добавления
                            </div>
                        </div>

                        <div class="map-wrapper relative w-full overflow-x-auto rounded-xl">
                            <div class="relative inline-block w-full" style="min-width: 100%">
                                <img class="rounded-xl w-full h-auto" src="{{ asset('storage/map.jpg') }}" alt="map">

                                {{-- СЕТКА ДЛЯ HOVER И КЛИКА --}}
                                <div class="absolute inset-0 grid grid-cells">
                                    @for ($y = 0; $y < 120; $y++)
                                        @for ($x = 0; $x < 200; $x++)
                                            <div class="cell" data-x="{{ $x }}" data-y="{{ $y }}"
                                                title="X={{ $x }}, Y={{ $y }}"></div>
                                        @endfor
                                    @endfor
                                </div>

                                {{-- МАРКЕРЫ КОМИССАРИАТОВ --}}
                                <div class="absolute inset-0 grid-markers">
                                    @foreach ($commissariats as $commissariat)
                                        <div class="marker" data-id="{{ $commissariat->id }}"
                                            style="left: {{ ($commissariat->longitude / 200) * 100 }}%;
                                                   top: {{ ($commissariat->latitude / 120) * 100 }}%;"
                                            title="{{ $commissariat->name }} (X: {{ $commissariat->longitude }}, Y: {{ $commissariat->latitude }})">
                                            <div class="marker-dot"></div>
                                            <div class="marker-label">{{ $commissariat->id }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-center text-[#7F7F7F]">
                            💡 Наведите на ячейку для подсветки • Нажмите на ячейку чтобы создать комиссариат • Нажмите на
                            маркер чтобы редактировать
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    /* КАСТОМНЫЙ СКРОЛЛБАР */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #e7e1e1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #A60644;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #7a0433;
    }

    /* СТИЛИ ДЛЯ СЕТКИ */
    .grid-cells {
        display: grid;
        grid-template-columns: repeat(200, 1fr);
        grid-template-rows: repeat(120, 1fr);
        pointer-events: auto;
    }

    .cell {
        width: 100%;
        padding-bottom: 100%;
        transition: all 0.2s ease;
        cursor: pointer;
        background-color: transparent;
    }

    .cell:hover {
        background-color: rgba(166, 6, 68, 0.15);
        outline: 1px solid rgba(166, 6, 68, 0.3);
    }

    /* СТИЛИ ДЛЯ МАРКЕРОВ */
    .grid-markers {
        pointer-events: none;
    }

    .marker {
        position: absolute;
        transform: translate(-50%, -50%);
        cursor: pointer;
        pointer-events: auto;
        z-index: 20;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.2s ease;
    }

    .marker:hover {
        transform: translate(-50%, -50%) scale(1.1);
        z-index: 30;
    }

    .marker-dot {
        width: 12px;
        height: 12px;
        background-color: #A60644;
        border: 2px solid white;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        transition: all 0.2s ease;
    }

    .marker:hover .marker-dot {
        width: 16px;
        height: 16px;
        background-color: #c40752;
        box-shadow: 0 2px 8px rgba(166, 6, 68, 0.5);
    }

    .marker-label {
        background-color: #A60644;
        color: white;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 5px;
        border-radius: 10px;
        margin-top: 2px;
        white-space: nowrap;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .marker:hover .marker-label {
        background-color: #c40752;
        transform: scale(1.05);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // клики по ячейкам
        document.querySelectorAll('.cell').forEach(cell => {
            cell.addEventListener('click', () => {
                const x = cell.dataset.x;
                const y = cell.dataset.y;

                if (confirm(`Назначить комиссариат в X:${x}, Y:${y}?`)) {
                    const backUrl = encodeURIComponent(window.location.href);
                    window.location.href = "{{ route('commissariats.create') }}" +
                        `?x=${x}&y=${y}&back_url=${backUrl}`;
                }
            });
        });

        // клики по маркерам
        document.querySelectorAll('.marker').forEach(marker => {
            marker.addEventListener('click', (e) => {
                e.stopPropagation();
                const id = marker.dataset.id;
                const backUrl = encodeURIComponent(window.location.href);
                window.location.href = `/commissariats/${id}?back_url=${backUrl}`;
            });
        });
    });
</script>
