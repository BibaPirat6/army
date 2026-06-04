/**
 * Army Task Management System
 * Главный JavaScript модуль
 */

import { CalendarManager } from './calendar/CalendarManager';
import { ModalManager } from './ui/ModalManager';
import { initTomSelect } from './components/tom-select';

// Vue компоненты
import { createApp } from 'vue';
import StructureGraph from './components/StructureGraph.vue';

// ============================================
// Инициализация Vue
// ============================================
const vueApp = createApp({});
vueApp.component('structure-graph', StructureGraph);
vueApp.mount('#vue-app');

// ============================================
// Инициализация менеджеров
// ============================================
const modalManager = new ModalManager();
const calendarManager = new CalendarManager(modalManager);

// Экспорт для использования в других модулях
export { calendarManager, modalManager };

// ============================================
// Инициализация при загрузке DOM
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    // TomSelect
    const tomSelectElements = [
        '#position_type_id',
        '#chief_type_id',
        '#commissariat_id',
        '#department_id',
        '#division_id'
    ];
    
    tomSelectElements.forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
            initTomSelect(selector);
        }
    });

    // Календарь
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        calendarManager.init(calendarEl);
    }

    // Делаем календарь доступным глобально
    window.calendarManager = calendarManager;
});