<div class="bg-white rounded-3xl border shadow-sm overflow-hidden mt-6">
    
    {{-- Легенда --}}
    <div class="px-6 py-4 border-b bg-gray-50/50 flex items-center gap-6">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded bg-blue-500"></div>
            <span class="text-sm text-gray-600">Задачи</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded" style="background: repeating-linear-gradient(45deg, #fef3c7 0px, #fef3c7 4px, #fde68a 4px, #fde68a 8px);"></div>
            <span class="text-sm text-gray-600">Перерывы</span>
        </div>
    </div>

    {{-- Временная шкала --}}
    <div class="timeline-scroll overflow-x-auto">
        <div class="min-w-[1440px]">
            
            {{-- Часы --}}
            <div class="flex border-b border-gray-200">
                @php
                    $workStart = \Carbon\Carbon::parse($timeline['work_start']);
                    $workEnd = \Carbon\Carbon::parse($timeline['work_end']);
                    $totalMinutes = $workStart->diffInMinutes($workEnd);
                @endphp
                
                @for ($hour = $workStart->hour; $hour <= $workEnd->hour; $hour++)
                    <div class="flex-1 min-w-[120px] border-r border-gray-200 last:border-r-0">
                        {{-- Заголовок часа --}}
                        <div class="text-xs font-semibold text-gray-500 px-3 py-2 bg-gray-50 border-b border-gray-100 text-center">
                            {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                        </div>
                        
                        {{-- Подъячейки (12 шт по 5 минут) --}}
                        <div class="flex h-8">
                            @for ($minute = 0; $minute < 60; $minute += 5)
                                <div class="flex-1 border-r border-gray-100 last:border-r-0
                                    {{ in_array($hour * 60 + $minute, range($workStart->hour * 60 + $workStart->minute, $workEnd->hour * 60 + $workEnd->minute - 1)) 
                                        ? 'bg-green-50/50' 
                                        : 'bg-gray-100/50' }}">
                                </div>
                            @endfor
                        </div>
                    </div>
                @endfor
            </div>

            {{-- Блоки задач и перерывов --}}
            <div class="relative" style="height: 120px;">
                
                {{-- Линии сетки --}}
                <div class="absolute inset-0 flex">
                    @for ($hour = $workStart->hour; $hour <= $workEnd->hour; $hour++)
                        <div class="flex-1 border-r border-gray-100 last:border-r-0">
                            @for ($minute = 0; $minute < 60; $minute += 5)
                                <div class="flex-1 border-r border-gray-50 last:border-r-0 h-full"></div>
                            @endfor
                        </div>
                    @endfor
                </div>

                {{-- Блоки --}}
                @foreach ($timeline['blocks'] as $block)
                    @php
                        $blockStart = \Carbon\Carbon::parse($block['start']);
                        $offsetMinutes = $workStart->diffInMinutes($blockStart);
                        $leftPercent = ($offsetMinutes / $totalMinutes) * 100;
                        $widthPercent = ($block['width_minutes'] / $totalMinutes) * 100;
                    @endphp
                    
                    @if ($block['type'] === 'task')
                        @include('admin.calendar.schedule.components.timeline.horizontal-task-block', [
                            'block' => $block,
                            'leftPercent' => $leftPercent,
                            'widthPercent' => $widthPercent,
                            'date' => $date,
                        ])
                    @else
                        @include('admin.calendar.schedule.components.timeline.horizontal-break-block', [
                            'block' => $block,
                            'leftPercent' => $leftPercent,
                            'widthPercent' => $widthPercent,
                        ])
                    @endif
                @endforeach

            </div>

            {{-- Текущее время --}}
            @if ($date->isToday())
                @php
                    $now = now();
                    $nowMinutes = $now->hour * 60 + $now->minute;
                    $workStartMinutes = $workStart->hour * 60 + $workStart->minute;
                    $workEndMinutes = $workEnd->hour * 60 + $workEnd->minute;
                @endphp
                @if ($nowMinutes >= $workStartMinutes && $nowMinutes <= $workEndMinutes)
                    @php
                        $currentOffset = $nowMinutes - $workStartMinutes;
                        $currentLeft = ($currentOffset / $totalMinutes) * 100;
                    @endphp
                    <div class="relative h-6">
                        <div class="absolute top-0 w-0.5 h-full bg-red-500 z-20" style="left: {{ $currentLeft }}%">
                            <div class="absolute -top-5 left-1/2 -translate-x-1/2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full whitespace-nowrap">
                                {{ $now->format('H:i') }}
                            </div>
                        </div>
                    </div>
                @endif
            @endif

        </div>
    </div>
</div>