

// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

import { createApp } from 'vue';
import StructureGraph from './components/StructureGraph.vue';

console.log('Vue app starting...'); // Отладка

const app = createApp({});
app.component('structure-graph', StructureGraph);
app.mount('#app');

console.log('Vue app mounted'); // Отладка