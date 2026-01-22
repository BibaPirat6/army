@extends('layouts.main')

@section('header-title')
    Типы должностей
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <h1>Типы должностей</h1>
    <h3><a href="{{ route('position-types.create') }}">Создать новый тип должности</a></h3>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($types as $type)
                <tr>
                    <td>{{ $type->id }}</td>
                    <td>{{ $type->name }}</td>
                    <td><a href="{{ route('position-types.edit', $type->id) }}">Редактировать</a></td>
                    <td>
                        <form action="{{ route('position-types.delete', $type->id) }}" method="POST">
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
