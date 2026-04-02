

// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

import { createApp } from 'vue';
import TestGraph from '../views//components/TestGraph.vue';

// Создаем приложение Vue
const app = createApp({});

// Регистрируем компонент глобально
app.component('test-graph', TestGraph);

// Монтируем приложение
app.mount('#app');