{{-- Тело таблицы --}}
<tbody id="employeesTableBody" class="divide-y divide-[#BFBFBF]">
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
                        <a href="{{ route('users.show', ['id' => $employee->user->id, 'back_url' => url()->full()]) }}"
                            class="text-[10px] px-2 py-1 bg-[#c0b6b9] text-white rounded hover:bg-[#A60644]/80 transition-colors">Подробнее</a>
                        <a href="{{ route('users.edit', ['id' => $employee->user->id, 'employee_id' => $employee->id, 'back_url' => url()->full()]) }}"
                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Изменить</a>
                        <form action="{{ route('users.delete', $employee->user->id) }}" method="POST"
                            class="inline-block"
                            onsubmit="return confirm('Удалить пользователя \'{{ $employee->user->login }}\'?')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="backUrl" value="{{ url()->full() }}">
                            <button type="submit"
                                class="text-[10px] px-2 py-1 bg-[#060606] text-white rounded hover:bg-[#060606]/80 transition-colors">Удалить</button>
                        </form>
                    </div>
                @else
                    <span class="text-[#7F7F7F] italic text-xs">Не привязан</span>
                    <div class="mt-2">
                        <a href="{{ route('users.create', ['employee_id' => $employee->id, 'back_url' => url()->full()]) }}"
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
                        <a href="{{ route('persons.show', ['id' => $employee->person->id, 'back_url' => url()->full()]) }}"
                            class="text-[10px] px-2 py-1 bg-[#c0b6b9] text-white rounded hover:bg-[#A60644]/80 transition-colors">Подробнее</a>
                        <a href="{{ route('persons.edit', ['id' => $employee->person->id, 'employee_id' => $employee->id, 'back_url' => url()->full()]) }}"
                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Изменить</a>
                        <form action="{{ route('persons.delete', $employee->person->id) }}" method="POST"
                            class="inline-block"
                            onsubmit="return confirm('Удалить персональные данные \'{{ $employee->person->last_name ?? '' }}\' \'{{ $employee->person->first_name ?? '' }}\'?')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="backUrl" value="{{ url()->full() }}">
                            <button type="submit"
                                class="text-[10px] px-2 py-1 bg-[#060606] text-white rounded hover:bg-[#060606]/80 transition-colors">Удалить</button>
                        </form>
                    </div>
                @else
                    <span class="text-[#7F7F7F] italic text-xs">Не указана</span>
                    <div class="mt-2">
                        <a href="{{ route('persons.create', ['employee_id' => $employee->id, 'back_url' => url()->full()]) }}"
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
                                @if ($ep->is_independent)
                                    (Самостоятельная должность)
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="flex flex-wrap gap-1 mt-2">
                        <a href="{{ route('employee-positions.create', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Назначить</a>
                        <a href="{{ route('employee-positions.show', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                            class="text-[10px] px-2 py-1 bg-[#c0b6b9] text-white rounded hover:bg-[#c0b6b9]/80 transition-colors">Подробнее</a>
                        <a href="{{ route('employee-positions.edit', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                            class="text-[10px] px-2 py-1 bg-[#5a4a50] text-white rounded hover:bg-[#A60644]/80 transition-colors">Изменить</a>
                        <form
                            action="{{ route('employee-positions.destroy', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
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
                        <a href="{{ route('employee-positions.create', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                            class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Назначить</a>
                    </div>
                @endif
            </td>

            <!-- Основные действия -->
            <td class="px-4 py-3 align-top text-right">
                <div class="flex flex-wrap justify-end gap-1">
                    <a href="{{ route('employees.edit', $employee->id) }}"
                        class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Редактировать</a>
                    <form action="{{ route('employees.delete', $employee->id) }}" method="POST" class="inline-block"
                        onsubmit="return confirm('Удалить сотрудника?')">
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
