@extends('layouts.main')

@section('header-title')
    Задача: {{ $task->title }}
@endsection

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow-lg">
            <!-- Заголовок -->
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-start">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $task->title }}</h1>
                    <button onclick="deleteTask()"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                        🗑️ Удалить задачу
                    </button>
                </div>
            </div>

            <!-- Информация -->
            <div class="px-6 py-4 space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Описание</h3>
                    <p class="mt-1 text-gray-800">{{ $task->description ?: 'Нет описания' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Дата начала</h3>
                        <p class="mt-1 text-gray-800">{{ $task->start_date->format('d.m.Y') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Дата окончания</h3>
                        <p class="mt-1 text-gray-800">
                            {{ $task->end_date ? $task->end_date->format('d.m.Y') : 'Не указана' }}
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Квота</h3>
                        <p class="mt-1 text-gray-800">{{ $task->quota ?: 'Не указана' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Цвет</h3>
                        <div class="mt-1 flex items-center">
                            <div class="w-6 h-6 rounded border" style="background-color: {{ $task->color }}"></div>
                            <span class="ml-2 text-gray-800">{{ $task->color }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500">Ответственный</h3>
                    <p class="mt-1 text-gray-800">
                        @if($task->employeePosition && $task->employeePosition->employee)
                            <a href="{{ route('employees.show', ['id' => $task->employeePosition->employee->id, 'back_url' => url()->full()]) }}">
                                {{ $task->employeePosition->employee->getFullNameAttribute() }}
                            </a>
                        @else
                            Не назначен
                        @endif
                    </p>
                </div>
            </div>

            {{-- Предупреждение: нет подзадач --}}
            @if($task->subtasks->isEmpty())
                <div class="border-t border-gray-200 px-6 py-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <span class="text-red-500 text-xl flex-shrink-0">⚠️</span>
                            <div>
                                <h4 class="text-sm font-semibold text-red-700 mb-1">Не добавлено ни одной подзадачи</h4>
                                <p class="text-sm text-red-600">
                                    Для корректного построения графиков, расчёта времени выполнения и распределения нагрузки
                                    необходимо добавить <strong>хотя бы одну подзадачу</strong> с указанием временных оценок
                                    (минимальное, среднее, максимальное время).
                                </p>
                                <button onclick="openSubtaskModal()"
                                    class="mt-3 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                    + Добавить подзадачу
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Оценка времени --}}
                <div class="border-t border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Оценка времени</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-sm text-gray-500 mb-1">На 1 выполнение</p>
                            <div class="text-sm text-gray-800">
                                <div>Мин: {{ $task->formatMinutes($task->total_min_time) }}</div>
                                <div>Сред: {{ $task->formatMinutes($task->total_avg_time) }}</div>
                                <div>Макс: {{ $task->formatMinutes($task->total_max_time) }}</div>
                            </div>
                        </div>

                        <div class="bg-indigo-50 rounded-lg p-3">
                            <p class="text-sm text-gray-500 mb-1">На всю квоту</p>
                            <div class="text-sm font-medium text-gray-900">
                                <div>Мин: {{ $task->formatMinutes($task->total_min_time_with_quota) }}</div>
                                <div>Сред: {{ $task->formatMinutes($task->total_avg_time_with_quota) }}</div>
                                <div>Макс: {{ $task->formatMinutes($task->total_max_time_with_quota) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Подзадачи --}}
            <div class="border-t border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-800">
                        Подзадачи ({{ $task->subtasks->count() }})
                    </h3>
                    <button onclick="openSubtaskModal()"
                        class="px-3 py-1 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        + Добавить подзадачу
                    </button>
                </div>

                <div id="subtasksList" class="space-y-2">
                    @forelse($task->subtasks as $subtask)
                        <div class="bg-gray-50 rounded-lg p-3 flex justify-between items-center">
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">{{ $subtask->title }}</p>
                                <p class="text-xs text-gray-500">
                                    Оценка: мин {{ $subtask->min_time_minutes }} мин |
                                    сред {{ $subtask->avg_time_minutes }} мин |
                                    макс {{ $subtask->max_time_minutes }} мин
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="editSubtask({{ $subtask->id }})" class="text-indigo-600 hover:text-indigo-800">
                                    ✏️
                                </button>
                                <button onclick="deleteSubtask({{ $subtask->id }})" class="text-red-600 hover:text-red-800">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Нет подзадач</p>
                    @endforelse
                </div>
            </div>

            {{-- Назначенные сотрудники --}}
            <div class="border-t border-gray-200 px-6 py-4" id="assign">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-800">
                        Назначенные сотрудники ({{ $task->taskAssignments->count() }})
                    </h3>
                </div>

                @if($task->taskAssignments->isEmpty())
                    <p class="text-sm text-gray-400">Нет назначенных сотрудников</p>
                @else
                    <div class="space-y-2">
                        @foreach($task->taskAssignments as $a)
                            @php
                                $ep = $a->employee->person;
                                $ename = $ep ? trim($ep->фамилия.' '.mb_substr($ep->имя,0,1).'.') : '#'.$a->employee_id;
                                $pct = $a->quota ? round($a->completed_count/$a->quota*100) : 0;
                            @endphp
                            <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('employees.show', $a->employee_id) }}" class="font-medium text-indigo-600 hover:underline">
                                        {{ $ename }}
                                    </a>
                                    <span class="text-xs text-gray-500">Приоритет: {{ $a->priority }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm">{{ $a->completed_count }}/{{ $a->quota }}</span>
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $pct>=100?'bg-emerald-500':'bg-indigo-500' }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium {{ $pct>=100?'text-emerald-600':'text-indigo-600' }}">{{ $pct }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Назад -->
            <div class="border-t border-gray-200 px-6 py-4">
                <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-800 transition">
                    ← Назад
                </a>
            </div>
        </div>
    </div>

    {{-- Модальное окно для подзадач --}}
    <div id="subtaskModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-gray-900/50"></div>
        <div class="flex items-center justify-center min-h-full p-4">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4" id="subtaskModalTitle">Добавить подзадачу</h3>
                <form id="subtaskForm">
                    @csrf
                    <input type="hidden" id="subtask_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                        <input type="text" id="subtask_title" required
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Мин (мин) *</label>
                            <input type="number" id="subtask_min" required min="0"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Сред (мин) *</label>
                            <input type="number" id="subtask_avg" required min="0"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Макс (мин) *</label>
                            <input type="number" id="subtask_max" required min="0"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeSubtaskModal()"
                            class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Отмена
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const taskId = {{ $task->id }};
        const csrfToken = '{{ csrf_token() }}';

        function openSubtaskModal(subtaskId = null) {
            const modal = document.getElementById('subtaskModal');
            const form = document.getElementById('subtaskForm');
            const title = document.getElementById('subtaskModalTitle');

            form.reset();
            document.getElementById('subtask_id').value = '';

            if (subtaskId) {
                title.textContent = 'Редактировать подзадачу';
                fetch(`/calendar/tasks/${taskId}/subtasks/${subtaskId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('subtask_id').value = data.subtask.id;
                            document.getElementById('subtask_title').value = data.subtask.title;
                            document.getElementById('subtask_min').value = data.subtask.min_time_minutes;
                            document.getElementById('subtask_avg').value = data.subtask.avg_time_minutes;
                            document.getElementById('subtask_max').value = data.subtask.max_time_minutes;
                        }
                    });
            } else {
                title.textContent = 'Добавить подзадачу';
            }

            modal.classList.remove('hidden');
        }

        function closeSubtaskModal() {
            document.getElementById('subtaskModal').classList.add('hidden');
        }

        document.getElementById('subtaskForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const minVal = parseInt(document.getElementById('subtask_min').value);
            const avgVal = parseInt(document.getElementById('subtask_avg').value);
            const maxVal = parseInt(document.getElementById('subtask_max').value);
            const titleVal = document.getElementById('subtask_title').value.trim();

            if (!titleVal) return alert('Введите название');
            if (minVal > avgVal) return alert('Минимальное время не может быть больше среднего');
            if (avgVal > maxVal) return alert('Среднее время не может быть больше максимального');

            const subtaskId = document.getElementById('subtask_id').value;
            const url = subtaskId
                ? `/calendar/tasks/${taskId}/subtasks/${subtaskId}`
                : `/calendar/tasks/${taskId}/subtasks`;
            const method = subtaskId ? 'PUT' : 'POST';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    title: titleVal,
                    min_time_minutes: minVal,
                    avg_time_minutes: avgVal,
                    max_time_minutes: maxVal,
                    _token: csrfToken,
                    _method: method,
                }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
                else alert('Ошибка: ' + (data.message || ''));
            });
        });

        function editSubtask(id) { openSubtaskModal(id); }

        function deleteSubtask(id) {
            if (!confirm('Удалить подзадачу?')) return;
            fetch(`/calendar/tasks/${taskId}/subtasks/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); });
        }

        function deleteTask() {
            if (!confirm('Удалить задачу со всеми подзадачами и файлами?')) return;
            fetch(`/calendar/tasks/${taskId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => { if (data.success) window.location.href = '{{ route("calendar.index") }}'; });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('subtaskModal');
            const overlay = modal?.querySelector('.bg-gray-900\\/50');
            if (overlay) overlay.addEventListener('click', closeSubtaskModal);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal?.classList.contains('hidden')) closeSubtaskModal();
            });
        });
    </script>
@endsection