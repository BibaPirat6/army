@extends('layouts.main')

@section('header-title')
    Отделы
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <h1>Отделы</h1>
    <h3><a href="{{ route('departments.create') }}">Добавить отдел</a></h3>

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
            @foreach ($departments as $department)
                <tr>
                    <td>{{ $department->id }}</td>
                    <td>{{ $department->name }}</td>
                    <td>{{ $department->is_active ? 'Да' : 'Нет' }}</td>
                    <td>
                        <a href="{{ route('departments.edit', $department->id) }}">Редактировать</a>
                        <form action="{{ route('departments.delete', $department->id) }}" method="POST">
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
