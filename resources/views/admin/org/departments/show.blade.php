@extends('layouts.main')

@section('header-title')
    {{ $department['name'] }}
@endsection

@section('content')
    <div class="max-w-2xl p-6 mx-auto">
        {{-- кнопка назад --}}
        <div class="flex items-center mb-4">
            <a href="{{ $backUrl ?? route('departments.index') }}"
                class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Назад
            </a>
        </div>

        {{-- данные --}}
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                {{-- название - верх --}}
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-[#060606]">{{ $department['name'] }}</h1>
                    @if (isset($department?->chiefEmployeePosition?->employee?->workStatus?->id))
                        <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                            <span class="font-medium text-[#565A5B]">Статус сотрудника</span>
                            <span
                                class="text-[#060606]">{{ $department->chiefEmployeePosition->employee->workStatus->description }}</span>
                        </div>
                    @else
                        @if (isset($department?->chiefEmployee))
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <a
                                    href="{{ route('employees.edit', [
                                        'id' => $department->chiefEmployee,
                                        'back_url' => url()->full(),
                                    ]) }}">
                                    Нет статуса сотрудника.</a>
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <a
                                    href="{{ route('departments.edit', [
                                        'id' => $department->id,
                                        'back_url' => url()->full(),
                                    ]) }}">
                                    Не назначен начальник.</a>
                            </span>
                        @endif
                    @endif
                </div>

                <div class="space-y-4">
                    {{-- департамент --}}
                    <details class="group bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg overflow-hidden">
                        <!-- Заголовок аккордеона -->
                        <summary
                            class="flex items-center justify-between cursor-pointer p-4 hover:bg-[#A60644]/10 transition-colors duration-200">
                            <h1 class="text-xl font-bold text-[#060606]">Детали отдела</h1>
                            <svg class="w-5 h-5 text-[#565A5B] group-open:rotate-180 transition-transform duration-300"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </summary>

                        <div class="p-4 space-y-3 animate-fadeIn">
                            <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                <span class="font-medium text-[#565A5B]">ID</span>
                                <span class="text-[#060606]">{{ $department['id'] }}</span>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                <span class="font-medium text-[#565A5B]">Начальник</span>



                                <span class="text-[#060606]">
                                    @if ($department->chiefEmployeePosition !== null)
                                        @if ($department->chiefEmployeePosition->employee && $department->chiefEmployeePosition->employee->person)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $department->chiefEmployeePosition->employee->person->last_name ?? '' }}
                                                {{ $department->chiefEmployeePosition->employee->person->first_name ?? '' }}
                                                {{ $department->chiefEmployeePosition->employee->person->patronymic ?? '' }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">Без ФИО (ID:
                                                {{ $department->chiefEmployeePosition->employee->id }})</span>
                                        @endif
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Нет
                                        </span>
                                    @endif
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                <span class="font-medium text-[#565A5B]">Комиссариат</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><a href="{{ route("commissariats.show",[
                                        "id"=>$department->commissariat->id,
                                        "back_url"=>url()->full()
                                    ]) }}">{{ $department->commissariat->name }}</a></span>
                            </div>





                            <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                <span class="font-medium text-[#565A5B]">Создан</span>
                                <span
                                    class="text-[#060606]">{{ \Carbon\Carbon::parse($department['created_at'])->format('d.m.Y H:i') }}</span>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                <span class="font-medium text-[#565A5B]">Обновлен</span>
                                <span
                                    class="text-[#060606]">{{ \Carbon\Carbon::parse($department['updated_at'])->format('d.m.Y H:i') }}</span>
                            </div>


                            <div>
                                <a href="{{ route('departments.edit', [
                                    'id' => $department->id,
                                    'back_url' => url()->full(),
                                ]) }}"
                                    class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    Редактировать отдел
                                </a>
                                <form action="{{ route('departments.delete', $department->id) }}" method="POST"
                                    class="inline-block mt-0.5"
                                    onsubmit="return confirm('Вы уверены, что хотите удалить комиссариат \'{{ $department->name }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="backUrl"
                                        value="{{ $backUrl ?? route('departments.index') }}">
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                        Удалить отдел
                                    </button>
                                </form>
                            </div>
                        </div>
                    </details>

                    {{-- персона --}}
                    @if (isset($department?->chiefEmployeePosition?->employee?->person?->id))
                        <details class="group bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg overflow-hidden">
                            <!-- Заголовок аккордеона -->
                            <summary
                                class="flex items-center justify-between cursor-pointer p-4 hover:bg-[#A60644]/10 transition-colors duration-200">
                                <h1 class="text-xl font-bold text-[#060606]">Персональные данные</h1>
                                <svg class="w-5 h-5 text-[#565A5B] group-open:rotate-180 transition-transform duration-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7">
                                    </path>
                                </svg>
                            </summary>

                            <!-- Содержимое аккордеона -->
                            <div class="p-4 space-y-3 animate-fadeIn">
                                <div
                                    class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                    <span class="font-medium text-[#565A5B]">ФИО</span>
                                    <span class="text-[#060606]">
                                        {{ $department->chiefEmployeePosition->employee->person->last_name ?? '' }}
                                        {{ $department->chiefEmployeePosition->employee->person->first_name ?? '' }}
                                        {{ $department->chiefEmployeePosition->employee->person->patronymic ?? '' }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0 text-right">
                                    <span class="font-medium text-[#565A5B]">Телефон</span>
                                    <span class="text-[#060606]">
                                        @if (
                                            $department->chiefEmployeePosition->employee->person->phones &&
                                                count($department->chiefEmployeePosition->employee->person->phones) > 0)
                                            @foreach ($department->chiefEmployeePosition->employee->person->phones as $phone)
                                                <div>+{{ $phone }}</div>
                                            @endforeach
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Нет
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0 text-right">
                                    <span class="font-medium text-[#565A5B]">Почта</span>
                                    <span class="text-[#060606]">
                                        @if (
                                            $department->chiefEmployeePosition->employee->person->emails &&
                                                count($department->chiefEmployeePosition->employee->person->emails) > 0)
                                            @foreach ($department->chiefEmployeePosition->employee->person->emails as $email)
                                                <div>{{ $email }}</div>
                                            @endforeach
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Нет
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                    <span class="font-medium text-[#565A5B]">Фото</span>
                                    <span class="text-[#060606]">
                                        @if ($department->chiefEmployeePosition->employee->person->photo)
                                            <div
                                                class="w-28 h-28 rounded-full overflow-hidden border border-[#565A5B] bg-[#060606]">
                                                <img src="{{ asset('storage/' . $department->chiefEmployeePosition->employee->person->photo) }}"
                                                    alt="Фото {{ $department->chiefEmployeePosition->employee->person->last_name }}"
                                                    class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Нет
                                            </span>
                                        @endif
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                    <span class="font-medium text-[#565A5B]">Создан</span>
                                    <span
                                        class="text-[#060606]">{{ \Carbon\Carbon::parse($department?->chiefEmployeePosition?->employee?->person?->created_at)->format('d.m.Y H:i') }}</span>
                                </div>

                                <div
                                    class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                    <span class="font-medium text-[#565A5B]">Обновлен</span>
                                    <span
                                        class="text-[#060606]">{{ \Carbon\Carbon::parse($department?->chiefEmployeePosition?->employee?->person?->updated_at)->format('d.m.Y H:i') }}</span>
                                </div>

                                <div>
                                    <a href="{{ route('persons.edit', [
                                        'id' => $department->chiefEmployee->person->id,
                                        'back_url' => url()->full(),
                                    ]) }}"
                                        class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Редактировать
                                    </a>
                                    <form action="{{ route('persons.delete', $department->chiefEmployee->person->id) }}"
                                        method="POST" class="inline-block mt-0.5"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить персональные данные?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="backUrl" value="{{ url()->full() }}">
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    @else
                        @if (isset($department?->chiefEmployee))
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <a
                                    href="{{ route('employees.edit', [
                                        'id' => $department->chiefEmployee,
                                        'back_url' => url()->full(),
                                    ]) }}">
                                    Нет перс. данных.</a>
                            </span>
                        @endif
                    @endif

                    {{-- user --}}
                    @if (isset($department?->chiefEmployeePosition?->employee?->user?->id))
                        <details class="group bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg overflow-hidden">
                            <!-- Заголовок аккордеона -->
                            <summary
                                class="flex items-center justify-between cursor-pointer p-4 hover:bg-[#A60644]/10 transition-colors duration-200">
                                <h1 class="text-xl font-bold text-[#060606]">Пользовательские данные</h1>
                                <svg class="w-5 h-5 text-[#565A5B] group-open:rotate-180 transition-transform duration-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7">
                                    </path>
                                </svg>
                            </summary>

                            <!-- Содержимое аккордеона -->
                            <div class="p-4 space-y-3 animate-fadeIn">
                                <div
                                    class="flex items-center justify-between py-2 border-b border-[#BFBFBF] last:border-b-0">
                                    <span class="font-medium text-[#565A5B]">Пользователь</span>
                                    <span class="text-[#060606] truncate max-w-[150px]">
                                        {{ $department?->chiefEmployeePosition?->employee?->user?->login ?? '-' }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between py-2 border-b border-[#BFBFBF] last:border-b-0">
                                    <span class="font-medium text-[#565A5B]">Роль</span>
                                    <span class="text-[#060606] truncate max-w-[150px]">
                                        {{ $department?->chiefEmployeePosition?->employee?->user?->role?->description ?? '-' }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between py-2 border-b border-[#BFBFBF] last:border-b-0">
                                    <span class="font-medium text-[#565A5B]">Создан</span>
                                    <span class="text-[#060606] text-sm">
                                        {{ $department?->chiefEmployeePosition?->employee?->user?->created_at ? \Carbon\Carbon::parse($department?->chiefEmployeePosition?->employee?->user?->created_at)->format('d.m.Y H:i') : '-' }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between py-2 border-b border-[#BFBFBF] last:border-b-0">
                                    <span class="font-medium text-[#565A5B]">Обновлен</span>
                                    <span class="text-[#060606] text-sm">
                                        {{ $department?->chiefEmployeePosition?->employee?->user?->updated_at ? \Carbon\Carbon::parse($department?->chiefEmployeePosition?->employee?->user?->updated_at)->format('d.m.Y H:i') : '-' }}
                                    </span>
                                </div>

                                <div>
                                    <a href="{{ route('users.edit', [
                                        'id' => $department->chiefEmployee->user->id,
                                        'back_url' => url()->full(),
                                    ]) }}"
                                        class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Редактировать
                                    </a>
                                    <form action="{{ route('users.delete', $department->chiefEmployee->user->id) }}"
                                        method="POST" class="inline-block mt-0.5"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить пользовательские данные');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="backUrl" value="{{ url()->full() }}">
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    @else
                        @if (isset($department?->chiefEmployee))
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <a
                                    href="{{ route('employees.edit', [
                                        'id' => $department->chiefEmployee,
                                        'back_url' => url()->full(),
                                    ]) }}">
                                    Нет пользовательских данных.</a>
                            </span>
                        @endif
                    @endif

                    {{-- должности --}}
                    @if ($department?->chiefEmployeePosition?->employee?->positions->count() > 0)
                        <details class="group bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg overflow-hidden">
                            <!-- Заголовок аккордеона -->
                            <summary
                                class="flex items-center justify-between cursor-pointer p-4 hover:bg-[#A60644]/10 transition-colors duration-200">
                                <h1 class="text-xl font-bold text-[#060606]">Должности</h1>
                                <svg class="w-5 h-5 text-[#565A5B] group-open:rotate-180 transition-transform duration-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7">
                                    </path>
                                </svg>
                            </summary>

                            <!-- Содержимое аккордеона -->
                            <div class="p-4 space-y-3 animate-fadeIn">
                                <a href="{{ route('employee-positions.create', [
                                    'id' => $department?->chiefEmployeePosition?->employee?->id,
                                    'back_url' => url()->full(),
                                ]) }}"
                                    class="w-full inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Назначить должность
                                </a>
                                @foreach ($department?->chiefEmployeePosition?->employee?->positions as $position)
                                    <div class="bg-white/50 rounded-lg border border-[#BFBFBF] p-4 mb-4 last:mb-0">
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-[#060606]">Должность</span>
                                                <span class="text-[#060606]">{{ $position->position->name }}</span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-[#060606]">Тип должности</span>
                                                <span
                                                    class="text-[#060606]">{{ $position->position->positionType->name }}</span>
                                            </div>

                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-[#060606]">Ставка</span>
                                                <span class="text-[#060606]">{{ $position->rate }}</span>
                                            </div>

                                            @if ($position->department_id)
                                                <div class="flex items-center justify-between">
                                                    <span class="font-medium text-[#565A5B]">Комиссариат</span>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $position->department->name }}</span>
                                                </div>
                                            @endif

                                            @if ($position->department_id)
                                                <div class="flex items-center justify-between">
                                                    <span class="font-medium text-[#565A5B]">Отдел</span>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $position->department->name }}</span>
                                                </div>
                                            @endif

                                            @if ($position->division_id)
                                                <div class="flex items-center justify-between">
                                                    <span class="font-medium text-[#565A5B]">Отделение</span>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $position->division->name }}</span>
                                                </div>
                                            @endif

                                            @if ($position->is_independent !== false)
                                                <i
                                                    style="color: rgb(17, 183, 17)">({{ $position->is_independent ? 'Самостоятельная должность' : '' }})</i>
                                            @endif

                                            <div>
                                                <a href="{{ route('employee-positions.edit', [
                                                    'id' => $department->chiefEmployee->id,
                                                    'back_url' => url()->full(),
                                                ]) }}"
                                                    class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                        </path>
                                                    </svg>
                                                    Редактировать
                                                </a>
                                                <form action="{{ route('employee-positions.delete', $position->id) }}"
                                                    method="POST" class="inline-block mt-0.5"
                                                    onsubmit="return confirm('Вы уверены, что хотите удалить должность?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="backUrl" value="{{ url()->full() }}">
                                                    <button type="submit"
                                                        class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                        Удалить
                                                    </button>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    {{-- кнопка создания сотрудника --}}
                    @if (isset($department?->chiefEmployee))
                        <a href="{{ route('employees.edit', [
                            'id' => $department?->chiefEmployeePosition?->employee?->id,
                            'back_url' => url()->full(),
                        ]) }}"
                            class="w-full inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Редактировать сотрудника
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
