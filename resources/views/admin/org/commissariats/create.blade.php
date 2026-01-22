@extends('layouts.main')

@section('header-title')
    Добавление коммиссариата
@endsection

@section('content')
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">
                {{ $error }}
            </div>
        @endforeach
    @endif


    <h1> Добавление коммиссариата</h1>
    <p><a href="{{ route('commissariats.index') }}">Назад к списку</a></p>

    <form action="{{ route('commissariats.store') }}" method="POST">
        @csrf
        <div>
            <label for="name">Название комиссариата:</label>
            <input type="text" name="name" id="name" placeholder="Название комиссариата" value="{{ old('name') }}">
        </div>

        <div>
            <label for="is_active">Действующий:</label>
            <select name="is_active" id="is_active">
                <option value="1" selected>Да</option>
                <option value="0">Нет</option>
            </select>
        </div>

        <button type="submit">Создать</button>
    </form>
@endsection
