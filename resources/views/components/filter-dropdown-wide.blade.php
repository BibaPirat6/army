@props(['title', 'route'])

<th class="inline-block relative px-4 py-2 text-left font-medium group whitespace-nowrap">

    <div class="flex items-center gap-1 cursor-pointer select-none">
        {{ $title }}

        <svg class="w-3 h-3 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </div>

    <div
        class="absolute left-1/2 -translate-x-1/2 top-full mt-2
                w-[950px] bg-white border rounded-xl shadow-xl
                p-4 opacity-0 invisible
                group-hover:opacity-100 group-hover:visible
                transition-all duration-200 z-50">

        <div class="grid grid-cols-2 gap-6 text-sm">
            {{ $slot }}
        </div>

    </div>

</th>
