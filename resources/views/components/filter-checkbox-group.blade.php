@props([
    'label',
    'name',
    'items',        // коллекция или массив
    'valueKey',     // поле value
    'labelKey',     // поле label
    'selected' => []
])

<div>
    <span class="block font-medium text-black mb-1">
        {{ $label }}
    </span>

    @foreach($items as $item)
        @php
            $value = is_array($item) ? $item[$valueKey] : $item->$valueKey;
            $text = is_array($item) ? $item[$labelKey] : $item->$labelKey;
        @endphp

        <label class="flex items-center gap-1 mb-1 cursor-pointer">
            <input type="checkbox"
                   name="{{ $name }}[]"
                   value="{{ $value }}"
                   {{ in_array($value, (array)$selected) ? 'checked' : '' }}>
            {{ $text }}
        </label>
    @endforeach
</div>
