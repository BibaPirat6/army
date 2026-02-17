@props(['title', 'route'])

<th class="relative inline-block px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">

    <!-- Кнопка-триггер -->
    <button type="button" class="dropdown-btn flex items-center gap-1 focus:outline-none">
        {{ $title }}
        <svg class="w-3 h-3 dropdown-arrow transition-transform" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Содержимое dropdown -->
    <div class="dropdown-menu absolute top-full left-0 mt-1 bg-white border border-gray-300 rounded shadow-lg z-50 p-3 text-xs text-gray-700
                opacity-0 scale-95 hidden transition-all duration-200 w-64">

        <form method="GET" action="{{ $route }}">
            <div class="space-y-3">
                {{ $slot }}

                <button type="submit"
                    class="w-full mt-2 px-3 py-2 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">
                    Применить
                </button>

                @if (request()->hasAny([
                        'sort_id',
                        'sort_status',
                        'sort_position',
                        'sort_type',
                        'sort_rate',
                        'sort_commissariat',
                        'sort_department',
                        'sort_division',
                        'is_independent',
                    ]))
                    <a href="{{ $route }}"
                        class="block w-full text-center py-2 border border-gray-500 rounded-lg hover:bg-gray-100 transition">
                        Сбросить фильтры
                    </a>
                @endif
            </div>
        </form>
    </div>

</th>
