@extends('layouts.main')

@section('header-title')
    Комиссариаты
@endsection

@section(section: 'content')
    <h1>Комиссариаты</h1>

    @if ($commissariats->count() > 0)
        <ul>
            @foreach ($commissariats as $commissariat)
                <li>
                    <a href="{{ route('structure.show', $commissariat->id) }}">{{ $commissariat->name }}</a>
                </li>
            @endforeach
        </ul>
    @else
        <p>Нет доступных комиссариатов.</p>
    @endif
@endsection
