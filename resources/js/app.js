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
// import 'fullcalendar/index.css';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, multiMonthPlugin, interactionPlugin],
            initialView: 'multiMonthYear',
            locale: ruLocale,
            firstDay: 1,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'multiMonthYear,dayGridMonth'
            },
            dateClick: function (info) {
                // Открытие модального окна для создания задачи
                // Пример информации: info.dateStr – '2026-05-15'
                console.log('Клик по дате:', info.dateStr);
            },
            // Позже добавим загрузку событий
            events: [],
        });
        calendar.render();
    }
});