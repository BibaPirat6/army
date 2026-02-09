@extends('layouts.main')

@section('header-title')
    Комиссариаты
@endsection

@section('vite-resources')
    @vite(['resources/css/map.css'])
@endsection

@section(section: 'content')

    <div class="max-w-4xl p-6 mx-auto">
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <h1 class="text-2xl font-bold text-[#060606] mb-8">Комиссариаты</h1>

                @if ($commissariats?->count() > 0)
                    <ul class="space-y-3">
                        @foreach ($commissariats as $commissariat)
                            <li
                                class="bg-white rounded-lg border border-[#BFBFBF] p-4 hover:bg-[#A60644]/5 transition-colors duration-200">
                                <a href="{{ route('structure.show', $commissariat->id) }}"
                                    class="inline-flex items-center text-[#060606] font-medium hover:text-[#A60644] transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-3 text-[#A60644]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    {{ $commissariat->name }}

                                    <span>
                                        ⠀(ID: {{ $commissariat->id }})
                                    </span>

                                    <svg class="w-4 h-4 ml-2 text-[#A60644]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>

                                    <p>
                                        ⠀(X: {{ $commissariat->longitude }}
                                        Y: {{ $commissariat->latitude }})
                                    </p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
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
                            <p class="text-[#7F7F7F] mt-1">Создайте первый комиссариат для начала работы со структурой
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="map-wrapper max-w-6xl mx-auto">
        <img class="rounded-2xl" src="{{ asset('storage/map.jpg') }}" alt="map">

        {{-- СЕТКА ДЛЯ HOVER И КЛИКА --}}
        <div class="grid grid-cells">
            @for ($y = 0; $y < 60; $y++)
                @for ($x = 0; $x < 100; $x++)
                    <div class="cell" data-x="{{ $x }}" data-y="{{ $y }}"></div>
                @endfor
            @endfor
        </div>

        {{-- МАРКЕРЫ КОМИССАРИАТОВ --}}
        <div class="grid grid-markers">
            @foreach ($commissariats as $commissariat)
                <div class="marker" data-id="{{ $commissariat->id }}"
                    style="
                    left: {{ ($commissariat->longitude / 100) * 100 }}%;
                    top: {{ ($commissariat->latitude / 60) * 100 }}%;
                "
                    title="{{ $commissariat->name }}">
                    {{ $commissariat->id }}
                </div>
            @endforeach
        </div>
    </div>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // клики по ячейкам
        document.querySelectorAll('.cell').forEach(cell => {
            cell.addEventListener('click', () => {
                const x = cell.dataset.x;
                const y = cell.dataset.y;
                

                if (confirm(`Назначить комиссариат в X:${x}, Y:${y}?`)) {
                    const backUrl = encodeURIComponent(window.location.href); 
                    window.location.href =
                        "{{ route('commissariats.create') }}" + `?x=${x}&y=${y}&back_url=${backUrl}`;
                }
            });
        });

        // клики по маркерам
        document.querySelectorAll('.marker').forEach(marker => {
            marker.addEventListener('click', (e) => {
                e.stopPropagation();

                const id = marker.dataset.id;
                const backUrl = encodeURIComponent(window.location.href); 

                const url = `/commissariats/${id}?back_url=${backUrl}`;

                window.location.href = url;
            });
        });

    });
</script>
