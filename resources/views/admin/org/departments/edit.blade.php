@extends('layouts.main')

@section('header-title')
    Изменение отдела
@endsection

@section('content')
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">
                {{ $error }}
            </div>
        @endforeach
    @endif


    <h1>Изменение отдела</h1>
    <p><a href="{{ route('departments.index') }}">Назад к списку</a></p>

    <form action="{{ route('departments.update', $department->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="name">Название отдела:</label>
            <input type="text" name="name" id="name" placeholder="Название отдела"
                value="{{ old('name', $department->name) }}">
        </div>

        <div>
            <label for="is_active">Действующий:</label>
            <select name="is_active" id="is_active">
                <option value="1" {{ old('is_active', $department->is_active) ? 'selected' : '' }}>Да</option>
                <option value="0" {{ !old('is_active', $department->is_active) ? 'selected' : '' }}>Нет</option>
            </select>
        </div>

        <button type="submit">Создать</button>
    </form>
@endsection
