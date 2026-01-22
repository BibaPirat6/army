@extends('layouts.main')

@section('header-title')
    Комиссариаты
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <h1>Комиссариаты</h1>
    <h3><a href="{{ route('commissariats.create') }}">Добавить комиссариат</a></h3>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Действующий</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($commissariats as $commissariat)
                <tr>
                    <td>{{ $commissariat->id }}</td>
                    <td>{{ $commissariat->name }}</td>
                    <td>{{ $commissariat->is_active ? 'Да' : 'Нет' }}</td>
                    <td>
                        <a href="{{ route('commissariats.edit', $commissariat->id) }}">Редактировать</a>
                        <form action="{{ route('commissariats.delete', $commissariat->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
