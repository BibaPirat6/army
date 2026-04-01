<template>
  <div class="test-graph">
    <div ref="svgContainer" class="graph-container"></div>
    <div class="controls">
      <button @click="addNode">➕ Добавить узел</button>
      <button @click="resetView">⟳ Сбросить вид</button>
    </div>
  </div>
</template>

<script>
import * as d3 from 'd3';

export default {
  name: 'TestGraph',
  data() {
    return {
      nodes: [
        { id: 1, name: 'Начальник', type: 'boss', x: 400, y: 200 },
        { id: 2, name: 'Отдел 1', type: 'department', x: 200, y: 350 },
        { id: 3, name: 'Отдел 2', type: 'department', x: 600, y: 350 },
        { id: 4, name: 'Сотрудник 1', type: 'employee', x: 100, y: 500 },
        { id: 5, name: 'Сотрудник 2', type: 'employee', x: 300, y: 500 },
        { id: 6, name: 'Сотрудник 3', type: 'employee', x: 500, y: 500 },
        { id: 7, name: 'Сотрудник 4', type: 'employee', x: 700, y: 500 },
      ],
      links: [
        { source: 1, target: 2 },
        { source: 1, target: 3 },
        { source: 2, target: 4 },
        { source: 2, target: 5 },
        { source: 3, target: 6 },
        { source: 3, target: 7 },
      ],
      simulation: null,
      svg: null,
      g: null,
      nextId: 8
    };
  },
  mounted() {
    this.initGraph();
  },
  beforeUnmount() {
    if (this.simulation) {
      this.simulation.stop();
    }
  },
  methods: {
    initGraph() {
      const width = window.innerWidth;
      const height = window.innerHeight;
      
      // Создаем SVG
      this.svg = d3.select(this.$refs.svgContainer)
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .call(d3.zoom().on('zoom', (event) => {
          this.g.attr('transform', event.transform);
        }));
      
      this.g = this.svg.append('g');
      
      // Создаем симуляцию сил
      this.simulation = d3.forceSimulation(this.nodes)
        .force('link', d3.forceLink(this.links).id(d => d.id).distance(150))
        .force('charge', d3.forceManyBody().strength(-200))
        .force('center', d3.forceCenter(width / 2, height / 2))
        .force('collision', d3.forceCollide().radius(50));
      
      this.draw();
      
      // Обновляем позиции при каждом тике
      this.simulation.on('tick', () => {
        this.updatePositions();
      });
    },
    
    draw() {
      // Рисуем связи
      this.linkElements = this.g.append('g')
        .selectAll('line')
        .data(this.links)
        .enter()
        .append('line')
        .attr('stroke', '#999')
        .attr('stroke-width', 2)
        .attr('stroke-opacity', 0.6);
      
      // Рисуем узлы
      this.nodeElements = this.g.append('g')
        .selectAll('g')
        .data(this.nodes)
        .enter()
        .append('g')
        .call(d3.drag()
          .on('start', this.dragStarted)
          .on('drag', this.dragged)
          .on('end', this.dragEnded));
      
      // Добавляем круги для узлов
      this.nodeElements.append('circle')
        .attr('r', 35)
        .attr('fill', d => {
          if (d.type === 'boss') return '#A60644';
          if (d.type === 'department') return '#565A5B';
          return '#7F7F7F';
        })
        .attr('stroke', '#060606')
        .attr('stroke-width', 2);
      
      // Добавляем текст
      this.nodeElements.append('text')
        .text(d => d.name)
        .attr('text-anchor', 'middle')
        .attr('dy', '0.35em')
        .attr('fill', 'white')
        .style('font-size', '12px')
        .style('font-weight', 'bold')
        .style('pointer-events', 'none');
      
      // Добавляем обработчик клика
      this.nodeElements.on('click', (event, d) => {
        alert(`Вы нажали на: ${d.name}\nID: ${d.id}\nТип: ${d.type}`);
      });
      
      // Добавляем эффект при наведении
      this.nodeElements.on('mouseenter', function() {
        d3.select(this).select('circle')
          .transition()
          .duration(200)
          .attr('r', 40)
          .attr('stroke-width', 3);
      }).on('mouseleave', function() {
        d3.select(this).select('circle')
          .transition()
          .duration(200)
          .attr('r', 35)
          .attr('stroke-width', 2);
      });
    },
    
    updatePositions() {
      // Обновляем позиции связей
      this.linkElements
        .attr('x1', d => d.source.x)
        .attr('y1', d => d.source.y)
        .attr('x2', d => d.target.x)
        .attr('y2', d => d.target.y);
      
      // Обновляем позиции узлов
      this.nodeElements
        .attr('transform', d => `translate(${d.x},${d.y})`);
    },
    
    dragStarted(event, d) {
      if (!event.active) this.simulation.alphaTarget(0.3).restart();
      d.fx = d.x;
      d.fy = d.y;
    },
    
    dragged(event, d) {
      d.fx = event.x;
      d.fy = event.y;
    },
    
    dragEnded(event, d) {
      if (!event.active) this.simulation.alphaTarget(0);
      d.fx = null;
      d.fy = null;
    },
    
    addNode() {
      const newNode = {
        id: this.nextId++,
        name: `Новый ${this.nextId - 1}`,
        type: 'employee',
        x: Math.random() * 800 + 100,
        y: Math.random() * 500 + 100
      };
      
      this.nodes.push(newNode);
      
      // Случайно связываем с существующим узлом
      const randomNode = this.nodes[Math.floor(Math.random() * (this.nodes.length - 1))];
      if (randomNode && randomNode.id !== newNode.id) {
        this.links.push({ source: randomNode.id, target: newNode.id });
      }
      
      // Обновляем визуализацию
      this.refreshGraph();
    },
    
    refreshGraph() {
      // Очищаем текущий граф
      this.g.selectAll('*').remove();
      
      // Обновляем данные в симуляции
      this.simulation.nodes(this.nodes);
      this.simulation.force('link').links(this.links);
      this.simulation.alpha(1).restart();
      
      // Перерисовываем
      this.draw();
    },
    
    resetView() {
      this.svg.transition()
        .duration(750)
        .call(d3.zoom().transform, d3.zoomIdentity);
    }
  }
};
</script>

<style scoped>
.test-graph {
  width: 100vw;
  height: 100vh;
  position: relative;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.graph-container {
  width: 100%;
  height: 100%;
}

.controls {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 100;
  display: flex;
  gap: 10px;
}

.controls button {
  padding: 10px 20px;
  background: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  box-shadow: 0 2px 10px rgba(0,0,0,0.2);
  transition: all 0.2s;
}

.controls button:hover {
  transform: scale(1.05);
  background: #A60644;
  color: white;
}
</style>