@extends('layouts.main')

@section('header-title')
    Структура комиссариата
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/structure.css') }}">
@endpush





@section('content')
    <button id="resetView">Вернуться к центру</button>


    <div id="viewport">
        <div id="canvas">
            <div class="tree">
                <div class="boss-wrapper">
                    <!-- БОСС -->
                    <div class="node boss">БОСС</div>

                    <div class="lines-to-departments">
                        <div class="line vertical"></div>
                        <div class="line horizontal"></div>
                    </div>

                    <!-- ОТДЕЛЫ -->
                    <div class="departments">

                        <!-- ОТДЕЛ A -->
                        <div class="department">
                            <div class="node dept-title">Отдел A</div>
                            <div class="node head">Начальник отдела</div>

                            <div class="units">
                                <!-- Отделение 1 -->
                                <div class="unit">
                                    <div class="unit-title">Отделение 1</div>
                                    <div class="node head">Начальник отделения</div>
                                    <div class="employees">
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                    </div>
                                </div>

                                <!-- Отделение 2 -->
                                <div class="unit">
                                    <div class="unit-title">Отделение 2</div>
                                    <div class="node head">Начальник отделения</div>
                                    <div class="employees">
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ОТДЕЛ B -->
                        <div class="department">
                            <div class="node dept-title">Отдел B</div>
                            <div class="node head">Начальник отдела</div>

                            <div class="units">
                                <div class="unit">
                                    <div class="unit-title">Отделение 1</div>
                                    <div class="node head">Начальник отделения</div>
                                    <div class="employees">
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                    </div>
                                </div>

                                <div class="unit">
                                    <div class="unit-title">Отделение 2</div>
                                    <div class="node head">Начальник отделения</div>
                                    <div class="employees">
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                    </div>
                                </div>
                                <div class="unit">
                                    <div class="unit-title">Отделение 3</div>
                                    <div class="node head">Начальник отделения</div>
                                    <div class="employees">
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                    </div>
                                </div>
                                <div class="unit">
                                    <div class="unit-title">Отделение 4</div>
                                    <div class="node head">Начальник отделения</div>
                                    <div class="employees">
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                    </div>
                                </div>
                                <div class="unit">
                                    <div class="unit-title">Отделение 5</div>
                                    <div class="node head">Начальник отделения</div>
                                    <div class="employees">
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                        <div class="employee">Сотрудник</div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="employee">Сотрудник</div>
                        <div class="employee">Сотрудник</div>
                        <div class="employee">Сотрудник</div>


                        <div class="unit">
                            <div class="unit-title">Отделение 5</div>
                            <div class="node head">Начальник отделения</div>
                            <div class="employees">
                                <div class="employee">Сотрудник</div>
                                <div class="employee">Сотрудник</div>
                                <div class="employee">Сотрудник</div>
                            </div>
                        </div>
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
