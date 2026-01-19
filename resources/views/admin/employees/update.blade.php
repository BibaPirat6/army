@extends('layouts.main')

@section('header-title')
    Сотрудник
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h1>Сотрудник</h1>

    <p><a href="{{ url()->previous() }}">Назад</a></p>

    {{-- здесь --}}
    <form action="" method="">
        <button></button>
    </form>
    
@endsection
