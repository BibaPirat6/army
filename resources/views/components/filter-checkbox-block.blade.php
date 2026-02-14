@props([
    'title',
    'name',
    'items',
    'valueKey',
    'labelCallback', // closure для кастомного вывода
    'selected' => []
])

<div>
    <p class="font-semibold text-gray-700 mb-1">
        {{ $title }}
    </p>

    @foreach($items as $item)
        @php
            $value = $item->$valueKey;
            $checked = in_array($value, (array)$selected);
        @endphp

        <label class="flex items-center gap-2 position-item hover:bg-gray-50 p-1 rounded">
            <input type="checkbox"
                   name="{{ $name }}[]"
                   value="{{ $value }}"
                   {{ $checked ? 'checked' : '' }}
                   class="accent-[#A60644]">

            {!! $labelCallback($item) !!}
        </label>
    @endforeach
</div>
