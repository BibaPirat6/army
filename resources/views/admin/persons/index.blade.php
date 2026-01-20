@extends('layouts.main')

@section('header-title')
    Персональные данные
@endsection

@section('content')
    <h1>Персональные данные</h1>


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

    <div>
        <form action="{{ route('persons.post') }}" method="post" enctype="multipart/form-data">
            @csrf

            <label for="last_name">Фамилия</label><br>
            <input type="text" name="last_name" id="last_name" placeholder="Введите Фамилию"
                value="{{ old('last_name') }}"><br>
            <label for="first_name">Имя</label><br>
            <input type="text" name="first_name" id="first_name" placeholder="Введите Имя"
                value="{{ old('first_name') }}"><br>
            <label for="patronymic">Отчество</label><br>
            <input type="text" name="patronymic" id="patronymic" placeholder="Введите Отчество"
                value="{{ old('patronymic') }}"><br>

            <label for="email">Почта</label><br>
            <input type="email" name="email" id="email" placeholder="Введите Почту" value="{{ old('email') }}"><br>
            <label for="phone">Телефон</label><br>
            <input type="tel" name="phone" id="phone" placeholder="Введите Телефон"
                value="{{ old('phone') }}"><br>


            <label for="photo">Фото</label><br>
            <input type="file" name="photo" id="photo" placeholder="Введите Фото"><br>


            <button type="submit">Создать</button>
        </form>
    </div>
    <h3>Список</h3>
    <table>
        <thead>
            <tr>
                <th>Фото</th>
                <th>Фамилия</th>
                <th>Имя</th>
                <th>Отчество</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($persons as $person)
                <tr>
                    <td>
                        @if ($person->photo)
                            <img src="{{ asset('storage/' . $person->photo) }}" alt="Фото {{ $person->surname }}"
                                width="100">
                        @else
                            Нет фото
                        @endif
                    </td>
                    <td>{{ $person->last_name }}</td>
                    <td>{{ $person->first_name }}</td>
                    <td>{{ $person->patronymic }}</td>
                    <td>{{ $person->phone }}</td>
                    <td>{{ $person->email }}</td>
                    <td>
                        <a href="{{ route('persons.update.index', $person->id) }}">Редактировать</a>
                        <form action="{{ route('persons.delete', $person->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>


    @endsection
