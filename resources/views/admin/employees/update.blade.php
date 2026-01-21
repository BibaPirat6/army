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
            <option value="">Не выбирать</option>
            @if ($users && count($users) > 0)
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->login }}</option>
                @endforeach
            @else
                <option disabled>Нет свободных пользователей</option>
            @endif
        </select><br>

        <label for="person_id">Выберите персональные данные сотрудника</label><br>
        <select name="person_id" id="person_id">
            <option value="">Не выбирать</option>
            @if ($persons && count($persons) > 0)
                @foreach ($persons as $person)
                    <option value="{{ $person->id }}">
                        {{ $person->last_name }} {{ $person->first_name }} {{ $person->phone }}
                    </option>
                @endforeach
            @else
                <option disabled>Нет свободных персональных данных</option>
            @endif
        </select> <br>

        <label for="work_status">Рабочий статус*</label><br>
        <select name="work_status" id="work_status">
            @foreach ($statuses as $status)
                <option value="{{ $status->id }}" @if ($status->name == $employee->workStatus->name) selected @endif>
                    {{ $status->description }}
                </option>
            @endforeach
        </select> <br>

        <button type="submit">Изменить</button>
    </form>

@endsection
