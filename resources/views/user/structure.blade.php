@extends('layouts.main')

@section('header-title')
    Структура сотрудников
@endsection

@section('content')
    <h1>структура</h1>
    <form action="{{ route('logout') }}" method="post">
        @csrf
        <button type="submit">Выйти из системы</button>
    </form>
@endsection
