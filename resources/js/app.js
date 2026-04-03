import { createApp } from 'vue';
import StructureGraph from './components/StructureGraph.vue';
const app = createApp({});
app.component('structure-graph', StructureGraph);
app.mount('#app');
