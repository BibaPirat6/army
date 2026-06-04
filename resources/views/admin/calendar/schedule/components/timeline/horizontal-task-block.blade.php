<div 
    class="absolute top-2 rounded-xl shadow-sm border-2 border-white/20 cursor-pointer
           hover:shadow-lg hover:scale-[1.02] hover:z-30 transition-all duration-200
           overflow-hidden group" overflow-hidden group" title="{{ $block['title'] }} {{ $block['start'] }}-{{ $block['end'] }} ({{ $block['duration'] }} мин)"
    style="
        left: {{ $leftPercent }}%;
        width: {{ max($widthPercent, 1) }}%;
        background: {{ $block['color'] }};
        height: 80px;
    "
    onclick="openModal({{ $block['assignment_id'] }}, '{{ $date->toDateString() }}')"
>
    {{-- Прогресс-бар сверху --}}
    <div class="absolute top-0 left-0 right-0 h-1 bg-white/30">
        <div class="h-full bg-white/50" style="width: {{ rand(20, 100) }}%"></div>
    </div>

    {{-- Контент --}}
    <div class="p-2 h-full flex flex-col justify-center">
        <div class="text-white font-semibold text-xs truncate">
            {{ $block['title'] }}
        </div>
        
        @if($widthPercent > 3)
            <div class="text-white/80 text-xs mt-1">
                {{ $block['start'] }} – {{ $block['end'] }}
            </div>
            <div class="text-white/70 text-xs">
                {{ $block['duration'] }} мин
            </div>
        @endif
    </div>

    {{-- Ховер-эффект --}}
    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors"></div>
</div>