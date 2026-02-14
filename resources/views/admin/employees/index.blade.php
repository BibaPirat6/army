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


    <div class="max-w-6xl mx-auto p-4">
        <!-- кнопка создания -->
        <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-[#060606]">Сотрудники</h1>
                <p class="text-[#565A5B] text-sm">Список всех сотрудников системы</p>
            </div>
            <a href="{{ route('employees.create') }}"
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
                                            valueKey="id" :selected="request('sort_department', [])" :labelCallback="fn($item) => 'ID: ' .
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

                                        <x-filter-checkbox-block title="Должности" name="sort_position" :items="$positions"
                                            valueKey="id" :selected="request('sort_position', [])" :labelCallback="fn($item) => 'ID: ' .
                                                $item->id .
                                                ' | <b>' .
                                                $item->name .
                                                '</b> | ' .
                                                $item->positionType->name" />

                                        <x-filter-checkbox-block title="Типы должностей" name="sort_type" :items="$positionTypes"
                                            valueKey="id" :selected="request('sort_type', [])" :labelCallback="fn($item) => $item->name" />

                                        <x-filter-checkbox-block title="Ставка" name="sort_rate" :items="collect($rates)->map(fn($r) => (object) ['value' => $r])"
                                            valueKey="value" :selected="request('sort_rate', [])" :labelCallback="fn($item) => $item->value" />

                                        {{-- Самостоятельная --}}
                                        <div>
                                            <p class="font-semibold text-gray-700 mb-1">
                                                Самостоятельная
                                            </p>

                                            <select name="is_independent"
                                                class="w-full px-2 py-1 border rounded-lg
                               focus:ring-2 focus:ring-[#A60644]">
                                                <option value="">Все</option>
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

                {{-- Тело таблицы --}}
                <tbody class="divide-y divide-[#BFBFBF]">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-[#dfdad9] align-top transition-colors">
                            <!-- ID и статус -->
                            <td class="px-4 py-3 align-top">
                                <div class="font-bold">ID: {{ $employee->id }}</div>
                                <div class="text-[#565A5B] text-xs mt-1">
                                    Статус: {{ $employee->workStatus?->description ?? '—' }}
                                </div>
                            </td>

                            <!-- Пользователь -->
                            <td class="px-4 py-3 align-top">
                                @if ($employee->user)
                                    <div class="font-medium">{{ $employee->user->login }}</div>
                                    <div class="text-[#565A5B] text-xs">ID: {{ $employee->user->id }}</div>
                                    <div class="text-[#565A5B] text-xs">
                                        Роль: {{ $employee->user->role?->description ?? '—' }}
                                    </div>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <a href="{{ route('users.show', ['id' => $employee->user->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#c0b6b9] text-white rounded hover:bg-[#A60644]/80 transition-colors">Подробнее</a>
                                        <a href="{{ route('users.edit', ['id' => $employee->user->id, 'employee_id' => $employee->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Изменить</a>
                                        <form action="{{ route('users.delete', $employee->user->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Удалить пользователя {{ $employee->user->login }}?')">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="backUrl" value="{{ route('employees.index') }}">
                                            <button type="submit"
                                                class="text-[10px] px-2 py-1 bg-[#060606] text-white rounded hover:bg-[#060606]/80 transition-colors">Удалить</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[#7F7F7F] italic text-xs">Не привязан</span>
                                    <div class="mt-2">
                                        <a href="{{ route('users.create', ['employee_id' => $employee->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Создать</a>
                                    </div>
                                @endif
                            </td>

                            <!-- Персона -->
                            <td class="px-4 py-3 align-top">
                                @if ($employee->person)
                                    <div class="font-medium">
                                        {{ trim(
                                            implode(' ', [
                                                $employee->person->last_name ?? '',
                                                $employee->person->first_name ?? '',
                                                $employee->person->patronymic ?? '',
                                            ]),
                                        ) ?:
                                            '—' }}
                                    </div>
                                    <div class="text-[#565A5B] text-xs">ID: {{ $employee->person->id }}</div>
                                    @if ($employee->person->phones && count($employee->person->phones))
                                        <div class="text-[#565A5B] text-xs mt-1">
                                            Телефоны:
                                            {{ implode(', ', array_map(fn($p) => '+' . $p, $employee->person->phones)) }}
                                        </div>
                                    @endif
                                    @if ($employee->person->emails && count($employee->person->emails))
                                        <div class="text-[#565A5B] text-xs mt-1">
                                            Email: {{ implode(', ', $employee->person->emails) }}
                                        </div>
                                    @endif
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <a href="{{ route('persons.show', ['id' => $employee->person->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#c0b6b9] text-white rounded hover:bg-[#A60644]/80 transition-colors">Подробнее</a>
                                        <a href="{{ route('persons.edit', ['id' => $employee->person->id, 'employee_id' => $employee->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Изменить</a>
                                        <form action="{{ route('persons.delete', $employee->person->id) }}"
                                            method="POST" class="inline-block"
                                            onsubmit="return confirm('Удалить персональные данные {{ $employee->person->last_name ?? '' }} {{ $employee->person->first_name ?? '' }}?')">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="backUrl" value="{{ route('employees.index') }}">
                                            <button type="submit"
                                                class="text-[10px] px-2 py-1 bg-[#060606] text-white rounded hover:bg-[#060606]/80 transition-colors">Удалить</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[#7F7F7F] italic text-xs">Не указана</span>
                                    <div class="mt-2">
                                        <a href="{{ route('persons.create', ['employee_id' => $employee->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Создать</a>
                                    </div>
                                @endif
                            </td>

                            <!-- Должности -->
                            <td class="px-4 py-3 align-top">
                                @if ($employee->positions->count() > 0)
                                    <ul class="text-[#565A5B] text-xs space-y-1">
                                        @foreach ($employee->positions as $ep)
                                            <li>ID: {{ $ep->id }} | {{ $ep->position->name }} (ставка:
                                                {{ number_format($ep->rate, 2, ',', '') }})
                                                <br>
                                                Тип: {{ $ep->position->positionType->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <a href="{{ route('employee-positions.create', ['id' => $employee->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Назначить</a>
                                        <a href="{{ route('employee-positions.show', ['id' => $employee->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#c0b6b9] text-white rounded hover:bg-[#c0b6b9]/80 transition-colors">Подробнее</a>
                                        <a href="{{ route('employee-positions.edit', ['id' => $employee->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#5a4a50] text-white rounded hover:bg-[#A60644]/80 transition-colors">Изменить</a>
                                        <form
                                            action="{{ route('employee-positions.destroy', ['id' => $employee->id, 'back_url' => route('employees.index')]) }}"
                                            method="POST" class="inline-block"
                                            onsubmit="return confirm('Удалить все назначения для сотрудника?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-[10px] px-2 py-1 bg-[#060606] text-white rounded hover:bg-[#060606]/80 transition-colors">Удалить</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[#7F7F7F] italic text-xs">Не назначены</span>
                                    <div class="mt-2">
                                        <a href="{{ route('employee-positions.create', ['id' => $employee->id, 'back_url' => route('employees.index')]) }}"
                                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Назначить</a>
                                    </div>
                                @endif
                            </td>

                            <!-- Основные действия -->
                            <td class="px-4 py-3 align-top text-right">
                                <div class="flex flex-wrap justify-end gap-1">
                                    <a href="{{ route('employees.edit', $employee->id) }}"
                                        class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Редактировать</a>
                                    <form action="{{ route('employees.delete', $employee->id) }}" method="POST"
                                        class="inline-block" onsubmit="return confirm('Удалить сотрудника?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-[10px] px-2 py-1 bg-[#060606] text-white rounded hover:bg-[#060606]/80 transition-colors">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <svg class="w-12 h-12 text-[#BFBFBF] mx-auto mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <p class="text-[#565A5B] font-medium">Нет сотрудников</p>
                                <p class="text-[#7F7F7F] text-sm">Создайте первого сотрудника для начала работы</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('includes.pagination', ['paginator' => $employees])
    </div>
@endsection


{{-- поиск по всем полям --}}
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
