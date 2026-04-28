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

// Глобальные переменные
let dropzoneInstance = null;
let calendar = null;

// === ФУНКЦИЯ ИНИЦИАЛИЗАЦИИ DROPZONE ===
function initDropzone() {
    if (dropzoneInstance) {
        dropzoneInstance.destroy();
    }

    const dropzoneEl = document.getElementById('taskFileDropzone');
    if (!dropzoneEl) return;

    dropzoneInstance = new Dropzone('#taskFileDropzone', {
        url: '/calendar/files/upload',
        method: 'post',
        maxFilesize: 10,
        acceptedFiles: 'image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar',
        addRemoveLinks: true,
        dictDefaultMessage: 'Перетащите файлы сюда или кликните для выбора',
        dictRemoveFile: 'Удалить',
        dictCancelUpload: 'Отмена',
        dictFileTooBig: 'Файл слишком большой (макс. 10 МБ)',
        dictInvalidFileType: 'Недопустимый тип файла',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        init: function () {
            this.on('sending', function (file, xhr, formData) {
                const taskId = document.getElementById('task_id')?.value;
                if (taskId) {
                    formData.append('task_id', taskId);
                }
            });

            this.on('success', function (file, response) {
                file.uploadedFileId = response.file_id;
                console.log('Файл загружен, ID:', response.file_id);
            });

            this.on('removedfile', function (file) {
                if (file.uploadedFileId) {
                    fetch(`/calendar/files/${file.uploadedFileId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                        },
                    })
                    .then(r => r.json())
                    .then(data => console.log('Файл удалён:', data))
                    .catch(err => console.error('Ошибка удаления файла:', err));
                }
            });

            this.on('error', function (file, message) {
                console.error('Ошибка загрузки файла:', file.name, message);
            });
        },
    });
}

// === ФУНКЦИЯ ПРИВЯЗКИ ФАЙЛОВ К ЗАДАЧЕ ===
function attachFilesToTask(taskId) {
    if (!dropzoneInstance) return Promise.resolve();
    
    const uploadedFiles = dropzoneInstance.getFilesWithStatus(Dropzone.SUCCESS);
    if (uploadedFiles.length === 0) return Promise.resolve();

    return fetch(`/calendar/files/attach/${taskId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        console.log('Файлы привязаны к задаче:', data);
        return data;
    })
    .catch(err => {
        console.error('Ошибка привязки файлов:', err);
    });
}

// === ИНИЦИАЛИЗАЦИЯ ПРИ ЗАГРУЗКЕ СТРАНИЦЫ ===
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, multiMonthPlugin, interactionPlugin],
        initialView: 'multiMonthYear',
        locale: ruLocale,
        firstDay: 1,
        editable: true,

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

        // === КЛИК ПО ДНЮ ===
        dateClick: function (info) {
            console.log('Клик по дате:', info.dateStr);

            if (typeof window.resetForm === 'function') {
                window.resetForm();
            }

            // Удаляем старые файлы из Dropzone
            if (dropzoneInstance) {
                dropzoneInstance.removeAllFiles(true);
            }

            document.getElementById('start_date').value = info.dateStr;
            document.getElementById('end_date').value = '';

            if (typeof window.openModal === 'function') {
                window.openModal();
            }

            // Инициализируем Dropzone после открытия модалки
            setTimeout(initDropzone, 100);
        },

        // === КЛИК ПО СОБЫТИЮ (РЕДАКТИРОВАНИЕ) ===
        eventClick: function (info) {
            console.log('Клик по событию:', info.event.id);

            const e = info.event;
            const props = e.extendedProps || {};

            document.getElementById('task_id').value = e.id;
            document.getElementById('title').value = e.title;
            document.getElementById('description').value = props.description || '';
            document.getElementById('color').value = e.backgroundColor || '#3788d8';
            document.getElementById('quota').value = props.quota || '';
            document.getElementById('commissariat_id').value = props.commissariat_id || '';
            document.getElementById('start_date').value = e.startStr;

            if (e.end) {
                const endDate = new Date(e.end);
                endDate.setDate(endDate.getDate() - 1);
                document.getElementById('end_date').value = endDate.toISOString().slice(0, 10);
            } else {
                document.getElementById('end_date').value = '';
            }

            document.getElementById('modalTitle').textContent = 'Редактирование задачи';

            if (typeof window.openModal === 'function') {
                window.openModal();
            }

            // Инициализируем Dropzone и загружаем существующие файлы
            setTimeout(() => {
                initDropzone();
                
                // Загружаем существующие файлы задачи
                if (e.id) {
                    fetch(`/calendar/tasks/${e.id}/files`)
                        .then(r => r.json())
                        .then(files => {
                            files.forEach(file => {
                                if (dropzoneInstance) {
                                    // Создаём mock-файл в Dropzone
                                    const mockFile = {
                                        name: file.original_name,
                                        size: file.size,
                                        accepted: true,
                                        uploadedFileId: file.id,
                                    };
                                    
                                    dropzoneInstance.emit('addedfile', mockFile);
                                    dropzoneInstance.emit('thumbnail', mockFile, file.url || '/storage/' + file.path);
                                    dropzoneInstance.emit('complete', mockFile);
                                    dropzoneInstance.files.push(mockFile);
                                }
                            });
                        })
                        .catch(err => console.error('Ошибка загрузки файлов задачи:', err));
                }
            }, 100);
        },

        // === DRAG & DROP СОБЫТИЯ ===
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
                if (!data.success) {
                    info.revert();
                }
            })
            .catch(() => info.revert());
        },
    });

    calendar.render();

    // === САБМИТ ФОРМЫ ===
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', function (e) {
            e.preventDefault();
            console.log('Форма отправляется...');

            const id = document.getElementById('task_id').value;
            const url = id ? `/calendar/tasks/${id}` : '/calendar/tasks';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const data = {
                _token: csrfToken,
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                color: document.getElementById('color').value,
                quota: document.getElementById('quota').value,
                commissariat_id: document.getElementById('commissariat_id').value,
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value,
            };

            if (id) {
                data._method = 'PUT';
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(data => {
                console.log('Ответ сервера:', data);

                if (data.success) {
                    const taskId = data.event.id;

                    // Привязываем файлы к задаче
                    attachFilesToTask(taskId).then(() => {
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
                    });
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
    } else {
        console.error('Форма #taskForm не найдена на странице!');
    }
});