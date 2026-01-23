@extends('layouts.main')

@section('header-title')
    Отделения
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <h1>Отделения</h1>
    <h3><a href="{{ route('divisions.create') }}">Добавить отделение</a></h3>

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
            @foreach ($divisions as $division)
                <tr>
                    <td>{{ $division->id }}</td>
                    <td>{{ $division->name }}</td>
                    <td>{{ $division->is_active ? 'Да' : 'Нет' }}</td>
                    <td>
                        <a href="{{ route('divisions.edit', $division->id) }}">Редактировать</a>
                        <form action="{{ route('divisions.delete', $division->id) }}" method="POST">
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
