@extends('layouts.main')

@section('header-title')
    Редактирование должности
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif


    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ route('positions.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку должностей
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Редактирование должности</h1>
            <p class="text-[#565A5B] mt-1">Редактирование должности: "{{ $position->name }}"</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('positions.update', $position->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Название должности -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название должности
                        </label>
                        <input type="text" name="name" id="name" placeholder="Введите должность"
                            value="{{ old('name', $position->name) }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    {{-- тип должности --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Тип должности
                        </label>

                        {{-- visible input --}}
                        <input type="text" id="position_type_search" placeholder="Выберите тип должности"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
               focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
               outline-none transition-colors text-[#060606]"
                            autocomplete="off" value="{{ old('position_type_name') }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="position_type_id" id="position_type_id"
                            value="{{ old('position_type_id') }}">

                        {{-- dropdown --}}
                        <ul id="position_type_list"
                            class="relative z-20 mt-1 w-full bg-white border border-[#BFBFBF]
               rounded-lg max-h-72 overflow-auto hidden">

                            {{-- не выбирать --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="" data-static="true">
                                Не выбирать
                            </li>

                            @foreach ($types as $type)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $type->id }}"
                                    data-name="{{ $type->name }}">
                                    {{ $type->name }}
                                    <span class="text-gray-400">(ID: {{ $type->id }})</span>
                                </li>
                            @endforeach

                        </ul>
                    </div>

                    <!-- Кнопка отправки -->
                    <div class="flex justify-end pt-6">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Обновить должность
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection




<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('position_type_search');
        const hidden = document.getElementById('position_type_id');
        const list = document.getElementById('position_type_list');

        input.addEventListener('focus', () => {
            list.classList.remove('hidden');
        });

        input.addEventListener('input', () => {
            const q = input.value.toLowerCase();

            [...list.children].forEach(li => {
                if (li.dataset.static) return;
                li.classList.toggle(
                    'hidden',
                    !li.dataset.name.toLowerCase().includes(q)
                );
            });

            list.classList.remove('hidden');
        });

        list.addEventListener('click', (e) => {
            const item = e.target.closest('li');
            if (!item) return;

            input.value = item.dataset.name || '';
            hidden.value = item.dataset.id || '';

            list.classList.add('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#position_type_search') &&
                !e.target.closest('#position_type_list')) {
                list.classList.add('hidden');
            }
        });
    });
</script>
