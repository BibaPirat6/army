@extends('layouts.main')

@section('header-title')
    Редактирование должности
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


    <h1>Редактирование должности</h1>
    <p><a href="{{ route('positions.index') }}">Назад к списку должностей</a></p>

    <form action="{{ route('positions.update', $position->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="name">Название должности:</label>
            <input type="text" name="name" id="name" placeholder="Введите должность"
                value="{{ old('name', $position->name) }}">
        </div>
        <div>
            <label for="position_type_id">Тип должности:</label>
            <select name="position_type_id" id="position_type_id">
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" {{ old("position_type_id", $position->positionType->id) == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit">Обновить</button>
    </form>
@endsection
