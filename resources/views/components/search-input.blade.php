@props([
    'name' => 'search',
    'placeholder' => 'Поиск...',
    'route',
])

<form method="GET" action="{{ $route }}" class="relative w-full sm:w-80">
    {{-- сохраняем остальные фильтры --}}
    @foreach (request()->except($name) as $key => $value)
        @if (is_array($value))
            @foreach ($value as $v)
                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <input type="text" name="{{ $name }}" value="{{ request($name) }}" placeholder="{{ $placeholder }}"
        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300
               focus:ring-2 focus:ring-[#A60644]
               focus:outline-none text-sm bg-white"
        autocomplete="off">

    {{-- Иконка --}}
    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>
</form>
