@extends('layouts.main')

@section('header-title')
    Редактировать статус
@endsection

@section('content')
    <h1>Редактировать статус</h1>


    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h3><a href="{{ route('work-statuses.index') }}">Назад к списку статусов</a></h3>

    <div>
        <form action="{{ route('work-statuses.update', $status->id) }}" method="post">
            @csrf
            @method('PUT')
            <label for="name">Название (на английском):</label><br>
            <input type="text" name="name" id="name" value="{{ old('name', $status->name) }}">
            <br>
            <label for="description">Название (на русском):</label> <br>
            <input type="text" name="description" id="description" value="{{ old('description', $status->description) }}">
            <br>
            <button type="submit">Обновить</button>
        </form>
    </div>
@endsection
