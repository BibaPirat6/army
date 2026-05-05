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
                            <a href="{{ route("employees.show",[
                                "id" => $task->employeePosition->employee->id,
                                "back_url"=>url()->full()                           
                            ]) }}">{{ $task->employeePosition->employee->getFullNameAttribute() }}</a>
                        @else
                            Не назначен
                        @endif
                    </p>
                </div>
            </div>

            <!-- Подзадачи -->
            <div class="border-t border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-800">Подзадачи</h3>
                    <button onclick="openSubtaskModal()"
                        class="px-3 py-1 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        + Добавить подзадачу
                    </button>
                </div>

                <div id="subtasksList" class="space-y-2">
                    @foreach($task->subtasks as $subtask)
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
                    @endforeach
                </div>
            </div>

            <!-- Кнопка назад -->
            <div class="border-t border-gray-200 px-6 py-4">
                <a href="{{ route('calendar.index') }}" class="text-indigo-600 hover:text-indigo-800 transition">
                    ← Назад к календарю
                </a>
            </div>
        </div>
    </div>

    <!-- Модальное окно для подзадач -->
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

        // === ПОДЗАДАЧИ ===
        function openSubtaskModal(subtaskId = null) {
            const modal = document.getElementById('subtaskModal');
            const form = document.getElementById('subtaskForm');
            const title = document.getElementById('subtaskModalTitle');

            form.reset();
            document.getElementById('subtask_id').value = '';

            if (subtaskId) {
                title.textContent = 'Редактировать подзадачу';
                // Загрузка данных подзадачи для редактирования
                fetch(`/calendar/tasks/${taskId}/subtasks/${subtaskId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById('subtask_id').value = data.subtask.id;
                            document.getElementById('subtask_title').value = data.subtask.title;
                            document.getElementById('subtask_min').value = data.subtask.min_time_minutes;
                            document.getElementById('subtask_avg').value = data.subtask.avg_time_minutes;
                            document.getElementById('subtask_max').value = data.subtask.max_time_minutes;
                        } else {
                            alert('Ошибка загрузки данных подзадачи');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        alert('Ошибка загрузки данных подзадачи');
                    });
            } else {
                title.textContent = 'Добавить подзадачу';
            }

            modal.classList.remove('hidden');
        }

        function closeSubtaskModal() {
            document.getElementById('subtaskModal').classList.add('hidden');
        }

        // 
        // Фронтенд валидация для полей времени
        function validateTimeFields(min, avg, max) {
            if (min > avg) {
                return {
                    valid: false,
                    message: 'Минимальное время не может быть больше среднего',
                    field: 'min'
                };
            }
            if (avg > max) {
                return {
                    valid: false,
                    message: 'Среднее время не может быть больше максимального',
                    field: 'avg'
                };
            }
            if (min > max) {
                return {
                    valid: false,
                    message: 'Минимальное время не может быть больше максимального',
                    field: 'min'
                };
            }
            return { valid: true };
        }

        document.getElementById('subtaskForm')?.addEventListener('submit', function (e) {
            e.preventDefault();

            // Получаем значения
            const minVal = parseInt(document.getElementById('subtask_min').value);
            const avgVal = parseInt(document.getElementById('subtask_avg').value);
            const maxVal = parseInt(document.getElementById('subtask_max').value);
            const title = document.getElementById('subtask_title').value.trim();

            // Проверка названия
            if (!title) {
                alert('Пожалуйста, введите название подзадачи');
                return;
            }

            // Фронтенд валидация времени
            const validation = validateTimeFields(minVal, avgVal, maxVal);
            if (!validation.valid) {
                alert(validation.message);

                // Подсвечиваем поле с ошибкой
                const fieldMap = {
                    'min': 'subtask_min',
                    'avg': 'subtask_avg',
                    'max': 'subtask_max'
                };
                const fieldId = fieldMap[validation.field];
                if (fieldId) {
                    const field = document.getElementById(fieldId);
                    field.classList.add('border-red-500');
                    setTimeout(() => {
                        field.classList.remove('border-red-500');
                    }, 3000);
                    field.focus();
                }
                return;
            }

            const subtaskId = document.getElementById('subtask_id').value;
            const url = subtaskId
                ? `/calendar/tasks/${taskId}/subtasks/${subtaskId}`
                : `/calendar/tasks/${taskId}/subtasks`;
            const method = subtaskId ? 'PUT' : 'POST';

            const data = {
                title: title,
                min_time_minutes: minVal,
                avg_time_minutes: avgVal,
                max_time_minutes: maxVal,
                _token: csrfToken,
                _method: method,
            };

            // Добавляем индикатор загрузки
            const submitBtn = document.querySelector('#subtaskForm button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Сохранение...';
            submitBtn.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(data),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                        // Если ошибка связана с конкретным полем, подсвечиваем его
                        if (data.field) {
                            const fieldMap = {
                                'min_time_minutes': 'subtask_min',
                                'avg_time_minutes': 'subtask_avg',
                                'max_time_minutes': 'subtask_max'
                            };
                            const fieldId = fieldMap[data.field];
                            if (fieldId) {
                                const field = document.getElementById(fieldId);
                                field.classList.add('border-red-500');
                                setTimeout(() => {
                                    field.classList.remove('border-red-500');
                                }, 3000);
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Ошибка соединения: ' + error.message);
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        });


        function editSubtask(subtaskId) {
            openSubtaskModal(subtaskId);
        }

        function deleteSubtask(subtaskId) {
            if (confirm('Удалить подзадачу?')) {
                fetch(`/calendar/tasks/${taskId}/subtasks/${subtaskId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Ошибка удаления');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        alert('Ошибка соединения');
                    });
            }
        }

        function deleteTask() {
            if (confirm('⚠️ Внимание! Задача будет удалена вместе со всеми подзадачами и файлами. Отменить действие будет невозможно. Продолжить?')) {
                fetch(`/calendar/tasks/${taskId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '{{ route("calendar.index") }}';
                        } else {
                            alert('Ошибка удаления: ' + (data.message || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        alert('Ошибка соединения');
                    });
            }
        }

        // Закрытие модалки по клику на оверлей и Escape
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('subtaskModal');
            const overlay = modal?.querySelector('.fixed.bg-gray-900\\/50');

            if (overlay) {
                overlay.addEventListener('click', closeSubtaskModal);
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal?.classList.contains('hidden')) {
                    closeSubtaskModal();
                }
            });
        });
    </script>
@endsection