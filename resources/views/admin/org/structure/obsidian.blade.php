@extends('layouts.main')

@section('header-title')
    {{ $commissariat->name }}
@endsection

@section('vite-resources')
    @vite(['resources/js/app.js'])
@endsection

@section('content')
    <div id="app">
        <structure-graph 
            :nodes="{{ json_encode($graphData['nodes']) }}" 
            :links="{{ json_encode($graphData['links']) }}">
        </structure-graph>
    </div>
@endsection