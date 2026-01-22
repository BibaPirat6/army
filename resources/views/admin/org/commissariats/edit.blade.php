@extends('layouts.main')

@section('header-title')
    Изменение коммиссариата
@endsection

@section('content')
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">
                {{ $error }}
            </div>
        @endforeach
    @endif


    <h1>Изменение коммиссариата</h1>
    <p><a href="{{ route('commissariats.index') }}">Назад к списку</a></p>

    <form action="{{ route('commissariats.update', $commissariat->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="name">Название комиссариата:</label>
            <input type="text" name="name" id="name" placeholder="Название комиссариата"
                value="{{ old('name', $commissariat->name) }}">
        </div>

        <div>
            <label for="is_active">Действующий:</label>
            <select name="is_active" id="is_active">
                <option value="1" {{ old('is_active', $commissariat->is_active) ? 'selected' : '' }}>Да</option>
                <option value="0" {{ !old('is_active', $commissariat->is_active) ? 'selected' : '' }}>Нет</option>
            </select>
        </div>

        <button type="submit">Создать</button>
    </form>
@endsection
