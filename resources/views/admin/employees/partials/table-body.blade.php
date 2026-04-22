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
                <a href="{{ route("employees.show", [
            "id" => $employee->id,
            "back_url" => url()->full()
        ]) }}"> {{ $employee->getFullNameAttribute() }}</a>
            </td>

            <!-- Должности -->
            <td class="px-4 py-3 align-top">
                @if ($employee->employeePositions->count() > 0)
                        <div class="space-y-2">
                            @foreach ($employee->employeePositions as $ep)
                                    <div class="group relative">
                                        <!-- Ссылка на редактирование назначения -->
                                        <a href="{{ route('commissariat-positions.assign.edit', [
                                    'id' => $ep->commissariat_position_id,
                                    'employeePositionId' => $ep->id,
                                    'back_url' => url()->full()
                                ]) }}" class="block hover:bg-gray-50 rounded-lg transition-colors duration-150 p-2 -m-2">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <!-- Название должности -->
                                                    <div class="font-medium text-[#060606] text-sm">
                                                        {{ $ep->commissariatPosition->position->name }}
                                                    </div>

                                                    <!-- Ставка -->
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                            @if($ep->employeePositionStatus->occupies_rate)
                                                                bg-green-100 text-green-800
                                                            @else
                                                                bg-gray-100 text-gray-600
                                                            @endif">
                                                            ставка: {{ number_format($ep->rate, 2) }}
                                                        </span>

                                                        <!-- Статус -->
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs"
                                                            style="background-color: {{ $ep->employeePositionStatus->color }}20; color: {{ $ep->employeePositionStatus->color }}">
                                                            {{ $ep->employeePositionStatus->name }}
                                                        </span>
                                                    </div>

                                                    <!-- Дополнительная информация -->
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        {{ $ep->commissariatPosition->commissariat->name }}
                                                        @if($ep->commissariatPosition->department)
                                                            / {{ $ep->commissariatPosition->department->name }}
                                                        @endif
                                                        @if($ep->commissariatPosition->division)
                                                            / {{ $ep->commissariatPosition->division->name }}
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Иконка перехода -->
                                                <svg class="w-4 h-4 text-gray-400 group-hover:text-[#A60644] transition-colors ml-2"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                        </a>
                                    </div>
                            @endforeach
                        </div>

                        <!-- Кнопка добавления новой должности -->
                        <div class="mt-3 pt-2 border-t border-gray-200">
                            <a href="{{ route('employee-positions.create', [
                        'id' => $employee->id,
                        'back_url' => url()->full()
                    ]) }}"
                                class="inline-flex items-center text-xs text-[#A60644] hover:text-[#A60644]/80 font-medium transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Назначить на должность
                            </a>
                        </div>
                @else
                        <div class="text-center py-4">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <p class="text-[#565A5B] text-sm mb-2">Нет назначений</p>
                            <a href="{{ route('employee-positions.create', [
                        'id' => $employee->id,
                        'back_url' => url()->full()
                    ]) }}"
                                class="inline-flex items-center px-3 py-1.5 bg-[#A60644] text-white text-xs font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Назначить на должность
                            </a>
                        </div>
                @endif
            </td>

            <!-- Основные действия -->
            <td class="px-4 py-3 align-top text-right">
                <div class="flex flex-wrap justify-end gap-1">
                    <a href="{{ route('employees.edit', [
            'id' => $employee->id,
            'back_url' => url()->full(),
        ]) }}" class="text-[10px] px-2 py-1 bg-[#A60644] text-white rounded hover:bg-[#A60644]/80 transition-colors">Редактировать</a>

                    <a href="{{ route('employees.show', [
            'id' => $employee->id,
            'back_url' => url()->full(),
        ]) }}" class="text-[10px] px-2 py-1 bg-[#746ccc] text-white rounded hover:bg-[#746ccc]/80 transition-colors">Подробнее</a>


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