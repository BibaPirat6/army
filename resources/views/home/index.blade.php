@extends('layouts.main')

@section('header-title')
    Структура
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif

    <h1>Структура</h1>
@endsection
