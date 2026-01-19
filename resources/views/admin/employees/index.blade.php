@extends('layouts.main')

@section('header-title')
    Сотрудники
@endsection

@section('content')
    <h1>Сотрудники</h1>

    {{-- создать сотрудника --}}
    <div>
        <h3>Создать сотрудника</h3>
        <form action="{{ route("employees.post") }}" method="post">
            @csrf

            <label for="user">Выберите пользователя</label><br>
            <select name="user" id="user">
                @if ($users)
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->login }}</option>
                    @endforeach
                @endif
            </select><br>

            <label for="person">Выберите персональные данные сотрудника</label><br>
            <select name="person" id="person">
                @if ($persons)
                    @foreach ($persons as $person)
                        <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->phone }}</option>
                    @endforeach
                @endif
            </select> <br>

            <label for="role">Выберите роль*</label><br>
            <select name="role" id="role">
                <option value="admin">Администратор (HR)</option>
                <option value="user">Обычный пользователь</option>
            </select> <br>

            <label for="status">Рабочий статус*</label><br>
            <select name="status" id="status">
                <option value="vacant">ВАКАНТ</option>
                <option value="fired">УВОЛЕН</option>
                <option value="active">РАБОТАЕТ</option>
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
                        </ul>
                    @else
                        <p style="color: gray;">Пользователь не указан</p>
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
                            <li>Создан: {{ $employee->person->created_at ?? '—' }}</li>
                            <li>Обновлен:
                                {{ $employee->person->updated_at ? $employee->person->updated_at->format('d.m.Y H:i') : '—' }}
                                </p>
                            </li>
                        </ul>
                    @else
                        <p style="color: gray;">Персона не указана</p>
                    @endif

                    <p><strong>РОЛЬ:</strong> {{ $employee->role ?? '—' }}</p>
                    <p><strong>СТАТУС:</strong> {{ $employee->work_status ?? '—' }}</p>
                    <p><strong>СОЗДАН:</strong> {{ $employee->created_at->format('d.m.Y H:i') }}</p>
                    <p><strong>ОБНОВЛЕН:</strong>
                        {{ $employee->updated_at ? $employee->updated_at->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div>
                    <p><a href="{{ route('users.update.index', $employee->id) }}">Изменить</a></p>
                    <form action="{{ route('users.delete', $employee->id) }}" method="post">@csrf <button
                            type="submit">Удалить</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
