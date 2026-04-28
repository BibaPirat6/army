@extends('layouts.main')

@section('header-title')
    Календарь
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Календарный график задач</h2>
        <div id="calendar"></div>
    </div>

    {{-- Модальное окно --}}
    <div id="taskModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
        {{-- Оверлей --}}
        <div class="fixed inset-0 bg-gray-900/50 transition-opacity" data-modal-overlay></div>

        {{-- Контейнер модалки --}}
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">Новая задача</h3>
                    <button type="button" data-modal-close
                        class="text-gray-400 hover:text-gray-600 transition p-1 rounded-full hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="taskForm" method="post" action="{{ route("calendar.tasks.store") }}">
                    @csrf
                    <input type="hidden" id="task_id" name="id">

                    {{-- Название --}}
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                        <input type="text" id="title" name="title" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Описание --}}
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                        <textarea id="description" name="description" rows="2"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    {{-- Цвет + Квота --}}
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Цвет</label>
                            <input type="color" id="color" name="color" value="#3788d8"
                                class="h-10 w-full rounded-lg border-gray-300 cursor-pointer">
                        </div>
                        <div>
                            <label for="quota" class="block text-sm font-medium text-gray-700 mb-1">Квота</label>
                            <input type="number" id="quota" name="quota" min="1"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Не задана" value="1">
                        </div>
                    </div>

                    {{-- Подразделение --}}
                    <div class="mb-4">
                        <label for="commissariat_id" class="block text-sm font-medium text-gray-700 mb-1">Подразделение</label>
                        <select id="commissariat_id" name="commissariat_id"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Выберите комиссариат</option>
                            @foreach($commissariats as $com)
                                <option value="{{ $com->id }}">{{ $com->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Даты --}}
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Дата начала *</label>
                            <input type="date" id="start_date" name="start_date" required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Дата окончания</label>
                            <input type="date" id="end_date" name="end_date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Кнопки --}}
                    <div class="flex justify-end gap-3">
                        <button type="button" data-modal-close
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Отмена
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

{{-- ===== СКРИПТЫ МОДАЛКИ ДОЛЖНЫ БЫТЬ В СЕКЦИИ ===== --}}
@push('scripts')
<script>
    // Эти функции должны быть глобальными, чтобы FullCalendar мог их вызвать
    window.openModal = function () {
        document.getElementById('taskModal').classList.remove('hidden');
    };

    window.closeModal = function () {
        document.getElementById('taskModal').classList.add('hidden');
    };

    window.resetForm = function () {
        document.getElementById('taskForm').reset();
        document.getElementById('task_id').value = '';
        document.getElementById('modalTitle').textContent = 'Новая задача';
    };

    // Закрытие по кнопкам
    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            window.closeModal();
        });
    });

    // Закрытие по оверлею
    const overlay = document.querySelector('[data-modal-overlay]');
    if (overlay) {
        overlay.addEventListener('click', function () {
            window.closeModal();
        });
    }

    // Закрытие по Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            window.closeModal();
        }
    });
</script>
@endpush