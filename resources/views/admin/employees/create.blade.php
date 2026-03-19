@extends('layouts.main')

@section('header-title')
    Создание сотрудника
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif



    <div class="w-full p-6 mx-auto">
   <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('employees.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Создание сотрудника</h1>
            <p class="text-[#565A5B] mt-1">Создание данных сотрудника</p>
        </div>


        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
 <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div class="grid grid-cols-4 gap-4">
        @foreach ($columns as $column)
            @php
                $name = $column['name'];
                $type = $column['type'];
                $value = old($name) 
                    ?? ($column['default'] !== null ? $column['default'] : '');

                // Определяем input type
                $inputType = match(true) {
                    str_contains($type, 'int') => 'number',
                    str_contains($type, 'decimal') => 'number',
                    str_contains($type, 'longtext') => 'textarea',
                    str_contains($type, 'text') => 'text',
                    str_contains($type, 'date') => 'date',
                    str_contains($type, 'varchar') => 'file',
                      default => 'text',
                };

                $isTextarea = in_array($inputType, ['textarea']);
                $step = str_contains($type, 'decimal') ? 'step=0.01' : null;
            @endphp

            <div class="flex flex-col">
                <label for="{{ $name }}" class="mb-1 text-sm font-medium text-[#060606]">
                    {{ $name }}
                </label>

                @if ($isTextarea)
                    <textarea
                        id="{{ $name }}"
                        name="{{ $name }}"
                        rows="3"
                        placeholder="Введите {{ $name }}"
                        class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]"
                    >{{ $value }}</textarea>
                @else
                    <input
                        id="{{ $name }}"
                        name="{{ $name }}"
                        type="{{ $inputType }}"
                        value="{{ $inputType !== 'file' ? $value : '' }}"
                        placeholder="Введите {{ $name }}"
                        {{ $step }}
                        {{ !$column["nullable"] ? "required" : "" }}
                        class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]"
                    >
                @endif
            </div>
        @endforeach
    </div>

    <hr>


      <div class="grid grid-cols-4 gap-4">
            
                        <!-- Логин -->
                        <div>
                            <label for="login" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Логин *
                            </label>
                            <input type="text" name="login" id="login" placeholder="Введите логин"
                                value="{{ old('login') }}" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">

                        </div>


                        <!-- Пароль -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Пароль *
                            </label>
                            <input type="password" name="password" id="password" required placeholder="Введите пароль"
                                value=""
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">

                        </div>

                        <!-- Роль -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Роль
                            </label>
                            <select name="role" id="role"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">
                                        {{ $role->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                   


                    <!-- Рабочий статус -->
                    <div>
                        <label for="work_status" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Рабочий статус *
                        </label>
                        <select name="work_status" id="work_status" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}">
                                    {{ $status->description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
      </div>



     

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="px-4 py-2 bg-[#A60644] text-white text-sm rounded-lg hover:bg-[#A60644]/85">
                            Создать сотрудника
                        </button>
                    </div>
                </form>
            </div>
        </div>
@endsection


{{--  телефоны и почта --}}
<script>
    function addEmail() {
        const wrapper = document.getElementById('emails-wrapper');
        wrapper.appendChild(createRow('email', 'emails[]', 'Введите почту'));
    }

    function addPhone() {
        const wrapper = document.getElementById('phones-wrapper');
        wrapper.appendChild(createRow('tel', 'phones[]', 'Введите телефон'));
    }

    function createRow(type, name, placeholder) {
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center';

        div.innerHTML = `
        <input type="${type}" name="${name}" placeholder="${placeholder}"
            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg">
        <button type="button" onclick="removeRow(this)"
            class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
            ✕
        </button>
    `;

        return div;
    }

    function removeRow(button) {
        button.parentElement.remove();
    }
</script>

