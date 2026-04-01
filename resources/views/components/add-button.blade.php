@props(['route', 'text'])

<a href="{{ $route }}" 
   {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-all duration-200 shadow-md hover:shadow-lg active:scale-[0.98]']) }}>
    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
    </svg>
    {{ $text }}
</a>