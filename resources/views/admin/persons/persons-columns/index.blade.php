@extends('layouts.main')

@section('header-title')
    Колонки в персонах
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif


    <div class="w-full mx-auto p-4 sm:p-6">
        <!-- Кнопка создания -->
        <div class="flex justify-end mb-5">
            <a href="{{ route('persons-columns.create', ['back_url' => url()->full()]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/85 transition-colors shadow-sm hover:shadow active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Создать колонки
            </a>
        </div>

        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-xl shadow border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[320px] text-sm">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-[#e7e1e1]">Колонка</th>
                            <th class="px-2 py-2.5 text-right text-xs font-semibold text-[#e7e1e1] w-28">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D0CFCF]">
                        @foreach ($columns as $col)
                            <tr class="hover:bg-[#A60644]/6 transition-colors">
                                <td class="px-3 py-2.5 text-[#111] text-sm font-medium">
                                    {{ $col["name"] }}
                                </td>
                                <td class="px-2 py-2.5">
                                    <div class="flex gap-1 justify-end">
                                        <a href="{{ route('persons-columns.edit', ['id' => $col["name"], 'back_url' => url()->full()]) }}"
                                            class="inline-flex items-center px-3 py-1.5 bg-[#A60644]/90 text-white text-xs font-medium rounded hover:bg-[#A60644] transition-colors">
                                            ✎ Ред.
                                        </a>
                                        <form
                                            action="{{ route('persons-columns.delete', ['id' => $col["name"], 'back_url' => url()->full()]) }}"
                                            method="POST" class="inline" onsubmit="return confirm('Удалить колонку?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-white text-xs font-medium rounded hover:bg-gray-900 transition-colors">
                                                × Удал.
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection