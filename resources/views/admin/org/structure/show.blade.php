@extends('layouts.main')

@section('header-title')
    Структура комиссариата
@endsection

@section('vite-resources')
    @vite(['resources/css/structure.css', 'resources/js/structure.js'])
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/structure.css') }}">
@endpush





@section('content')
    <button id="resetView">Вернуться к центру</button>
    <div class="instruction-move">
        Управление ~ <span style="color: chartreuse">КОЛЕСИКОМ МЫШИ</span> +
        Кнопка <span style="color: hotpink">ПРОБЕЛ</span> и <span style="color: hotpink">ЛЕВАЯ</span> кнопка мыши
    </div>


    <div id="viewport">
        <div id="canvas">
            <div class="tree">
                <div class="boss-wrapper">
                    <!-- Начальник комиссариата -->
                    <div class="node boss">Начальник комиссариата
                        <br>
                        {{ $commissariat->chiefEmployee->person->last_name }}
                        {{ $commissariat->chiefEmployee->person->first_name }}
                        {{ $commissariat->chiefEmployee->person->patronymic }}
                        <br>
                        <details>
                            @if ($commissariat->chiefEmployee->person->photo)
                                <img src="{{ asset('storage/' . $commissariat->chiefEmployee->person->photo) }}"
                                    alt="Фото {{ $commissariat->chiefEmployee->person->last_name }}">
                            @else
                                <div>
                                    <span>Нет фото</span>
                                </div>
                            @endif
                            @if ($commissariat->chiefEmployee->positions->count() > 0)
                                <p>Должности</p>
                                <ul>
                                    @foreach ($commissariat->chiefEmployee->positions as $position)
                                        <li>{{ $position->position->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>
                                    <span>Не назначены Должности</span>
                                </div>
                            @endif
                        </details>
                    </div>

                    {{-- линии --}}
                    <div class="lines-to-departments">
                        <div class="line vertical"></div>
                        <div class="line horizontal"></div>
                    </div>

                    <!-- ОТДЕЛЫ -->
                    <div class="departments">
                        @foreach ($commissariat->departments as $department)
                            <div class="department">
                                {{-- Данные по отделу --}}
                                <div class="node dept-title">{{ $department->name }}</div>
                                <div class="node head">Начальник отдела
                                    <br>
                                    {{ $department->chiefEmployee->person->last_name }}
                                    {{ $department->chiefEmployee->person->first_name }}
                                    {{ $department->chiefEmployee->person->patronymic }}
                                    <br>
                                    <details>
                                        @if ($department->chiefEmployee->person->photo)
                                            <img src="{{ asset('storage/' . $department->chiefEmployee->person->photo) }}"
                                                alt="Фото {{ $department->chiefEmployee->person->last_name }}">
                                        @else
                                            <div>
                                                <span>Нет фото</span>
                                            </div>
                                        @endif
                                        @if ($department->chiefEmployee->positions->count() > 0)
                                            <p>Должности</p>
                                            <ul>
                                                @foreach ($department->chiefEmployee->positions as $position)
                                                    <li>{{ $position->position->name }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div>
                                                <span>Не назначены Должности</span>
                                            </div>
                                        @endif
                                    </details>
                                </div>

                                {{-- Отделения --}}
                                @if ($department->divisions->count() > 0)
                                    <div class="units">
                                        @foreach ($department->divisions as $division)
                                            <div class="unit">
                                                <div class="unit-title">{{ $division->name }}</div>
                                                <div class="node head">Начальник отделения
                                                    <br>
                                                    {{ $division->chiefEmployee->person->last_name }}
                                                    {{ $division->chiefEmployee->person->first_name }}
                                                    {{ $division->chiefEmployee->person->patronymic }}
                                                    <br>
                                                    <details>
                                                        @if ($division->chiefEmployee->person->photo)
                                                            <img src="{{ asset('storage/' . $division->chiefEmployee->person->photo) }}"
                                                                alt="Фото {{ $division->chiefEmployee->person->last_name }}">
                                                        @else
                                                            <div>
                                                                <span>Нет фото</span>
                                                            </div>
                                                        @endif
                                                        @if ($division->chiefEmployee->positions->count() > 0)
                                                            <p>Должности</p>
                                                            <ul>
                                                                @foreach ($division->chiefEmployee->positions as $position)
                                                                    <li>{{ $position->position->name }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <div>
                                                                <span>Не назначены Должности</span>
                                                            </div>
                                                        @endif
                                                    </details>
                                                </div>
                                                {{-- сотрудники отделения --}}
                                                <div class="employees">
                                                    <div class="employee">Сотрудник</div>
                                                    <div class="employee">Сотрудник</div>
                                                    <div class="employee">Сотрудник</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach





                        {{-- сюда самостоятельных --}}
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        console.log('Inline script works');
    </script>
    <script src="{{ asset('js/structure.js') }}"></script>
@endpush
