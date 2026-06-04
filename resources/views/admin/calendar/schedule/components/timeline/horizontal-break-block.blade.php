<div 
    class="absolute top-2 rounded-xl shadow-sm overflow-hidden group"
    style="
        left: {{ $leftPercent }}%;
        width: {{ max($widthPercent, 1) }}%;
        height: 80px;
        background: repeating-linear-gradient(
            45deg,
            #fef3c7 0px,
            #fef3c7 10px,
            #fde68a 10px,
            #fde68a 20px
        );
    "
>
    {{-- Индикатор слева --}}
    <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-400"></div>

    <div class="p-2 h-full flex flex-col justify-center">
        <div class="text-amber-800 font-semibold text-xs">
            ☕ Перерыв
        </div>
        
        @if($widthPercent > 3)
            <div class="text-amber-700/80 text-xs mt-1">
                {{ $block['start'] }} – {{ $block['end'] }}
            </div>
            <div class="text-amber-700/70 text-xs">
                {{ $block['duration'] }} мин
            </div>
        @else
            <div class="text-amber-700/70 text-xs mt-1">
                {{ $block['duration'] }}м
            </div>
        @endif
    </div>
</div>