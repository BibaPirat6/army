@extends('layouts.main')

@section('header-title')
    Должности
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <h1>Должности</h1>
    <h3><a href="{{ route('positions.create') }}">Создать новую должность</a></h3>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Тип Должности</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($positions as $position)
                <tr>
                    <td>{{ $position->id }}</td>
                    <td>{{ $position->name }}</td>
                    <td>{{ $position->positionType->name }}</td>
                    <td>
                        <a href="{{ route('positions.edit', $position->id) }}">Редактировать</a>
                        <form action="{{ route('positions.delete', $position->id) }}" method="POST">
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
