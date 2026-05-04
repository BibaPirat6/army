import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import multiMonthPlugin from '@fullcalendar/multimonth';
import interactionPlugin from '@fullcalendar/interaction';
import ruLocale from '@fullcalendar/core/locales/ru';
import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';
Dropzone.autoDiscover = false;

// vue узлы
import { createApp } from 'vue';
import StructureGraph from './components/StructureGraph.vue';
const vueApp = createApp({});
vueApp.component('structure-graph', StructureGraph);
vueApp.mount('#app');

let calendar = null;
let dropzoneInstance = null;


// === ЗАГРУЗКА СУЩЕСТВУЮЩИХ ФАЙЛОВ ЗАДАЧИ В DROPZONE ===
function loadTaskFiles(taskId) {
    if (!taskId) return;

    fetch(`/calendar/tasks/${taskId}/files`)
        .then(r => r.json())
        .then(files => {
            console.log('Загружены файлы задачи:', files);

            if (dropzoneInstance && files.length > 0) {
                files.forEach(file => {
                    // Создаём мок-файл в Dropzone
                    const mockFile = {
                        id: file.id,
                        name: file.original_name,
                        size: file.size,
                        accepted: true,
                        status: Dropzone.ADDED,
                        url: file.url,
                        existingFileId: file.id, // помечаем как существующий
                    };

                    // Добавляем файл в Dropzone
                    dropzoneInstance.emit('addedfile', mockFile);

                    // Если есть превью (изображение) — показываем
                    if (file.mime_type && file.mime_type.startsWith('image/')) {
                        dropzoneInstance.emit('thumbnail', mockFile, file.url);
                    }

                    dropzoneInstance.emit('complete', mockFile);
                    dropzoneInstance.files.push(mockFile);

                    // Устанавливаем data-атрибут с ID существующего файла
                    const previewElement = mockFile.previewElement;
                    if (previewElement) {
                        previewElement.dataset.existingFileId = file.id;
                    }
                });
            }
        })
        .catch(err => console.error('Ошибка загрузки файлов задачи:', err));
}

// === ФУНКЦИЯ ИНИЦИАЛИЗАЦИИ DROPZONE ===
function initDropzone() {
    const dropzoneEl = document.getElementById('taskFileDropzone');
    if (!dropzoneEl) return;

    if (dropzoneInstance) {
        dropzoneInstance.destroy();
    }

    dropzoneInstance = new Dropzone('#taskFileDropzone', {
        url: '/calendar/tasks',
        method: 'post',
        autoProcessQueue: false,
        maxFilesize: 10,
        maxFiles: 20,
        acceptedFiles: 'image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar',
        addRemoveLinks: true,
        dictDefaultMessage: 'Перетащите файлы сюда или кликните для выбора',
        dictRemoveFile: 'Удалить',
        dictCancelUpload: 'Отмена',
        dictFileTooBig: 'Файл слишком большой (макс. 10 МБ)',
        dictInvalidFileType: 'Недопустимый тип файла',

        init: function () {
            this.on('removedfile', function (file) {
                const existingFileId = file.previewElement?.dataset?.existingFileId;

                // Удаляем с сервера ТОЛЬКО если есть existingFileId
                // И ТОЛЬКО если модалка открыта для редактирования
                if (existingFileId) {
                    console.log('Удаляем существующий файл с сервера, ID:', existingFileId);

                    fetch(`/calendar/files/${existingFileId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                        },
                    })
                        .then(r => r.json())
                        .then(data => {
                            console.log('Файл удалён с сервера:', data);
                        })
                        .catch(err => console.error('Ошибка удаления файла:', err));
                }
            });
        },
    });

    console.log('Dropzone инициализирован');
}

// === ОЧИСТКА DROPZONE БЕЗ УДАЛЕНИЯ ФАЙЛОВ С СЕРВЕРА ===
function clearDropzone() {
    if (!dropzoneInstance) return;

    // Временно отключаем обработчик removedfile
    dropzoneInstance.options.autoProcessQueue = false;

    // Удаляем все файлы из Dropzone НЕ ТРОГАЯ сервер
    while (dropzoneInstance.files.length > 0) {
        const file = dropzoneInstance.files[0];
        // Удаляем data-атрибут, чтобы removedfile не слал DELETE
        if (file.previewElement) {
            delete file.previewElement.dataset.existingFileId;
        }
        dropzoneInstance.removeFile(file);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, multiMonthPlugin, interactionPlugin],
        initialView: 'multiMonthYear',
        locale: ruLocale,
        firstDay: 1,
        editable: false,

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'multiMonthYear,dayGridMonth'
        },

        buttonText: {
            today: 'Сегодня',
            month: 'Месяц',
            multiMonthYear: 'Год',
        },

        events: '/calendar/events',

        dateClick: function (info) {
            console.log('Клик по дате:', info.dateStr);

            if (typeof window.resetForm === 'function') {
                window.resetForm();
            }

            // Просто очищаем Dropzone (без удаления с сервера)
            clearDropzone();

            document.getElementById('start_date').value = info.dateStr;
            document.getElementById('end_date').value = '';

            if (typeof window.openModal === 'function') {
                window.openModal();
            }
        },

        eventClick: function (info) {
            console.log('Клик по событию:', info.event.id);

            const e = info.event;
            const props = e.extendedProps || {};

            document.getElementById('task_id').value = e.id;
            document.getElementById('title').value = e.title;
            document.getElementById('description').value = props.description || '';
            document.getElementById('color').value = e.backgroundColor || '#3788d8';
            document.getElementById('quota').value = props.quota || '';
            document.getElementById('employee_position_id').value = props.employee_position_id || '';
            document.getElementById('start_date').value = e.startStr;

            // Безопасная обработка даты окончания (только строки)
            if (e.endStr) {
                // endStr всегда на 1 день больше реальной даты окончания
                const endDate = new Date(e.endStr);
                endDate.setDate(endDate.getDate() - 1);
                const realEndStr = endDate.toISOString().slice(0, 10);

                // Если после вычитания получилась дата начала – задача однодневная
                document.getElementById('end_date').value = (realEndStr !== e.startStr) ? realEndStr : '';
            } else {
                document.getElementById('end_date').value = '';
            }

            document.getElementById('modalTitle').textContent = 'Редактирование задачи';

            clearDropzone();

            if (e.id) {
                loadTaskFiles(e.id);
            }

            if (typeof window.openModal === 'function') {
                window.openModal();
            }
        },


        eventDrop: function (info) {
            const id = info.event.id;
            const startDate = info.event.startStr;
            const endDate = info.event.end ? info.event.endStr : info.event.startStr;

            fetch(`/calendar/tasks/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: info.event.title,
                    color: info.event.backgroundColor,
                    start_date: startDate,
                    end_date: endDate,
                }),
            })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) info.revert();
                })
                .catch(() => info.revert());
        },
    });

    calendar.render();

    // === DROPZONE (без авто-загрузки) ===
    initDropzone();

    // === САБМИТ ФОРМЫ ===
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', function (e) {
            e.preventDefault();
            console.log('Форма отправляется...');

            const id = document.getElementById('task_id').value;
            const url = id ? `/calendar/tasks/${id}` : '/calendar/tasks';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('title', document.getElementById('title').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('color', document.getElementById('color').value);
            formData.append('quota', document.getElementById('quota').value);
            formData.append('employee_position_id', document.getElementById('employee_position_id').value);
            formData.append('start_date', document.getElementById('start_date').value);

            const endDate = document.getElementById('end_date').value;
            if (endDate) {
                formData.append('end_date', endDate);
            }

            if (id) {
                formData.append('_method', 'PUT');
            }

            // Добавляем ТОЛЬКО НОВЫЕ файлы (не существующие)
            if (dropzoneInstance && dropzoneInstance.files.length > 0) {
                let newFilesCount = 0;
                dropzoneInstance.files.forEach(file => {
                    // Проверяем, что файл новый (нет existingFileId в previewElement)
                    const isExisting = file.previewElement?.dataset?.existingFileId;
                    if (!isExisting && (file.status === Dropzone.ADDED || file.status === Dropzone.QUEUED)) {
                        formData.append('files[]', file);
                        newFilesCount++;
                    }
                });
                console.log('Добавлено НОВЫХ файлов:', newFilesCount);
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Ответ сервера:', data);

                    if (data.success) {
                        if (!id) {
                            calendar.addEvent(data.event);
                        } else {
                            const existingEvent = calendar.getEventById(id);
                            if (existingEvent) existingEvent.remove();
                            calendar.addEvent(data.event);
                        }

                        if (typeof window.closeModal === 'function') {
                            window.closeModal();
                        }
                    } else {
                        alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                    }
                })
                .catch(error => {
                    console.error('Ошибка запроса:', error);
                    alert('Ошибка соединения с сервером. Проверьте консоль.');
                });
        });
        console.log('Обработчик формы привязан');
    }
});

