{{-- В компоненте filter-dropdown-wide.blade.php --}}
@props(['title', 'route'])

<th class="relative inline-block px-4 py-2 text-left font-medium whitespace-nowrap">

    <!-- Кнопка-триггер -->
    <button type="button" class="dropdown-btn flex items-center gap-1 select-none focus:outline-none">
        {{ $title }}
        <svg class="w-3 h-3 dropdown-arrow transition-transform" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Содержимое dropdown -->
    <div class="dropdown-menu absolute left-1/2 -translate-x-1/2 top-full mt-2 w-[950px] bg-white border rounded-xl shadow-xl p-4 hidden opacity-0 scale-95 transition-all duration-200 z-[9999]">

        <form method="GET" action="{{ $route }}" class="filter-form">
            <div class="grid grid-cols-2 gap-6 text-sm">
                {{ $slot }}
            </div>
            
            <div class="col-span-2 flex justify-end gap-2 mt-4 pt-2 border-t">
                <button type="submit"
                    class="px-6 py-2 bg-[#A60644] text-white rounded-lg hover:bg-[#A60644]/90 transition cursor-pointer">
                    Применить фильтр
                </button>
                <a href="{{ $route }}"
                    class="px-6 py-2 border border-gray-500 rounded-lg hover:bg-gray-100 transition inline-block text-center">
                    Сбросить
                </a>
            </div>
        </form>

    </div>

</th>