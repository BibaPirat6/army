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


    <h3><a href="{{ route('persons.create') }}">Создать Персональные данные</a></h3>


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
                              >
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
                        <a href="{{ route('persons.edit', $person->id) }}">Редактировать</a>
                        <form action="{{ route('persons.delete', $person->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @include('includes.pagination', ['paginator' => $persons])
@endsection
