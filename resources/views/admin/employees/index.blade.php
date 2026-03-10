@extends('layouts.main')

@section('header-title')
    Сотрудники
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif


    <div class="w-full mx-auto p-4">
        <!-- кнопка создания -->
        <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-[#060606]">Сотрудники</h1>
                <p class="text-[#565A5B] text-sm">Список всех сотрудников системы</p>
            </div>
            {{-- поиск --}}
            <div>
                <x-search-input name="search" placeholder="Поиск по сотрудникам..." :route="route('employees.index')" />
            </div>

            <a href="{{ route('employees.create', [
                'back_url' => url()->full(),
            ]) }}"
                class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors shadow hover:shadow-md active:scale-[0.98]">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Создать сотрудника
            </a>
        </div>

        {{-- таблица --}}
        <div class="rounded-lg border border-[#BFBFBF]">
            <table class="min-w-full divide-y divide-[#BFBFBF] bg-[#e7e1e1] text-sm">
                {{-- шапка таблицы --}}
                <thead class="bg-[#d5cfcf]">
                    <tr>
                        <form method="GET" action="{{ route('employees.index') }}">
                            {{-- сортировка по id/статусу --}}
                            <x-filter-dropdown title="ID / Статус" :route="route('employees.index')">

                                {{-- Сортировка ID --}}
                                <x-filter-checkbox-group label="ID" name="sort_id" :items="[
                                    ['value' => 'asc', 'label' => 'Первые (ASC)'],
                                    ['value' => 'desc', 'label' => 'Последние (DESC)'],
                                ]" valueKey="value"
                                    labelKey="label" :selected="request('sort_id', [])" />

                                {{-- Статусы --}}
                                <x-filter-checkbox-group label="Статус" name="sort_status" :items="$statuses" valueKey="name"
                                    labelKey="description" :selected="request('sort_status', [])" />

                            </x-filter-dropdown>

                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Пользователь</th>
                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Персона (ФИО,
                                контакты)
                            </th>

                            {{-- сортировка по должностям --}}
                            <x-filter-dropdown-wide title="Должности" :route="route('employees.index')">

                                {{-- ЛЕВАЯ КОЛОНКА --}}
                                <div class="space-y-4">

                                    {{-- Поиск --}}
                                    <input type="text" id="positionSearch" placeholder="Поиск по фильтрам..."
                                        class="w-full px-3 py-1.5 border rounded-lg
                      focus:ring-2 focus:ring-[#A60644]
                      focus:outline-none">

                                    <div id="positionList" class="space-y-4 max-h-[400px] overflow-y-auto pr-2">

                                        <x-filter-checkbox-block title="Комиссариаты" name="sort_commissariat"
                                            :items="$commissariats" valueKey="id" :selected="request('sort_commissariat', [])" :labelCallback="fn($item) => 'ID: ' . $item->id . ' | ' . $item->name" />

                                        <x-filter-checkbox-block title="Отделы" name="sort_department" :items="$departments"
                                            valueKey="id" :sephp artisan serve --host=0.0.0.0
                                            npm run dev
                                            lected="request('sort_department', [])" :labelCallback="fn($item) => 'ID: ' .
                                                $item->id .
                                                ' | ' .
                                                $item->name .
                                                ' < ' .
                                                $item->commissariat->name" />

                                        <x-filter-checkbox-block title="Отделения" name="sort_division" :items="$divisions"
                                            valueKey="id" :selected="request('sort_division', [])" :labelCallback="fn($item) => 'ID: ' .
                                                $item->id .
                                                ' | ' .
                                                $item->name .
                                                ($item->department ? ' < ' . $item->department->name : '') .
                                                ' < ' .
                                                $item->commissariat->name .
                                                ($item->department ? ' < ' . '' : ' (Сам. отделение)')" />

                                    </div>

                                    @if (request()->hasAny([
                                            'sort_id',
                                            'sort_status',
                                            'sort_position',
                                            'sort_type',
                                            'sort_rate',
                                            'sort_commissariat',
                                            'sort_department',
                                            'sort_division',
                                            'is_independent',
                                        ]))
                                        <a href="{{ route('employees.index') }}"
                                            class="block w-full text-center py-2 border border-gray-500
              rounded-lg hover:bg-gray-100 transition">
                                            Сбросить фильтры
                                        </a>
                                    @endif
                                </div>

                                {{-- ПРАВАЯ КОЛОНКА --}}
                                <div class="space-y-4">

                                    <div id="positionList" class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                                        {{-- Самостоятельная --}}
                                        <div>
                                            <p class="font-semibold text-gray-700 mb-1">
                                                Самостоятельная
                                            </p>

                                            <select name="is_independent"
                                                class="w-full px-2 py-1 border rounded-lg
                               focus:ring-2 focus:ring-[#A60644]">
                                                <option value="0"
                                                    {{ request('is_independent') === '0' ? 'selected' : '' }}>
                                                    Нет
                                                </option>
                                                <option value="1"
                                                    {{ request('is_independent') === '1' ? 'selected' : '' }}>
                                                    Да
                                                </option>
                                            </select>
                                        </div>

                                        <x-filter-checkbox-block title="Типы должностей" name="sort_type" :items="$positionTypes"
                                            valueKey="id" :selected="request('sort_type', [])" :labelCallback="fn($item) => $item->name" />

                                        <x-filter-checkbox-block title="Ставка" name="sort_rate" :items="collect($rates)->map(fn($r) => (object) ['value' => $r])"
                                            valueKey="value" :selected="request('sort_rate', [])" :labelCallback="fn($item) => $item->value" />
                                        <x-filter-checkbox-block title="Должности" name="sort_position" :items="$positions"
                                            valueKey="id" :selected="request('sort_position', [])" :labelCallback="fn($item) => 'ID: ' .
                                                $item->id .
                                                ' | <b>' .
                                                $item->name .
                                                '</b> | ' .
                                                $item->positionType->name" />
                                    </div>

                                    <button type="submit"
                                        class="w-full py-2 bg-[#A60644] text-white
                       rounded-lg hover:bg-[#A60644]/90 transition">
                                        Применить фильтр
                                    </button>

                                </div>

                            </x-filter-dropdown-wide>

                            <th class="px-4 py-2 text-right text-[#060606] font-medium whitespace-nowrap">Действия</th>
                        </form>
                    </tr>
                </thead>

                @include('admin.employees.partials.table-body')
            </table>
        </div>

        @include('includes.pagination', ['paginator' => $employees])
    </div>
@endsection


{{-- поиск по всем полям в фильтре --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('positionSearch');
        const positionItems = document.querySelectorAll('#positionList .position-item');

        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();

            positionItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(term) ? 'flex' : 'none';
            });
        });
    });
</script>

{{-- поиск по всей странице --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const input = document.querySelector('input[name="search"]');
        const tableBody = document.getElementById('employeesTableBody');

        let timeout = null;

        input.addEventListener('input', function() {

            clearTimeout(timeout);

            timeout = setTimeout(() => {

                fetch("{{ route('employees.live-search') }}?search=" + this.value)
                    .then(response => response.text())
                    .then(html => {
                        tableBody.innerHTML = html;
                    });

            }, 300);

        });

    });
</script>
