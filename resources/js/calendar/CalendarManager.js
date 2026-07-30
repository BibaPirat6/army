/**
 * CalendarManager
 * 
 * Управление жизненным циклом FullCalendar
 * Изолирован от Vue и остального приложения
 */
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import multiMonthPlugin from '@fullcalendar/multimonth';
import interactionPlugin from '@fullcalendar/interaction';
import ruLocale from '@fullcalendar/core/locales/ru';

export class CalendarManager {
    constructor(modalManager) {
        this.calendar = null;
        this.modalManager = modalManager;
        this.isLoading = false;
    }

    /**
     * Инициализация календаря
     * @param {HTMLElement} element - DOM элемент для монтирования
     */
    init(element) {
        if (this.calendar) {
            this.destroy();
        }

        this.calendar = new Calendar(element, {
            plugins: [dayGridPlugin, multiMonthPlugin, interactionPlugin],
            initialView: 'multiMonthYear',
            locale: ruLocale,
            firstDay: 1,
            editable: false,
            
            // Адаптивная высота
            height: 'auto',
            contentHeight: 'auto',
            
            // Обработка изменения размера окна
            windowResize: () => {
                this.calendar?.updateSize();
            },

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'multiMonthYear,dayGridMonth',
            },

            buttonText: {
                today: 'Сегодня',
                month: 'Месяц',
                multiMonthYear: 'Год',
            },

            // Показываем индикатор загрузки
            loading: (isLoading) => {
                this.isLoading = isLoading;
                this.toggleLoadingIndicator(isLoading);
            },

            // Загрузка событий через AJAX
            events: {
                url: '/calendar/events',
                method: 'GET',
                failure: (error) => {
                    this.showError('Ошибка загрузки данных календаря');
                    console.error('Calendar events fetch error:', error);
                }
            },

            // Навигация при клике на дату
            dateClick: this.handleDateClick.bind(this),

            // Навигация при клике на задачу
            eventClick: this.handleEventClick.bind(this),

            // Показываем подсказку при наведении
            eventMouseEnter: this.handleEventMouseEnter.bind(this),
            eventMouseLeave: this.handleEventMouseLeave.bind(this),

            // Адаптивный вид для мобильных устройств
            views: {
                multiMonthYear: {
                    duration: { months: 12 },
                    multiMonthMaxColumns: 3,
                }
            }
        });

        this.calendar.render();
    }

    /**
     * Безопасное формирование URL для создания задачи
     */
    handleDateClick(info) {
        const url = new URL('/calendar/tasks/create', window.location.origin);
        url.searchParams.set('start_date', info.dateStr);
        window.location.href = url.toString();
    }

    /**
     * Переход на страницу задачи
     */
    handleEventClick(info) {
        if (info.event.id) {
            window.location.href = `/calendar/tasks/${info.event.id}`;
        }
    }

    /**
     * Показ тултипа с информацией о задаче
     */
    handleEventMouseEnter(info) {
        // Можно добавить кастомный тултип с деталями задачи
        const event = info.event;
        const tooltip = document.createElement('div');
        tooltip.className = 'calendar-tooltip';
        tooltip.innerHTML = `
            <div class="font-semibold">${event.title}</div>
            <div class="text-sm text-gray-600">${event.extendedProps?.department || ''}</div>
        `;
        document.body.appendChild(tooltip);
        // Позиционирование тултипа относительно курсора
    }

    handleEventMouseLeave(info) {
        document.querySelector('.calendar-tooltip')?.remove();
    }

    /**
     * Индикатор загрузки
     */
    toggleLoadingIndicator(show) {
        const indicator = document.getElementById('calendar-loading');
        if (indicator) {
            indicator.style.display = show ? 'flex' : 'none';
        }
    }

    /**
     * Показать ошибку
     */
    showError(message) {
        const errorEl = document.getElementById('calendar-error');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
            setTimeout(() => {
                errorEl.style.display = 'none';
            }, 5000);
        }
    }

    /**
     * Обновление календаря (для внешних вызовов)
     */
    refresh() {
        this.calendar?.refetchEvents();
    }

    /**
     * Переключение вида
     */
    changeView(viewName) {
        this.calendar?.changeView(viewName);
    }

    /**
     * Очистка при уничтожении
     */
    destroy() {
        if (this.calendar) {
            this.calendar.destroy();
            this.calendar = null;
        }
    }
}