@extends('layouts.main')

@section('header-title')
    Структура
@endsection

@section('content')
    <h1>Структура</h1>

    <nav>
        @if (auth()->check() && auth()->user()->employee?->role === 'admin')
            <ul>
                <li><a href="{{ route('users.index') }}">Пользователи</a></li>
                <li><a href="{{ route('employees.index') }}">Сотрудники</a></li>
            </ul>
        @endif
    </nav>
@endsection
