@if ($paginator->hasPages())
    <div class="flex items-center justify-center gap-2 mt-8">
        {{-- Кнопка "Первая страница" --}}
        @if (!$paginator->onFirstPage())
            <a href="{{ $paginator->url(1) }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <span aria-hidden="true">&laquo;&laquo;</span>
            </a>
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <span aria-hidden="true">&laquo;</span>
            </a>
        @else
            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                <span aria-hidden="true">&laquo;&laquo;</span>
            </span>
            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                <span aria-hidden="true">&laquo;</span>
            </span>
        @endif

        {{-- Нумерация страниц --}}
        @php
            $elements = method_exists($paginator, 'getUrlRange')
                ? $paginator->getUrlRange(1, $paginator->lastPage())
                : [];
        @endphp
        @foreach ($elements as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="px-3 py-2 text-sm font-bold text-white bg-blue-600 border border-blue-600 rounded-lg">
                    {{ $page }}
                </span>
            @else
                <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        {{-- Кнопка "Последняя страница" --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <span aria-hidden="true">&raquo;</span>
            </a>
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <span aria-hidden="true">&raquo;&raquo;</span>
            </a>
        @else
            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                <span aria-hidden="true">&raquo;</span>
            </span>
            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                <span aria-hidden="true">&raquo;&raquo;</span>
            </span>
        @endif
    </div>
@endif
