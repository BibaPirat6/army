<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест D3.js графа</title>
    @vite(['resources/js/app.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        
        .info-panel {
            position: fixed;
            top: 20px;
            left: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 100;
            pointer-events: none;
        }
        
        .info-panel h1 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .info-panel p {
            font-size: 12px;
            color: #666;
        }
        
        .legend {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 100;
            font-size: 12px;
            pointer-events: none;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .legend-color.boss { background: #A60644; }
        .legend-color.department { background: #565A5B; }
        .legend-color.employee { background: #7F7F7F; }
    </style>
</head>
<body>
    <div class="info-panel">
        <h1>📊 Тест D3.js графа</h1>
        <p>Перетаскивайте узлы | Нажимайте на узлы | Используйте колесико мыши для зума</p>
    </div>
    
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color boss"></div>
            <span>Начальник</span>
        </div>
        <div class="legend-item">
            <div class="legend-color department"></div>
            <span>Отдел</span>
        </div>
        <div class="legend-item">
            <div class="legend-color employee"></div>
            <span>Сотрудник</span>
        </div>
    </div>
    
    <div id="app">
        <test-graph></test-graph>
    </div>
    
    <script type="module">
        import { createApp } from 'vue';
        import TestGraph from './resources/js/components/TestGraph.vue';
        
        const app = createApp({});
        app.component('test-graph', TestGraph);
        app.mount('#app');
    </script>
</body>
</html>