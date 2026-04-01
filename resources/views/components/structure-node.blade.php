@props(['route', 'name', 'type' => 'default'])

<div {{ $attributes->merge(['class' => 'node ' . ($type === 'boss' ? 'boss' : '')]) }}>
    <div class="relative">
        <a href="{{ $route }}" class="absolute inset-0 z-10" aria-label="Подробнее"></a>
        <div class="data">
            <p class="data__fio">{{ $name }}</p>
        </div>
    </div>
</div>