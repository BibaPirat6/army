@props(['buttonText', 'items'])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" 
            class="flex items-center gap-1 px-4 py-2 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg font-bold hover:text-[#A60644] transition-colors">
        <svg class="w-5 h-5 mr-1 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        {{ $buttonText }}
        <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <ul x-show="open" 
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute top-full right-0 mt-2 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-lg list-none p-2 min-w-[220px] z-50">
        @foreach($items as $item)
            <li>
                <a href="{{ $item['route'] }}" class="block px-4 py-2 rounded hover:bg-[#A60644]/10 hover:text-[#A60644] transition-colors">
                    {{ $item['text'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>