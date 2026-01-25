@extends('layouts.main')

@section('header-title')
    Рабочие статусы
@endsection

@section('content')
    <h1>Рабочие статусы</h1>

    @if (session('success'))
        {{ session('success') }}
    @endif

    <h3><a href="{{ route('work-statuses.create') }}">Добавить статус</a></h3>

    <div>
        @foreach ($statuses as $status)
            <p>{{ $status->id }}. {{ $status->description }} - (на английском) {{ $status->name }}</p>
            <a href="{{ route('work-statuses.edit', $status->id) }}">Редактировать</a>
            <form action="{{ route('work-statuses.delete', $status->id) }}" method="post">
                @csrf
                @method('DELETE')
                <button type="submit">Удалить
                    статус</button>
            </form>
            <hr>
        @endforeach

        @include('includes.pagination', ['paginator' => $statuses])
    </div>
@endsection
