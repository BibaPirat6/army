@extends('layouts.main')

@section('header-title')
    Сотрудник
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h1>Сотрудник</h1>

    <p><a href="{{ url()->previous() }}">Назад</a></p>

    <form action="{{ route('employees.update.post', $employee->id) }}" method="post">
        @csrf
        <label for="user_id">Выберите пользователя</label><br>
        <select name="user_id" id="user_id">
            @if ($users && count($users) > 0)
                <option value="null">Не выбирать</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}"
                        {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->login }}
                    </option>
                @endforeach
            @else
                <option selected disabled>Нет доступных пользователей</option>
            @endif

            @if ($employee->user_id && !$users->contains('id', $employee->user_id))
                <option value="{{ $employee->user_id }}" selected>
                    {{ $employee->user->login ?? 'Текущий пользователь (ID: ' . $employee->user_id . ')' }}
                </option>
            @endif
        </select><br>

        <label for="person_id">Выберите персональные данные сотрудника</label><br>
        <select name="person_id" id="person_id">
            @if ($persons && count($persons) > 0)
                <option value="null">Не выбирать</option>
                @foreach ($persons as $person)
                    <option value="{{ $person->id }}"
                        {{ old('person_id', $employee->person_id) == $person->id ? 'selected' : '' }}>
                        {{ $person->last_name }} {{ $person->first_name }} {{ $person->phone }}
                    </option>
                @endforeach
            @else
                <option selected disabled>Нет свободных персональных данных</option>
            @endif

            @if ($employee->person_id && !$persons->contains('id', $employee->person_id))
                <option value="{{ $employee->person_id }}" selected>
                    {{ $employee->person->last_name ?? 'Текущие данные (ID: ' . $employee->person_id . ')' }}
                    {{ $employee->person->first_name ?? '' }}
                    {{ $employee->person->phone ?? '' }}
                </option>
            @endif
        </select> <br>

        <label for="role">Выберите роль*</label><br>
        <select name="role" id="role">
            <option value="admin" {{ old('role', $employee->role) == 'admin' ? 'selected' : '' }}>Администратор (HR)
            </option>
            <option value="user" {{ old('role', $employee->role) == 'user' ? 'selected' : '' }}>Обычный пользователь
            </option>
        </select> <br>

        <label for="work_status">Рабочий статус*</label><br>
        <select name="work_status" id="work_status">
            <option value="vacant" {{ old('work_status', $employee->work_status) == 'vacant' ? 'selected' : '' }}>ВАКАНТ
            </option>
            <option value="fired" {{ old('work_status', $employee->work_status) == 'fired' ? 'selected' : '' }}>УВОЛЕН
            </option>
            <option value="active" {{ old('work_status', $employee->work_status) == 'active' ? 'selected' : '' }}>
                РАБОТАЕТ</option>
        </select> <br>

        <button type="submit">Изменить</button>
    </form>

@endsection
