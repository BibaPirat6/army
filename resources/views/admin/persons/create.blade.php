@extends('layouts.main')

@section('header-title')
    Создание Персональных данных
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h1>Создание Персональных данных</h1>
    <h3><a href="{{ $backUrl ? $backUrl : route("persons.index") }}">Назад к списку</a></h3>



    <div>
        <form action="{{ route('persons.store') }}" method="post" enctype="multipart/form-data">
            @csrf

            <label for="last_name">Фамилия</label><br>
            <input type="text" name="last_name" id="last_name" placeholder="Введите Фамилию"
                value="{{ old('last_name') }}"><br>
            <label for="first_name">Имя</label><br>
            <input type="text" name="first_name" id="first_name" placeholder="Введите Имя"
                value="{{ old('first_name') }}"><br>
            <label for="patronymic">Отчество</label><br>
            <input type="text" name="patronymic" id="patronymic" placeholder="Введите Отчество"
                value="{{ old('patronymic') }}"><br>

            <label for="email">Почта</label><br>
            <input type="email" name="email" id="email" placeholder="Введите Почту" value="{{ old('email') }}"><br>
            <label for="phone">Телефон</label><br>
            <input type="tel" name="phone" id="phone" placeholder="Введите Телефон"
                value="{{ old('phone') }}"><br>


            <label for="photo">Фото</label><br>
            <input type="file" name="photo" id="photo" placeholder="Введите Фото"><br>


            <input type="hidden" name="backUrl" value="{{ $backUrl }}">
            <input type="hidden" name="employeeId" value="{{ $employeeId }}">


            <button type="submit">Создать</button>
        </form>
    </div>


@endsection
