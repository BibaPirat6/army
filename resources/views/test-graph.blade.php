<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест D3.js графа</title>
    <script src="https://d3js.org/d3.v7.min.js"></script>
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
        
        #graph-container {
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .controls {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            max-width: 600px;
            justify-content: flex-end;
        }
        
        button, .controls select {
            padding: 10px 15px;
            background: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.2s;
        }
        
        button:hover {
            transform: scale(1.05);
            background: #A60644;
            color: white;
        }
        
        .controls select {
            cursor: pointer;
        }
        
        .controls select:hover {
            background: #A60644;
            color: white;
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
        
        .search-box {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        #search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 250px;
            font-size: 14px;
        }
        
        #search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 5px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 101;
        }
        
        #search-results div {
            padding: 8px 12px;
            cursor: pointer;
        }
        
        #search-results div:hover {
            background: #f0f0f0;
        }
        
        .info-panel-click {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            min-width: 300px;
            max-width: 400px;
        }
        
        .stats-panel {
            position: fixed;
            top: 100px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 100;
            font-size: 12px;
            min-width: 200px;
            display: none;
        }
        
        .stats-panel h4 {
            margin-bottom: 10px;
            color: #A60644;
        }
        
        .stats-panel p {
            margin: 5px 0;
        }
        
        .close-stats {
            float: right;
            cursor: pointer;
            color: #999;
        }
        
        .close-stats:hover {
            color: #A60644;
        }
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
    
    <div class="search-box">
        <input type="text" id="search-input" placeholder="🔍 Поиск сотрудника...">
        <div id="search-results"></div>
    </div>
    
    <div class="stats-panel" id="stats-panel">
        <span class="close-stats" onclick="closeStats()">×</span>
        <h4>📊 Статистика графа</h4>
        <div id="stats-content"></div>
    </div>
    
    <div id="graph-container"></div>
    
    <div class="controls">
        <button onclick="addRandomNode()">➕ Добавить узел</button>
        <button onclick="resetView()">⟳ Сбросить вид</button>
        <button onclick="exportToPNG()">📸 Экспорт PNG</button>
        <button onclick="togglePhysics()" id="physics-btn">⏸ Выключить физику</button>
        <button onclick="saveGraph()">💾 Сохранить</button>
        <button onclick="loadGraph()">📂 Загрузить</button>
        <button onclick="showStats()">📊 Статистика</button>
        <select onchange="filterByType(this.value)">
            <option value="all">Все типы</option>
            <option value="boss">Начальники</option>
            <option value="department">Отделы</option>
            <option value="employee">Сотрудники</option>
        </select>
    </div>
    
    <div class="info-panel-click" id="info-panel-click"></div>

    <script>
        const width = window.innerWidth;
        const height = window.innerHeight;
        
        let nodes = [
            { id: 1, name: 'Начальник', type: 'boss', x: 400, y: 200 },
            { id: 2, name: 'Отдел 1', type: 'department', x: 200, y: 350 },
            { id: 3, name: 'Отдел 2', type: 'department', x: 600, y: 350 },
            { id: 4, name: 'Сотрудник 1', type: 'employee', x: 100, y: 500 },
            { id: 5, name: 'Сотрудник 2', type: 'employee', x: 300, y: 500 },
            { id: 6, name: 'Сотрудник 3', type: 'employee', x: 500, y: 500 },
            { id: 7, name: 'Сотрудник 4', type: 'employee', x: 700, y: 500 },
        ];
        
        let links = [
            { source: 1, target: 2 },
            { source: 1, target: 3 },
            { source: 2, target: 4 },
            { source: 2, target: 5 },
            { source: 3, target: 6 },
            { source: 3, target: 7 },
        ];
        
        let nextId = 8;
        let simulation;
        let svg;
        let g;
        let linkElements;
        let nodeElements;
        
        function initGraph() {
            svg = d3.select("#graph-container")
                .append("svg")
                .attr("width", width)
                .attr("height", height)
                .call(d3.zoom().on("zoom", (event) => {
                    g.attr("transform", event.transform);
                }));
            
            g = svg.append("g");
            
            simulation = d3.forceSimulation(nodes)
                .force("link", d3.forceLink(links).id(d => d.id).distance(150))
                .force("charge", d3.forceManyBody().strength(-200))
                .force("center", d3.forceCenter(width / 2, height / 2))
                .force("collision", d3.forceCollide().radius(50));
            
            draw();
            
            simulation.on("tick", () => {
                updatePositions();
            });
            
            setupSearch();
        }
        
        function draw() {
            // Рисуем связи
            linkElements = g.append("g")
                .selectAll("line")
                .data(links)
                .enter()
                .append("line")
                .attr("stroke", "#999")
                .attr("stroke-width", 2)
                .attr("stroke-opacity", 0.6);
            
            // Рисуем узлы
            nodeElements = g.append("g")
                .selectAll("g")
                .data(nodes)
                .enter()
                .append("g")
                .call(d3.drag()
                    .on("start", dragStarted)
                    .on("drag", dragged)
                    .on("end", dragEnded));
            
            nodeElements.append("circle")
                .attr("r", 35)
                .attr("fill", d => {
                    if (d.type === "boss") return "#A60644";
                    if (d.type === "department") return "#565A5B";
                    return "#7F7F7F";
                })
                .attr("stroke", "#060606")
                .attr("stroke-width", 2);
            
            nodeElements.append("text")
                .text(d => d.name)
                .attr("text-anchor", "middle")
                .attr("dy", "0.35em")
                .attr("fill", "white")
                .style("font-size", "12px")
                .style("font-weight", "bold")
                .style("pointer-events", "none");
            
            // Обработчик клика с информационной панелью
            nodeElements.on("click", (event, d) => {
                showNodeInfo(d);
                
                // Анимация узла
                d3.select(event.currentTarget).select('circle')
                    .transition()
                    .duration(300)
                    .attr('r', 45)
                    .transition()
                    .duration(300)
                    .attr('r', 35);
            });
        }
        
        function showNodeInfo(d) {
            const connections = links.filter(l => 
                (l.source.id === d.id || l.target.id === d.id)
            );
            
            const connectedNodes = connections.map(l => {
                const nodeId = l.source.id === d.id ? l.target.id : l.source.id;
                return nodes.find(n => n.id === nodeId);
            });
            
            const panel = document.getElementById('info-panel-click');
            panel.innerHTML = `
                <div style="margin-bottom: 15px;">
                    <h3 style="color: #A60644; margin-bottom: 5px;">${d.name}</h3>
                    <p style="color: #666; font-size: 12px;">Тип: ${getTypeName(d.type)}</p>
                    <p style="color: #666; font-size: 12px;">ID: ${d.id}</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>🔗 Связи:</strong> ${connections.length}
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>📌 Связанные узлы:</strong>
                    ${connectedNodes.map(n => `
                        <div style="padding: 5px; margin: 5px 0; background: #f5f5f5; border-radius: 5px; cursor: pointer;" onclick="centerOnNode(${n.id})">
                            ${n.name}
                        </div>
                    `).join('')}
                </div>
                <button onclick="closeInfoPanel()" style="
                    width: 100%;
                    padding: 8px;
                    background: #A60644;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                ">Закрыть</button>
            `;
            panel.style.display = 'block';
        }
        
        function closeInfoPanel() {
            document.getElementById('info-panel-click').style.display = 'none';
        }
        
        function getTypeName(type) {
            const types = {
                boss: 'Начальник',
                department: 'Отдел',
                employee: 'Сотрудник'
            };
            return types[type] || type;
        }
        
        function centerOnNode(nodeId) {
            const node = nodes.find(n => n.id === nodeId);
            if (node) {
                const transform = d3.zoomTransform(svg.node());
                const scale = transform.k;
                const translateX = width/2 - node.x * scale;
                const translateY = height/2 - node.y * scale;
                svg.transition()
                    .duration(750)
                    .call(d3.zoom().transform, d3.zoomIdentity.translate(translateX, translateY).scale(scale));
                closeInfoPanel();
            }
        }
        
        function updatePositions() {
            linkElements
                .attr("x1", d => d.source.x)
                .attr("y1", d => d.source.y)
                .attr("x2", d => d.target.x)
                .attr("y2", d => d.target.y);
            
            nodeElements
                .attr("transform", d => `translate(${d.x},${d.y})`);
        }
        
        function dragStarted(event, d) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            d.fx = d.x;
            d.fy = d.y;
        }
        
        function dragged(event, d) {
            d.fx = event.x;
            d.fy = event.y;
        }
        
        function dragEnded(event, d) {
            if (!event.active) simulation.alphaTarget(0);
            d.fx = null;
            d.fy = null;
        }
        
        function addRandomNode() {
            const newNode = {
                id: nextId++,
                name: `Новый ${nextId - 1}`,
                type: "employee",
                x: Math.random() * 800 + 100,
                y: Math.random() * 500 + 100
            };
            
            nodes.push(newNode);
            
            const randomNode = nodes[Math.floor(Math.random() * (nodes.length - 2))];
            if (randomNode && randomNode.id !== newNode.id) {
                links.push({ source: randomNode.id, target: newNode.id });
            }
            
            refreshGraph();
        }
        
        function refreshGraph() {
            g.selectAll("*").remove();
            simulation.nodes(nodes);
            simulation.force("link").links(links);
            simulation.alpha(1).restart();
            draw();
        }
        
        function resetView() {
            svg.transition()
                .duration(750)
                .call(d3.zoom().transform, d3.zoomIdentity);
        }
        
        function exportToPNG() {
            const serializer = new XMLSerializer();
            let source = serializer.serializeToString(svg.node());
            source = '<?xml version="1.0" standalone="no"?>\r\n' + source;
            const url = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(source);
            const downloadLink = document.createElement("a");
            downloadLink.href = url;
            downloadLink.download = "graph-export.svg";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
        
        function togglePhysics() {
            const enabled = simulation.alpha() > 0;
            const btn = document.getElementById('physics-btn');
            if (enabled) {
                simulation.stop();
                btn.textContent = '▶ Включить физику';
                btn.style.background = '#28a745';
            } else {
                simulation.alpha(1).restart();
                btn.textContent = '⏸ Выключить физику';
                btn.style.background = '#dc3545';
            }
        }
        
        function saveGraph() {
            const graphData = {
                nodes: nodes.map(({ x, y, ...rest }) => rest),
                links: links.map(l => ({
                    source: l.source.id,
                    target: l.target.id
                })),
                nextId: nextId
            };
            localStorage.setItem('savedGraph', JSON.stringify(graphData));
            alert('✅ Граф сохранен!');
        }
        
        function loadGraph() {
            const saved = localStorage.getItem('savedGraph');
            if (saved) {
                const graphData = JSON.parse(saved);
                nodes = graphData.nodes.map(n => ({
                    ...n,
                    x: Math.random() * 800 + 100,
                    y: Math.random() * 500 + 100
                }));
                links = graphData.links;
                nextId = graphData.nextId;
                refreshGraph();
                alert('✅ Граф загружен!');
            } else {
                alert('❌ Нет сохраненного графа');
            }
        }
        
        function filterByType(type) {
            nodeElements.style('display', d => {
                if (type === 'all') return null;
                return d.type === type ? null : 'none';
            });
            
            linkElements.style('display', l => {
                if (type === 'all') return null;
                const sourceType = nodes.find(n => n.id === l.source.id)?.type;
                const targetType = nodes.find(n => n.id === l.target.id)?.type;
                return (sourceType === type || targetType === type) ? null : 'none';
            });
        }
        
        function setupSearch() {
            const searchInput = document.getElementById('search-input');
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const results = nodes.filter(node => 
                    node.name.toLowerCase().includes(query)
                );
                
                nodeElements.select('circle')
                    .attr('stroke', d => results.includes(d) ? '#ffeb3b' : '#060606')
                    .attr('stroke-width', d => results.includes(d) ? 4 : 2);
                
                const resultsDiv = document.getElementById('search-results');
                if (query && results.length) {
                    resultsDiv.innerHTML = results.map(r => 
                        `<div onclick="centerOnNode(${r.id})">${r.name}</div>`
                    ).join('');
                    resultsDiv.style.display = 'block';
                } else {
                    resultsDiv.style.display = 'none';
                }
            });
        }
        
        function showStats() {
            const stats = {
                total: nodes.length,
                bosses: nodes.filter(n => n.type === 'boss').length,
                departments: nodes.filter(n => n.type === 'department').length,
                employees: nodes.filter(n => n.type === 'employee').length,
                connections: links.length,
                avgConnections: (links.length * 2 / nodes.length).toFixed(2)
            };
    
            const content = document.getElementById('stats-content');
            content.innerHTML = `
                <p>📊 Всего узлов: ${stats.total}</p>
                <p>├─ Начальников: ${stats.bosses}</p>
                <p>├─ Отделов: ${stats.departments}</p>
                <p>└─ Сотрудников: ${stats.employees}</p>
                <p>🔗 Связей: ${stats.connections}</p>
                <p>📈 Средняя степень: ${stats.avgConnections}</p>
            `;
            document.getElementById('stats-panel').style.display = 'block';
        }
        
        function closeStats() {
            document.getElementById('stats-panel').style.display = 'none';
        }
        
        // Запускаем граф после загрузки страницы
        window.addEventListener('load', initGraph);
        
        // Делаем функции глобальными
        window.addRandomNode = addRandomNode;
        window.resetView = resetView;
        window.exportToPNG = exportToPNG;
        window.togglePhysics = togglePhysics;
        window.saveGraph = saveGraph;
        window.loadGraph = loadGraph;
        window.filterByType = filterByType;
        window.showStats = showStats;
        window.closeStats = closeStats;
        window.closeInfoPanel = closeInfoPanel;
        window.centerOnNode = centerOnNode;
    </script>
</body>
</html>