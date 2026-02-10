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
        <!-- Заголовок и кнопка создания -->
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







        <div class="overflow-x-auto rounded-lg border border-[#BFBFBF]">
            <table class="min-w-full divide-y divide-[#BFBFBF] bg-[#e7e1e1] text-sm">
                {{-- Заголовок всегда --}}
                <thead class="bg-[#d5cfcf]">
                    <tr>
                        <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap relative group">
                            <div class="flex items-center gap-1 cursor-pointer">
                                ID / Статус
                                <svg class="w-3 h-3 transition-transform group-hover:rotate-180" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div
                                class="absolute top-full left-0 mt-1 bg-white border border-gray-300 rounded shadow-lg z-50 p-2 text-xs text-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <!-- Объединенная форма фильтрации -->
                                <form method="GET" action="{{ route('employees.index') }}"
                                    class="mb-2 p-2 bg-white border border-gray-300 rounded shadow-sm">
                                    <span class="text-black block font-medium mb-1">ID</span>

                                    {{-- ASC --}}
                                    <label class="flex items-center gap-1 mb-1 cursor-pointer">
                                        <input type="checkbox" name="sort_id[]" value="asc"
                                            {{ in_array('asc', (array) request('sort_id', [])) ? 'checked' : '' }}>
                                        Первые (ASC)
                                    </label>

                                    {{-- DESC --}}
                                    <label class="flex items-center gap-1 mb-2 cursor-pointer">
                                        <input type="checkbox" name="sort_id[]" value="desc"
                                            {{ in_array('desc', (array) request('sort_id', [])) ? 'checked' : '' }}>
                                        Последние (DESC)
                                    </label>

                                    <span class="text-black block font-medium mb-1 mt-2">Статус</span>
                                    @foreach ($statuses as $status)
                                        <label class="flex items-center gap-1 mb-1 cursor-pointer">
                                            <input type="checkbox" name="sort_status[]" value="{{ $status->name }}"
                                                {{ in_array($status->name, (array) request('sort_status', [])) ? 'checked' : '' }}>
                                            {{ $status?->description }}
                                        </label>
                                    @endforeach

                                    {{-- Кнопка поиска --}}
                                    <button type="submit"
                                        class="mt-2 px-4 py-2 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">
                                        Поиск
                                    </button>
                                </form>


                            </div>
                        </th>

                        <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Пользователь</th>
                        <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Персона (ФИО, контакты)
                        </th>


                        <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap relative group">
                            <div class="flex items-center gap-1 cursor-pointer">
                                Должности
                                <svg class="w-3 h-3 transition-transform group-hover:rotate-180" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>

                            {{-- фильтры --}}
                            <div
                                class="absolute top-full -translate-x-1/2 mt-1 bg-white border border-gray-300 rounded shadow-lg z-50 p-3 text-xs text-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 w-[1000px] max-h-[none] overflow-visible flex gap-4">


                                <form method="GET" action="{{ route('employees.index') }}" class="flex-1 flex gap-4">

                                    <!-- Левый блок: комиссариаты / отделы / отделения -->
                                    <div class="flex-1 space-y-3">
                                        <!-- Поле поиска -->
                                        <input type="text" id="positionSearch" placeholder="Поиск по всем фильтрам..."
                                            class="w-full mb-2 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-[#A60644]">

                                        <!-- Комиссариаты -->
                                        <div id="positionList">
                                            <span class="text-black block font-medium mb-1">Комиссариаты</span>
                                            @foreach ($commissariats as $com)
                                                <label class="flex items-center gap-1 mb-1 cursor-pointer position-item">
                                                    <input type="checkbox" name="sort_commissariat[]"
                                                        value="{{ $com->id }}"
                                                        {{ in_array($com->id, (array) request('sort_commissariat', [])) ? 'checked' : '' }}>
                                                    ID: {{ $com->id }} | {{ $com->name }}
                                                </label>
                                            @endforeach
                                        </div>

                                        <!-- Отделы -->
                                        <div id="positionList">
                                            <span class="text-black block font-medium mb-1">Отделы</span>
                                            @foreach ($departments as $dep)
                                                <label class="flex items-center gap-1 mb-1 cursor-pointer position-item">
                                                    <input type="checkbox" name="sort_department[]"
                                                        value="{{ $dep->id }}"
                                                        {{ in_array($dep->id, (array) request('sort_department', [])) ? 'checked' : '' }}>
                                                    ID: {{ $dep->id }} | {{ $dep->name }} <
                                                        {{ $dep->commissariat->name }} (ID: {{ $dep->commissariat->id }})
                                                        </label>
                                            @endforeach
                                        </div>

                                        <!-- Отделения -->
                                        <div id="positionList">
                                            <span class="text-black block font-medium mb-1">Отделения</span>
                                            @foreach ($divisions as $div)
                                                <label class="flex items-center gap-1 mb-1 cursor-pointer position-item">
                                                    <input type="checkbox" name="sort_division[]"
                                                        value="{{ $div->id }}"
                                                        {{ in_array($div->id, (array) request('sort_division', [])) ? 'checked' : '' }}>
                                                    ID: {{ $div->id }} | {{ $div->name }}
                                                    @if (isset($div?->department?->id))
                                                        < {{ $div?->department?->name ?? '-' }} (ID:
                                                            {{ $div?->department?->id ?? '-' }}) <
                                                            {{ $div->commissariat->name }} (ID:
                                                        {{ $div->commissariat->id }}) @else <
                                                            {{ $div->commissariat->name }} (ID:
                                                            {{ $div->commissariat->id }}) <span class="text-green-500">
                                                            (Сам. отделение)
                                                            </span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Правый блок: должности / типы должностей -->
                                    <div class="flex-1 space-y-3">
                                        <!-- Должности -->
                                        <div id="positionList">
                                            <span class="text-black block font-medium mb-1">Должности</span>
                                            @foreach ($positions as $pos)
                                                <label class="flex items-center gap-1 mb-1 cursor-pointer position-item">
                                                    <input type="checkbox" name="sort_position[]"
                                                        value="{{ $pos->id }}"
                                                        {{ in_array($pos->id, (array) request('sort_position', [])) ? 'checked' : '' }}>
                                                    ID: {{ $pos->id }} | <b>{{ $pos->name }}</b> | Тип:
                                                    {{ $pos->positionType->name }}
                                                </label>
                                            @endforeach
                                        </div>

                                        <!-- Типы должностей -->
                                        <div id="positionList">
                                            <span class="text-black block font-medium mb-1 mt-2">Типы должностей</span>
                                            @foreach ($positionTypes as $type)
                                                <label class="flex items-center gap-1 mb-1 cursor-pointer position-item">
                                                    <input type="checkbox" name="sort_type[]" value="{{ $type->id }}"
                                                        {{ in_array($type->id, (array) request('sort_type', [])) ? 'checked' : '' }}>
                                                    {{ $type->name }}
                                                </label>
                                            @endforeach
                                        </div>


                                        {{-- ставка --}}
                                        <div id="positionList">
                                            <span class="text-black block font-medium mb-1 mt-2">Ставка</span>
                                            @foreach ($rates as $rate)
                                                <label class="flex items-center gap-1 mb-1 cursor-pointer position-item">
                                                    <input type="checkbox" name="sort_rate[]"
                                                        value="{{ $rate }}"
                                                        {{ in_array($rate, (array) request('sort_rate', [])) ? 'checked' : '' }}>
                                                    {{ $rate }}
                                                </label>
                                            @endforeach
                                        </div>

                                        {{-- самостоятельная должность --}}
                                        <div id="positionList">
                                            <span class="text-black block font-medium mb-1 mt-2">самостоятельная</span>
                                            <select name="is_independent" id="is_independent">
                                                <option value="0" selected>Нет</option>
                                                <option value="1">Да</option>
                                            </select>
                                        </div>

                                        <!-- Кнопка поиска -->
                                        <button type="submit"
                                            class="mt-2 px-4 py-2 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">
                                            Поиск
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </th>


                        <th class="px-4 py-2 text-right text-[#060606] font-medium whitespace-nowrap">Действия</th>
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
                                            <li>ID: {{ $ep->position->id }} | {{ $ep->position->name }} (ставка:
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
