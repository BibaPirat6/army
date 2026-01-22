@extends('layouts.main')

@section('header-title')
    Создание должности
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


    <h1> Создание должности</h1>
    <p><a href="{{ route('positions.index') }}">Назад к списку должностей</a></p>

    <form action="{{ route('positions.store') }}" method="POST">
        @csrf
        <div>
            <label for="name">Название должности:</label>
            <input type="text" id="name" name="name" placeholder="Введите название должности" value="{{ old('name') }}">
        </div>
        <div>
            <label for="position_type_id">Тип должности:</label>
            <select id="position_type_id" name="position_type_id" required>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" {{ old('position_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit">Создать должность</button>
    </form>
@endsection
