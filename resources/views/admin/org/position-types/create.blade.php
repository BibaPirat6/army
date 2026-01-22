@extends('layouts.main')

@section('header-title')
    Типы должностей
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1>Создание типа должности</h1>
    <p><a href="{{ route('position-types.index') }}">Назад</a></p>
    <form action="{{ route('position-types.store') }}" method="POST">
        @csrf
        <div>
            <label for="name">Название типа должности:</label>
            <input type="text" id="name" name="name" placeholder="Введите название типа" value="{{ old('name') }}" required>
        </div>
        <button type="submit">Создать</button>
    </form>
@endsection
