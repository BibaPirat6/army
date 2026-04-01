<template>
  <div class="graph-container">
    <div ref="svgContainer" class="svg-container"></div>
    <div class="controls">
      <button @click="zoomIn">+</button>
      <button @click="zoomOut">-</button>
      <button @click="resetView">⟳</button>
    </div>
  </div>
</template>

<script>
import * as d3 from 'd3';

export default {
  props: {
    data: {
      type: Object,
      required: true
    }
  },
  mounted() {
    this.initGraph();
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
        }))
        .append('g');
      
      this.g = this.svg;
      
      // Создаем симуляцию сил
      this.simulation = d3.forceSimulation(this.data.nodes)
        .force('link', d3.forceLink(this.data.links).id(d => d.id).distance(150))
        .force('charge', d3.forceManyBody().strength(-300))
        .force('center', d3.forceCenter(width / 2, height / 2))
        .force('collision', d3.forceCollide().radius(60));
      
      // Рисуем связи
      this.links = this.g.append('g')
        .selectAll('line')
        .data(this.data.links)
        .enter()
        .append('line')
        .attr('stroke', '#999')
        .attr('stroke-width', 2)
        .attr('stroke-opacity', 0.6);
      
      // Рисуем узлы
      this.nodes = this.g.append('g')
        .selectAll('g')
        .data(this.data.nodes)
        .enter()
        .append('g')
        .call(d3.drag()
          .on('start', this.dragStarted)
          .on('drag', this.dragged)
          .on('end', this.dragEnded));
      
      // Добавляем круги для узлов
      this.nodes.append('circle')
        .attr('r', 30)
        .attr('fill', d => d.type === 'commissariat' ? '#A60644' : 
                           d.type === 'department' ? '#565A5B' : 
                           d.type === 'division' ? '#7F7F7F' : '#BFBFBF')
        .attr('stroke', '#060606')
        .attr('stroke-width', 2);
      
      // Добавляем текст
      this.nodes.append('text')
        .text(d => d.name)
        .attr('text-anchor', 'middle')
        .attr('dy', '0.35em')
        .attr('fill', 'white')
        .style('font-size', '12px')
        .style('pointer-events', 'none');
      
      // Добавляем обработчик клика
      this.nodes.on('click', (event, d) => {
        window.location.href = d.url;
      });
      
      // Обновляем позиции
      this.simulation.on('tick', () => {
        this.links
          .attr('x1', d => d.source.x)
          .attr('y1', d => d.source.y)
          .attr('x2', d => d.target.x)
          .attr('y2', d => d.target.y);
        
        this.nodes
          .attr('transform', d => `translate(${d.x},${d.y})`);
      });
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
    
    zoomIn() {
      const transform = d3.zoomTransform(this.svg.node());
      this.svg.transition().call(d3.zoom().scaleBy, 1.2);
    },
    
    zoomOut() {
      this.svg.transition().call(d3.zoom().scaleBy, 0.8);
    },
    
    resetView() {
      this.svg.transition().call(d3.zoom().transform, d3.zoomIdentity);
    }
  },
  
  beforeUnmount() {
    this.simulation.stop();
  }
};
</script>

<style scoped>
.graph-container {
  width: 100vw;
  height: 100vh;
  position: relative;
  background: #f7f3f3;
}

.svg-container {
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
  background: white;
  padding: 10px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.controls button {
  width: 40px;
  height: 40px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 18px;
  transition: all 0.2s;
}

.controls button:hover {
  background: #A60644;
  color: white;
  border-color: #A60644;
}
</style>