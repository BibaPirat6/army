@extends('layouts.main')

@section('header-title')
    Добавить рабочий статус
@endsection

@section('content')
    <h1>Добавить рабочий статус</h1>


    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h3><a href="{{ route('work-statuses.index') }}">Назад к списку статусов</a></h3>

    <div>
        <form action="{{ route('work-statuses.store') }}" method="post">
            @csrf
            <label for="name">Название (на английском):</label><br>
            <input type="text" name="name" id="name" value="{{ old('name') }}">
            <br>
            <label for="description">Название (на русском):</label> <br>
            <input type="text" name="description" id="description" value="{{ old('description') }}">
            <br>
            <button type="submit">Добавить статус</button>
        </form>
    </div>
@endsection
