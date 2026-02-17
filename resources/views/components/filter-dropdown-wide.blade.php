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
    <div
        class="dropdown-menu absolute left-1/2 -translate-x-1/2 top-full mt-2 w-[950px] bg-white border rounded-xl shadow-xl p-4
               opacity-0 scale-95 hidden transition-all duration-200 z-50">

        <div class="grid grid-cols-2 gap-6 text-sm">
            {{ $slot }}
        </div>

    </div>

</th>
