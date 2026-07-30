<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <span class="w-8 h-8 bg-indigo-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            Таймлайн сотрудника
        </h1>
        
        <div class="flex items-center gap-4 mt-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-sm font-semibold text-indigo-600">
                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $employee->full_name }}</p>
                    <p class="text-xs text-gray-500">{{ $employee->position?->title ?? 'Должность' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2">
        {{-- Навигация по дням --}}
        <a href="?date={{ $date->copy()->subDay()->toDateString() }}"
           class="px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 
                  transition-all hover:border-indigo-300 hover:shadow-sm group">
            <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 transition-colors" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        
        <div class="bg-gray-50 px-5 py-2.5 rounded-xl border">
            <div class="text-lg font-bold text-gray-900">{{ $date->format('d') }}</div>
            <div class="text-xs text-gray-500">{{ $date->translatedFormat('F Y') }}</div>
        </div>
        
        <a href="?date={{ $date->copy()->addDay()->toDateString() }}"
           class="px-4 py-2.5 rounded-xl border bg-white hover:bg-gray-50 
                  transition-all hover:border-indigo-300 hover:shadow-sm group">
            <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 transition-colors" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>

        {{-- Кнопка сегодня --}}
        @if (!$date->isToday())
            <a href="?date={{ now()->toDateString() }}"
               class="ml-2 px-4 py-2.5 text-sm font-medium text-indigo-600 bg-indigo-50 
                      rounded-xl hover:bg-indigo-100 transition-colors">
                Сегодня
            </a>
        @endif
    </div>
</div>