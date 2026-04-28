// vue узлы
import { createApp } from 'vue';
import StructureGraph from './components/StructureGraph.vue';
const app = createApp({});
app.component('structure-graph', StructureGraph);
app.mount('#app');

// calendar
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import multiMonthPlugin from '@fullcalendar/multimonth';
import interactionPlugin from '@fullcalendar/interaction';
import ruLocale from '@fullcalendar/core/locales/ru';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const calendar = new Calendar(calendarEl, {
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

        // Загрузка событий с сервера web.php
        events: '/calendar/events',

        // === КЛИК ПО ДНЮ — открываем модалку ===
        dateClick: function (info) {
            console.log('Клик по дате:', info.dateStr);

            // Проверяем, что функция resetForm существует
            if (typeof window.resetForm === 'function') {
                window.resetForm();
            }

            // Заполняем дату начала
            const startDateInput = document.getElementById('start_date');
            if (startDateInput) {
                startDateInput.value = info.dateStr;
            }

            // Очищаем дату окончания
            const endDateInput = document.getElementById('end_date');
            if (endDateInput) {
                endDateInput.value = '';
            }

            // Открываем модалку
            if (typeof window.openModal === 'function') {
                window.openModal();
            }
        },

        // === КЛИК ПО СОБЫТИЮ — редактирование ===
        eventClick: function (info) {
            const e = info.event;
            const props = e.extendedProps || {};

            // Заполняем форму
            const taskId = document.getElementById('task_id');
            if (taskId) taskId.value = e.id;

            const title = document.getElementById('title');
            if (title) title.value = e.title;

            const description = document.getElementById('description');
            if (description) description.value = props.description || '';

            const color = document.getElementById('color');
            if (color) color.value = e.backgroundColor || '#3788d8';

            const quota = document.getElementById('quota');
            if (quota) quota.value = props.quota || '';

            const commissariat = document.getElementById('commissariat_id');
            if (commissariat) commissariat.value = props.commissariat_id || '';

            const startDate = document.getElementById('start_date');
            if (startDate) startDate.value = e.startStr;

            const endDate = document.getElementById('end_date');
            if (endDate) {
                if (e.end) {
                    const end = new Date(e.end);
                    end.setDate(end.getDate() - 1);
                    endDate.value = end.toISOString().slice(0, 10);
                } else {
                    endDate.value = '';
                }
            }

            // Меняем заголовок модалки
            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) modalTitle.textContent = 'Редактирование задачи';

            // Открываем модалку
            if (typeof window.openModal === 'function') {
                window.openModal();
            }
        },

        // === DRAG & DROP ===
        eventDrop: function (info) {
            const id = info.event.id;
            const startDate = info.event.startStr;
            const endDate = info.event.end ? info.event.endStr : info.event.startStr;

            fetch(`/calendar/tasks/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: info.event.title,
                    color: info.event.backgroundColor,
                    start_date: startDate,
                    end_date: endDate,
                })
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

            const id = document.getElementById('task_id')?.value;
            const url = id ? `/calendar/tasks/${id}` : '/calendar/tasks';
            const method = id ? 'PUT' : 'POST';

            const formData = new FormData(this);
            if (id) {
                formData.append('_method', 'PUT');
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (!id) {
                        // Новая задача — добавляем событие
                        calendar.addEvent(data.event);
                    } else {
                        // Редактирование — удаляем старое, добавляем новое
                        const existingEvent = calendar.getEventById(id);
                        if (existingEvent) {
                            existingEvent.remove();
                        }
                        calendar.addEvent(data.event);
                    }

                    // Закрываем модалку
                    if (typeof window.closeModal === 'function') {
                        window.closeModal();
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка сохранения:', error);
            });
        });
    }
});