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

        dateClick: function (info) {
            console.log('Клик по дате:', info.dateStr);

            if (typeof window.resetForm === 'function') {
                window.resetForm();
            }

            // Очищаем Dropzone
            if (dropzoneInstance) {
                dropzoneInstance.removeAllFiles(true);
            }

            // Безопасно устанавливаем дату
            const startDateEl = document.getElementById('start_date');
            if (startDateEl) {
                startDateEl.value = info.dateStr;
            } else {
                console.error('Элемент #start_date не найден в DOM');
            }

            const endDateEl = document.getElementById('end_date');
            if (endDateEl) {
                endDateEl.value = '';
            }

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

            // Очищаем Dropzone
            if (dropzoneInstance) {
                dropzoneInstance.removeAllFiles(true);
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

            // Собираем FormData (поддерживает файлы)
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('title', document.getElementById('title').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('color', document.getElementById('color').value);
            formData.append('quota', document.getElementById('quota').value);
            formData.append('commissariat_id', document.getElementById('commissariat_id').value);
            formData.append('start_date', document.getElementById('start_date').value);
            formData.append('end_date', document.getElementById('end_date').value);

            if (id) {
                formData.append('_method', 'PUT');
            }

            // Добавляем файлы из Dropzone
            if (dropzoneInstance) {
                const files = dropzoneInstance.getAcceptedFiles(); // все принятые файлы
                files.forEach(file => {
                    formData.append('files[]', file);
                });
            }

            console.log('Отправка на:', url);

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

// === ФУНКЦИЯ ИНИЦИАЛИЗАЦИИ DROPZONE ===
function initDropzone() {
    const dropzoneEl = document.getElementById('taskFileDropzone');
    if (!dropzoneEl) return;

    // Уничтожаем старый экземпляр
    if (dropzoneInstance) {
        dropzoneInstance.destroy();
    }

    dropzoneInstance = new Dropzone('#taskFileDropzone', {
        url: '/calendar/tasks',        // фейковый URL (не используется)
        method: 'post',
        autoProcessQueue: false,       // ← ГЛАВНОЕ: отключаем авто-загрузку
        maxFilesize: 10,
        maxFiles: 10,
        acceptedFiles: 'image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar',
        addRemoveLinks: true,
        dictDefaultMessage: 'Перетащите файлы сюда или кликните для выбора',
        dictRemoveFile: 'Удалить',
        dictCancelUpload: 'Отмена',
        dictFileTooBig: 'Файл слишком большой (макс. 10 МБ)',
        dictInvalidFileType: 'Недопустимый тип файла',
    });

    console.log('Dropzone инициализирован (авто-загрузка отключена)');
}