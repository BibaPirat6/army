import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import multiMonthPlugin from '@fullcalendar/multimonth';
import interactionPlugin from '@fullcalendar/interaction';
import ruLocale from '@fullcalendar/core/locales/ru';

// vue узлы
import { createApp } from 'vue';
import StructureGraph from './components/StructureGraph.vue';
const app = createApp({});
app.component('structure-graph', StructureGraph);
app.mount('#app');

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

        events: '/calendar/events',

        // КЛИК ПО ДНЮ
        dateClick: function (info) {
            console.log('Клик по дате:', info.dateStr);
            
            // Сбрасываем форму
            if (typeof window.resetForm === 'function') {
                window.resetForm();
            }
            
            // Устанавливаем дату
            document.getElementById('start_date').value = info.dateStr;
            document.getElementById('end_date').value = '';
            
            // Открываем модалку
            if (typeof window.openModal === 'function') {
                window.openModal();
            }
        },

        // КЛИК ПО СОБЫТИЮ
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

        // Берём CSRF-токен из мета-тега
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        console.log('CSRF Token:', csrfToken);

        // Собираем данные
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

        console.log('Отправка на:', url);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(response => {
            console.log('Статус ответа:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Ответ сервера:', data);
            
            if (data.success) {
                if (!id) {
                    calendar.addEvent(data.event);
                } else {
                    const existingEvent = calendar.getEventById(id);
                    if (existingEvent) {
                        existingEvent.remove();
                    }
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
} else {
    console.error('Форма #taskForm не найдена на странице!');
}
});