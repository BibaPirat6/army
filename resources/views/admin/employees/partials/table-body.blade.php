{{-- Тело таблицы --}}
<tbody id="employeesTableBody" class="divide-y divide-[#BFBFBF]">
    @forelse ($employees as $employee)
        <tr class="hover:bg-[#dfdad9] align-top transition-colors">
            <!-- ID и статус -->
            <td class="px-4 py-3 align-top">
                <div class="font-bold">ID: {{ $employee->id }}</div>
                <div class="text-[#565A5B] text-xs mt-1">
                    Статус: {{ $employee->workStatus?->description ?? '' }}
                </div>
            </td>

            <!-- Пользователь -->
            <td class="px-4 py-3 align-top">
                <div class="font-medium">{{ $employee->user->login }}</div>
                <div class="text-[#565A5B] text-xs">ID: {{ $employee->user->id }}</div>
                <div class="text-[#565A5B] text-xs">
                    Роль: {{ $employee->user->role?->description ?? '' }}
                </div>
            </td>

            <!-- Персона -->
            <td class="px-4 py-3 align-top">
                {{ $employee->getFullNameAttribute() }}
            </td>

            <!-- Должности -->
            <td class="px-4 py-3 align-top">
                @if ($employee->employeePositions->count() > 0)
                    <ul class="text-[#565A5B] text-xs space-y-1">
                        @foreach ($employee->employeePositions as $ep)
                            <li>ID: {{ $ep->id }} | {{ $ep->position->name }} (ставка:
                                {{ $ep->getRateValueAttribute() }})
                                <br>
                                Тип: {{ $ep->position->positionType->name }}
                                @if ($ep->is_independent)
                                    (Самостоятельная должность)
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    {{-- меню кнопок --}}
                    <div class="relative inline-block text-left">
                        <!-- Кнопка-триггер -->
                        <button
                            class="dropdown-btn p-1.5 text-gray-500 hover:text-[#A60644] hover:bg-gray-100 rounded-full transition-colors"
                            title="Действия">
                            <svg class="w-6 h-6 text-[#A60644] hover:text-[#c20c54] active:scale-95 transition-all duration-150 cursor-pointer"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 12l2 2 4-4m3 7h.01" />
                            </svg>
                        </button>

                        <!-- Меню -->
                        <div
                            class="dropdown-menu absolute left-full top-1/2 -translate-y-1/2 ml-2 bg-white border border-gray-200 rounded-md shadow-lg z-50 
                                    p-1 flex flex-row gap-0.5 min-w-[40px] hidden opacity-0 scale-95 transition-all duration-200">

                            <a href="{{ route('employee-positions.create', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                class="p-2 text-gray-600 hover:text-[#A60644] hover:bg-gray-100 rounded transition-colors"
                                title="Назначить">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </a>

                            <a href="{{ route('employee-positions.edit', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                class="p-2 text-gray-600 hover:text-[#A60644] hover:bg-gray-100 rounded transition-colors"
                                title="Изменить">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            <div class="border-l border-gray-200 mx-1"></div>
                        </div>
                    </div>
                @else
                    <span class="text-[#A60644] italic text-xs"><a
                            href="{{ route('employee-positions.create', ['id' => $employee->id, 'back_url' => url()->full()]) }}">Не
                            назначены</a></span>
                @endif
            </td>

            <!-- Основные действия -->
            <td class="px-4 py-3 align-top text-right">
                <div class="flex flex-wrap justify-end gap-1">
                    <a href="{{ route('employees.edit', [
            'id' => $employee->id,
            'back_url' => url()->full(),
        ]) }}" class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Редактировать</a>
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
                <svg class="w-12 h-12 text-[#BFBFBF] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
                <p class="text-[#565A5B] font-medium">Нет сотрудников</p>
                <p class="text-[#7F7F7F] text-sm">Создайте первого сотрудника для начала работы</p>
            </td>
        </tr>
    @endforelse
</tbody>