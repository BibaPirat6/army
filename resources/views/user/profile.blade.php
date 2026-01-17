@extends('layouts.main')

@section('header-title')
    Ваш профиль
@endsection

@section('content')
    <h1>профиль</h1>
    <form action="{{ route('logout') }}" method="post">
        @csrf
        <button type="submit">Выйти из системы</button>
    </form>
@endsection
