@if ($paginator->hasPages())
    <div class="flex items-center justify-center gap-2 mt-8">
        {{-- Кнопка "Первая страница" --}}
        @if (!$paginator->onFirstPage())
            <a href="{{ $paginator->url(1) }}" class="px-3 py-2 text-sm font-medium text-[#565A5B] bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg hover:bg-[#A60644]/10 hover:text-[#A60644] transition-colors duration-200">
                <span aria-hidden="true">&laquo;&laquo;</span>
            </a>
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-[#565A5B] bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg hover:bg-[#A60644]/10 hover:text-[#A60644] transition-colors duration-200">
                <span aria-hidden="true">&laquo;</span>
            </a>
        @else
            <span class="px-3 py-2 text-sm font-medium text-[#7F7F7F] bg-[#BFBFBF]/30 border border-[#BFBFBF] rounded-lg cursor-not-allowed">
                <span aria-hidden="true">&laquo;&laquo;</span>
            </span>
            <span class="px-3 py-2 text-sm font-medium text-[#7F7F7F] bg-[#BFBFBF]/30 border border-[#BFBFBF] rounded-lg cursor-not-allowed">
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
                <span class="px-3 py-2 text-sm font-bold text-white bg-[#A60644] border border-[#A60644] rounded-lg">
                    {{ $page }}
                </span>
            @else
                <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-[#565A5B] bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg hover:bg-[#A60644]/10 hover:text-[#A60644] transition-colors duration-200">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        {{-- Кнопка "Последняя страница" --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-[#565A5B] bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg hover:bg-[#A60644]/10 hover:text-[#A60644] transition-colors duration-200">
                <span aria-hidden="true">&raquo;</span>
            </a>
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="px-3 py-2 text-sm font-medium text-[#565A5B] bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg hover:bg-[#A60644]/10 hover:text-[#A60644] transition-colors duration-200">
                <span aria-hidden="true">&raquo;&raquo;</span>
            </a>
        @else
            <span class="px-3 py-2 text-sm font-medium text-[#7F7F7F] bg-[#BFBFBF]/30 border border-[#BFBFBF] rounded-lg cursor-not-allowed">
                <span aria-hidden="true">&raquo;</span>
            </span>
            <span class="px-3 py-2 text-sm font-medium text-[#7F7F7F] bg-[#BFBFBF]/30 border border-[#BFBFBF] rounded-lg cursor-not-allowed">
                <span aria-hidden="true">&raquo;&raquo;</span>
            </span>
        @endif
    </div>
@endif