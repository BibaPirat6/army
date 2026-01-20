@extends('layouts.main')

@section('header-title')
    Рабочие статусы
@endsection

@section('content')
    <h1>Рабочие статусы</h1>


    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if (session('success'))
        {{ session('success') }}
    @endif

    <div>
        <form action="{{ route('work-statuses.post') }}" method="post">
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

    <div>
        @foreach ($statuses as $status)
            <p>{{ $status->id }}. {{ $status->description }} - (на английском) {{ $status->name }}</p>
            <hr>
        @endforeach
    </div>
@endsection
