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
                    <div class="flex gap-2">
                        <button onclick="openEditTaskModal()"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                            ✏️ Редактировать
                        </button>
                        <button onclick="deleteTask()"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                            🗑️ Удалить
                        </button>
                    </div>
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
                            {{ $task->end_date ? $task->end_date->format('d.m.Y') : 'Не указана' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Квота</h3>
                        <p class="mt-1 text-gray-800">{{ $task->quota ?: 'Не указана' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Цвет</h3>
                        <div class="mt-1 flex items-center">
                            <div class="w-6 h-6 rounded border" style="background-color:{{ $task->color }}"></div><span
                                class="ml-2 text-gray-800">{{ $task->color }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500">Ответственный</h3>
                    <p class="mt-1 text-gray-800">
                        @if($task->employeePosition?->employee)
                            <a
                                href="{{ route('employees.show', ['id' => $task->employeePosition->employee->id, 'back_url' => url()->full()]) }}">
                                {{ $task->employeePosition->employee->getFullNameAttribute() }}
                            </a>
                        @else Не назначен @endif
                    </p>
                </div>

                {{-- Файлы --}}
                @if($task->files->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Файлы ({{ $task->files->count() }})</h3>
                        <div class="space-y-1">
                            @foreach($task->files as $file)
                                <div class="flex items-center justify-between text-sm">
                                    <a href="{{ asset('storage/' . $file->path) }}" target="_blank"
                                        class="text-indigo-600 hover:underline">📎 {{ $file->original_name }}</a>
                                    <span class="text-xs text-gray-400">{{ round($file->size / 1024, 1) }} КБ</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Три блока времени --}}
            <div class="grid grid-cols-3 gap-4 mt-4">
                {{-- Блок 1: На 1 итерацию --}}
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <p class="text-xs text-blue-500 font-medium mb-1">⏱ На 1 выполнение</p>
                    <div class="space-y-1 text-sm text-gray-700">
                        <div class="flex justify-between"><span>Мин:</span> <span
                                class="font-medium">{{ $task->formatMinutes($task->total_min_time) }}</span></div>
                        <div class="flex justify-between"><span>Сред:</span> <span
                                class="font-medium">{{ $task->formatMinutes($task->total_avg_time) }}</span></div>
                        <div class="flex justify-between"><span>Макс:</span> <span
                                class="font-medium">{{ $task->formatMinutes($task->total_max_time) }}</span></div>
                    </div>
                </div>

                {{-- Блок 2: На всю квоту --}}
                <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                    <p class="text-xs text-indigo-500 font-medium mb-1">📦 На всю квоту ({{ $task->quota ?: '∞' }})</p>
                    <div class="space-y-1 text-sm text-gray-700">
                        <div class="flex justify-between"><span>Мин:</span> <span
                                class="font-medium">{{ $task->formatMinutes($task->total_min_time_with_quota) }}</span>
                        </div>
                        <div class="flex justify-between"><span>Сред:</span> <span
                                class="font-medium">{{ $task->formatMinutes($task->total_avg_time_with_quota) }}</span>
                        </div>
                        <div class="flex justify-between"><span>Макс:</span> <span
                                class="font-medium">{{ $task->formatMinutes($task->total_max_time_with_quota) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Блок 3: Выполнено сотрудниками --}}
                <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-100">
                    <p class="text-xs text-emerald-500 font-medium mb-1">✅ Выполнено сотрудниками</p>
                    @php
                        $totalCompleted = $task->taskAssignments->sum('completed_count');
                        $totalQuotaAssigned = $task->taskAssignments->sum('quota');
                        $avgTime = $task->total_avg_time;
                    @endphp
                    <div class="space-y-1 text-sm text-gray-700">
                        <div class="flex justify-between"><span>Итераций:</span> <span
                                class="font-medium">{{ $totalCompleted }} / {{ $totalQuotaAssigned }}</span></div>
                        <div class="flex justify-between"><span>Времени (сред):</span> <span
                                class="font-medium">{{ $task->formatMinutes($totalCompleted * $avgTime) }}</span></div>
                        <div class="flex justify-between"><span>Прогресс:</span> <span
                                class="font-medium">{{ $totalQuotaAssigned > 0 ? round(($totalCompleted / $totalQuotaAssigned) * 100) : 0 }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Подзадачи --}}
            <div class="border-t border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-800">Подзадачи ({{ $task->subtasks->count() }})</h3>
                    <button onclick="openSubtaskModal()"
                        class="px-3 py-1 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">+
                        Добавить</button>
                </div>
                <div class="space-y-2">
                    @forelse($task->subtasks as $s)
                        <div class="bg-gray-50 rounded-lg p-3 flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-800">{{ $s->title }}</p>
                                <p class="text-xs text-gray-500">мин {{ $s->min_time_minutes }} | сред
                                    {{ $s->avg_time_minutes }} | макс {{ $s->max_time_minutes }} мин</p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="editSubtask({{ $s->id }})"
                                    class="text-indigo-600 hover:text-indigo-800">✏️</button>
                                <button onclick="deleteSubtask({{ $s->id }})"
                                    class="text-red-600 hover:text-red-800">🗑️</button>
                            </div>
                        </div>
                    @empty
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-600">
                            ⚠️ Нет подзадач. <button onclick="openSubtaskModal()"
                                class="underline font-medium">Добавить</button>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Назначенные сотрудники --}}
            <div class="border-t border-gray-200 px-6 py-4" id="assign">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-800">Назначенные сотрудники
                        ({{ $task->taskAssignments->count() }})</h3>
                    <a href="{{ route('calendar.matrix.index', $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1) }}"
                        class="px-3 py-1 text-sm text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">
                        + Назначить
                    </a>
                </div>
                @forelse($task->taskAssignments as $a)
                    @php
                        $ep = $a->employee->person;
                        $ename = $ep ? trim($ep->фамилия . ' ' . mb_substr($ep->имя, 0, 1) . '.') : '#' . $a->employee_id;
                        $pct = $a->quota ? round($a->completed_count / $a->quota * 100) : 0;
                    @endphp
                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('employees.show', $a->employee_id) }}"
                                class="font-medium text-indigo-600 hover:underline">{{ $ename }}</a>
                            <span class="text-xs text-gray-500">P{{ $a->priority }}</span>
                            <span class="text-xs text-gray-400">{{ $a->start_date?->format('d.m.Y') ?? '—' }} —
                                {{ $a->end_date?->format('d.m.Y') ?? '—' }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm">{{ $a->completed_count }}/{{ $a->quota }}</span>
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}"
                                    style="width:{{ $pct }}%"></div>
                            </div>
                            <a href="{{ route('calendar.assignments.edit', [$task->id, $a->id]) }}"
                                class="text-xs text-indigo-600 hover:bg-indigo-50 rounded px-2 py-1">✏️</a>
                            <form action="{{ route('calendar.assignments.destroy', [$task->id, $a->id]) }}" method="POST"
                                onsubmit="return confirm('Удалить?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700">🗑️</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Нет назначений</p>
                @endforelse
            </div>

            <div class="border-t border-gray-200 px-6 py-4">
                <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-800 transition">← Назад</a>
            </div>
        </div>
    </div>

    {{-- Модалка: Редактирование задачи --}}
    <div id="editTaskModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-gray-900/50" onclick="closeEditTaskModal()"></div>
        <div class="flex items-center justify-center min-h-full p-4">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Редактировать задачу</h3>
                <form id="editTaskForm" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-1">Название *</label><input
                            type="text" name="title" id="edit_title" value="{{ $task->title }}" required
                            class="w-full rounded-lg border-gray-300"></div>
                    <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-1">Описание</label><textarea
                            name="description" id="edit_description" rows="2"
                            class="w-full rounded-lg border-gray-300">{{ $task->description }}</textarea></div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Цвет</label><input type="color"
                                name="color" id="edit_color" value="{{ $task->color }}"
                                class="h-10 w-full rounded-lg border-gray-300 cursor-pointer"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Квота</label><input type="number"
                                name="quota" id="edit_quota" value="{{ $task->quota }}" min="1"
                                class="w-full rounded-lg border-gray-300"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Дата начала</label><input
                                type="date" name="start_date" id="edit_start_date"
                                value="{{ $task->start_date->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300">
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания</label><input
                                type="date" name="end_date" id="edit_end_date"
                                value="{{ $task->end_date?->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Файлы</label>
                        <input type="file" name="files[]" multiple class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeEditTaskModal()"
                            class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg">Отмена</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-lg">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Модалка: Подзадача --}}
    <div id="subtaskModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-gray-900/50"></div>
        <div class="flex items-center justify-center min-h-full p-4">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4" id="subtaskModalTitle">Добавить подзадачу</h3>
                <form id="subtaskForm">
                    @csrf
                    <input type="hidden" id="subtask_id">
                    <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-1">Название *</label><input
                            type="text" id="subtask_title" required class="w-full rounded-lg border-gray-300"></div>
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Мин</label><input type="number"
                                id="subtask_min" required min="0" class="w-full rounded-lg border-gray-300"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Сред</label><input type="number"
                                id="subtask_avg" required min="0" class="w-full rounded-lg border-gray-300"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Макс</label><input type="number"
                                id="subtask_max" required min="0" class="w-full rounded-lg border-gray-300"></div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeSubtaskModal()"
                            class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg">Отмена</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-lg">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const taskId = {{ $task->id }};
        const csrfToken = '{{ csrf_token() }}';

        function openEditTaskModal() { document.getElementById('editTaskModal').classList.remove('hidden'); }
        function closeEditTaskModal() { document.getElementById('editTaskModal').classList.add('hidden'); }

        document.getElementById('editTaskForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('_method', 'PUT');
            fetch(`/calendar/tasks/${taskId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: fd })
                .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
        });

        function openSubtaskModal(id = null) {
            document.getElementById('subtaskForm').reset();
            document.getElementById('subtask_id').value = '';
            document.getElementById('subtaskModalTitle').textContent = id ? 'Редактировать' : 'Добавить';
            if (id) fetch(`/calendar/tasks/${taskId}/subtasks/${id}`).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('subtask_id').value = d.subtask.id;
                    document.getElementById('subtask_title').value = d.subtask.title;
                    document.getElementById('subtask_min').value = d.subtask.min_time_minutes;
                    document.getElementById('subtask_avg').value = d.subtask.avg_time_minutes;
                    document.getElementById('subtask_max').value = d.subtask.max_time_minutes;
                }
            });
            document.getElementById('subtaskModal').classList.remove('hidden');
        }
        function closeSubtaskModal() { document.getElementById('subtaskModal').classList.add('hidden'); }

        document.getElementById('subtaskForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('subtask_id').value;
            const body = {
                title: document.getElementById('subtask_title').value,
                min_time_minutes: +document.getElementById('subtask_min').value,
                avg_time_minutes: +document.getElementById('subtask_avg').value,
                max_time_minutes: +document.getElementById('subtask_max').value,
                _token: csrfToken, _method: id ? 'PUT' : 'POST'
            };
            fetch(id ? `/calendar/tasks/${taskId}/subtasks/${id}` : `/calendar/tasks/${taskId}/subtasks`, {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify(body)
            }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
        });

        function editSubtask(id) { openSubtaskModal(id); }
        function deleteSubtask(id) {
            if (!confirm('Удалить?')) return;
            fetch(`/calendar/tasks/${taskId}/subtasks/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
        }
        function deleteTask() {
            if (!confirm('Удалить задачу?')) return;
            fetch(`/calendar/tasks/${taskId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } }).then(r => r.json()).then(d => { if (d.success) location.href = '{{ route("calendar.index") }}'; });
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeEditTaskModal(); closeSubtaskModal(); } });
    </script>
@endsection