@extends('layouts.main')

@section('header-title')
    Сотрудники
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if (session('success'))
        {{ session('success') }}
    @endif

    <h1>Сотрудники</h1>

    {{-- создать сотрудника --}}
    <div>
        <h3>Создать сотрудника</h3>
        <form action="{{ route('employees.post') }}" method="post">
            @csrf

            <label for="user_id">Выберите пользователя</label><br>
            <select name="user_id" id="user_id">
                @if ($users && count($persons) > 0)
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->login }}</option>
                    @endforeach
                    <option selected value="null">Не выбирать</option>
                @else
                    <option selected disabled>Нет свободных персональных данных</option>
                @endif
            </select><br>

            <label for="person_id">Выберите персональные данные сотрудника</label><br>
            <select name="person_id" id="person_id">
                @if ($persons && count($persons) > 0)
                    @foreach ($persons as $person)
                        <option value="{{ $person->id }}">
                            {{ $person->last_name }} {{ $person->first_name }} {{ $person->phone }}
                        </option>
                    @endforeach
                    <option selected value="null">Не выбирать</option>
                @else
                    <option selected disabled>Нет свободных персональных данных</option>
                @endif
            </select> <br>

            <label for="role">Выберите роль*</label><br>
            <select name="role" id="role">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @if ($role->name == 'user') selected @endif>
                        {{ $role->description }}
                    </option>
                @endforeach
            </select> <br>

            <label for="work_status">Рабочий статус*</label><br>
            <select name="work_status" id="work_status">
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}" @if ($status->name == 'inactive') selected @endif>
                        {{ $status->description }}
                    </option>
                @endforeach
            </select> <br>


            <button type="submit">Создать</button>
        </form>
    </div>


    {{-- вывод --}}
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        @foreach ($employees as $employee)
            <div style="width: 20%; background-color: antiquewhite; padding: 10px;">
                <div>
                    <p>ID: {{ $employee->id }}</p>

                    <p><strong>ПОЛЬЗОВАТЕЛЬ:</strong></p>
                    @if ($employee->user)
                        <ul>
                            <li>ID: {{ $employee->user->id }}</li>
                            <li>Логин: {{ $employee->user->login }}</li>
                            <li>Создан: {{ $employee->user->created_at->format('d.m.Y H:i') }}</li>
                            <li>Обновлен:
                                {{ $employee->user->updated_at ? $employee->user->updated_at->format('d.m.Y H:i') : '—' }}
                                </p>
                            </li>
                            <li><button>Изменить пользователя</button></li>
                        </ul>
                    @else
                        <p style="color: gray;">Пользователь не указан</p>
                        <p><a href="/">Создать пользователя</a></p>
                    @endif

                    <p><strong>ПЕРСОНАЛЬНЫЕ ДАННЫЕ:</strong></p>
                    @if ($employee->person)
                        <ul>
                            <li>ID: {{ $employee->person->id }}</li>
                            <li>Имя: {{ $employee->person->first_name ?? '—' }}</li>
                            <li>Фамилия: {{ $employee->person->last_name ?? '—' }}</li>
                            <li>Отчество: {{ $employee->person->patronymic ?? '—' }}</li>
                            <li>Телефон: {{ $employee->person->phone ?? '—' }}</li>
                            <li>Email: {{ $employee->person->email ?? '—' }}</li>
                            <li>Создан: {{ $employee->person->created_at->format('d.m.Y H:i') ?? '—' }}</li>
                            <li>Обновлен:
                                {{ $employee->person->updated_at ? $employee->person->updated_at->format('d.m.Y H:i') : '—' }}
                                </p>
                            </li>
                            <li>
                                @if ($employee->person->photo)
                                    <div>
                                        <img src="{{ asset('storage/' . $employee->person->photo) }}"
                                            alt="Фото пользователя" style="max-width: 200px; max-height: 200px;">
                                    </div>
                                @endif
                            </li>
                            <li><button>Изменить данные</button></li>
                        </ul>
                    @else
                        <p style="color: gray;">Персона не указана</p>
                        <p><a href="/">Создать персональные данные</a></p>
                    @endif

                    <p><strong>РОЛЬ:</strong> {{ $employee->user->role->description ?? '—' }}</p>
                    <p><strong>СТАТУС:</strong> {{ $employee->workStatus->description ?? '—' }}</p>
                    <p><strong>СОЗДАН:</strong> {{ $employee->created_at->format('d.m.Y H:i') }}</p>
                    <p><strong>ОБНОВЛЕН:</strong>
                        {{ $employee->updated_at ? $employee->updated_at->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div>
                    <p><a href="{{ route('employees.update.index', $employee->id) }}">Изменить сотрудника</a></p>
                    <form action="{{ route('employees.delete', $employee->id) }}" method="post">@csrf
                        @method('DELETE')
                        <button type="submit">Удалить сотрудника</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
