@extends('layouts.main')

@section('header-title')
    {{ $commissariat->name }}
@endsection

@section('vite-resources')
    @vite(['resources/css/structure.css', 'resources/js/app.js'])
@endsection

@section('content')
    <div id="app">
        <structure-graph :data="{{ json_encode($graphData) }}"></structure-graph>
    </div>
@endsection