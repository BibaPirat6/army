@extends('layouts.main')

@section('header-title')
    Задача: {{ $task->title }}
@endsection

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow-lg">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-start">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $task->title }}</h1>
                    <button onclick="openTaskModal({{ $task->id }})"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        ✏️ Редактировать
                    </button>
                </div>
            </div>

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
                        <p class="mt-1 text-gray-800">{{ $task->end_date?->format('d.m.Y') ?: 'Не указана' }}</p>
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

                {{-- Подзадачи --}}
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-800">Подзадачи ({{ $task->subtasks->count() }})</h3>
                        <button onclick="openSubtaskModal()"
                            class="px-3 py-1 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                            + Добавить
                        </button>
                    </div>
                    <div class="space-y-2" id="subtasks-list">
                        @forelse($task->subtasks as $s)
                            <div class="bg-gray-50 rounded-lg p-3 flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $s->title }}</p>
                                    <p class="text-xs text-gray-500">
                                        мин {{ $s->min_time_minutes }} | сред {{ $s->avg_time_minutes }} | макс {{ $s->max_time_minutes }} мин
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="editSubtask({{ $s->id }})"
                                        class="text-indigo-600 hover:text-indigo-800">✏️</button>
                                    <button onclick="deleteSubtask({{ $s->id }})"
                                        class="text-red-600 hover:text-red-800">🗑️</button>
                                </div>
                            </div>
                        @empty
                            <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-400">
                                Нет подзадач
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('calendar.index') }}" class="text-indigo-600 hover:text-indigo-800 transition">
                        ← Назад к календарю
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('admin.calendar.tasks.partials.form-modal')
    @include('admin.calendar.tasks.partials.subtask-modal')
@endsection

@push('scripts')
<script>
const taskId = {{ $task->id }};
const csrfToken = '{{ csrf_token() }}';

// ===== МОДАЛКА ЗАДАЧИ =====
function openTaskModal(id = null) {
    const modal = document.getElementById('taskModal');
    const form = document.getElementById('taskForm');
    form.reset();
    document.getElementById('task_id').value = '';
    document.getElementById('modalTitle').textContent = id ? 'Редактирование задачи' : 'Новая задача';
    
    if (id) {
        fetch(`/calendar/tasks/${id}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('task_id').value = data.id;
                document.getElementById('title').value = data.title;
                document.getElementById('description').value = data.description || '';
                document.getElementById('color').value = data.color;
                document.getElementById('quota').value = data.quota;
                document.getElementById('start_date').value = data.start_date;
                document.getElementById('end_date').value = data.end_date;
                if (data.employee_position_id) {
                    document.getElementById('employee_position_id').value = data.employee_position_id;
                }
            });
    }
    modal.classList.remove('hidden');
}

function closeTaskModal() {
    document.getElementById('taskModal').classList.add('hidden');
}

document.getElementById('taskForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('task_id').value;
    const url = id ? `/calendar/tasks/${id}` : '/calendar/tasks';
    
    const formData = new FormData(this);
    if (id) formData.append('_method', 'PUT');
    
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ошибка сохранения');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Ошибка соединения');
    });
});

// ===== МОДАЛКА ПОДЗАДАЧИ =====
function openSubtaskModal(id = null) {
    document.getElementById('subtaskForm').reset();
    document.getElementById('subtask_id').value = '';
    document.getElementById('subtaskModalTitle').textContent = id ? 'Редактировать подзадачу' : 'Добавить подзадачу';
    
    if (id) {
        fetch(`/calendar/tasks/${taskId}/subtasks/${id}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('subtask_id').value = data.id;
                document.getElementById('subtask_title').value = data.title;
                document.getElementById('subtask_min').value = data.min_time_minutes;
                document.getElementById('subtask_avg').value = data.avg_time_minutes;
                document.getElementById('subtask_max').value = data.max_time_minutes;
            });
    }
    document.getElementById('subtaskModal').classList.remove('hidden');
}

function closeSubtaskModal() {
    document.getElementById('subtaskModal').classList.add('hidden');
}

document.getElementById('subtaskForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('subtask_id').value;
    const url = id ? `/calendar/tasks/${taskId}/subtasks/${id}` : `/calendar/tasks/${taskId}/subtasks`;
    const method = id ? 'PUT' : 'POST';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            title: document.getElementById('subtask_title').value,
            min_time_minutes: parseInt(document.getElementById('subtask_min').value),
            avg_time_minutes: parseInt(document.getElementById('subtask_avg').value),
            max_time_minutes: parseInt(document.getElementById('subtask_max').value),
            _method: method
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
});

function editSubtask(id) {
    openSubtaskModal(id);
}

function deleteSubtask(id) {
    if (!confirm('Удалить подзадачу?')) return;
    fetch(`/calendar/tasks/${taskId}/subtasks/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    }).then(() => location.reload());
}

// Закрытие по ESC
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeTaskModal();
        closeSubtaskModal();
    }
});
</script>
@endpush