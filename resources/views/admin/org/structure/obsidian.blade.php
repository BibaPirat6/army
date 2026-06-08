@extends('layouts.main')

@section('header-title')
    {{ $commissariat->name }}
@endsection

@section('vite-resources')
    @vite(['resources/js/app.js'])
@endsection

@section('content')

    <a href="{{ $backUrl ?? route('commissariats.index') }}"
        class="fixed left-5 top-20 z-[100] inline-flex items-center gap-2 px-4 py-2 bg-white/90 backdrop-blur-sm rounded-xl text-[#A60644] font-medium hover:bg-white shadow-md hover:shadow-lg transition-all duration-200 group">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Назад
    </a>

    <div class="fixed bottom-5 right-5 z-[1000] flex flex-col gap-3">
        <!-- Зеленая кнопка - Штат -->
        <a href="{{ route("commissariat-positions.index", [
        "commissariat_id" => $commissariat->id,
        "back_url" => url()->full()
    ]) }}"
            class="px-4 py-2.5 rounded-xl bg-[#1ba606] text-white text-sm font-medium hover:bg-green-600 transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95 text-center">
            Штатные должности
        </a>
    </div>


    <div id="vue-app">
        <structure-graph :nodes="{{ json_encode($graphData['nodes']) }}" :links="{{ json_encode($graphData['links']) }}"
            back-url="{{ url()->full() }}">
        </structure-graph>
    </div>
@endsection