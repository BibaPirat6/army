@extends('layouts.main')

@section('header-title')
    Типы должностей
@endsection

@section('content')
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">
                {{ $error }}
            </div>
        @endforeach
    @endif


    <h1>Редактирование должности</h1>
    <p><a href="{{ route('position-types.index') }}">Назад</a></p>
    <form action="{{ route('position-types.update', $type->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="name">Название типа должности:</label>
            <input type="text" id="name" name="name" placeholder="Введите название типа"
                value="{{ old('name', $type->name) }}">
        </div>
        <button type="submit">Сохранить</button>
    </form>
@endsection
