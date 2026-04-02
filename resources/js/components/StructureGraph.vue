<template>
    <div class="graph-container">
        <div ref="svgContainer" class="svg-container"></div>
        
        <div class="controls">
            <button @click="zoomIn">+</button>
            <button @click="zoomOut">-</button>
            <button @click="resetView">⟳</button>
        </div>
        
        <div v-if="selectedNode" class="info-panel">
            <h3>{{ selectedNode.name }}</h3>
            <p>Тип: {{ selectedNode.type }}</p>
            <button @click="selectedNode = null">Закрыть</button>
        </div>
    </div>
</template>

<script>
import * as d3 from 'd3';

export default {
    name: 'StructureGraph',
    props: {
        nodes: {
            type: Array,
            default: () => []
        },
        links: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            svg: null,
            g: null,
            simulation: null,
            selectedNode: null,
            width: window.innerWidth,
            height: window.innerHeight
        };
    },
    mounted() {
        console.log('Component mounted, nodes:', this.nodes);
        console.log('Component mounted, links:', this.links);
        
        if (this.nodes.length > 0) {
            this.initGraph();
        } else {
            console.warn('No nodes data received!');
        }
        
        window.addEventListener('resize', this.handleResize);
    },
    beforeUnmount() {
        if (this.simulation) {
            this.simulation.stop();
        }
        window.removeEventListener('resize', this.handleResize);
    },
    watch: {
        nodes: {
            handler(newVal) {
                console.log('Nodes changed:', newVal);
                if (newVal.length > 0) {
                    this.initGraph();
                }
            },
            deep: true
        }
    },
    methods: {
        initGraph() {
            const container = this.$refs.svgContainer;
            if (!container) {
                console.error('Container not found!');
                return;
            }
            
            container.innerHTML = '';
            
            this.svg = d3.select(container)
                .append('svg')
                .attr('width', this.width)
                .attr('height', this.height)
                .call(d3.zoom().on('zoom', (event) => {
                    if (this.g) {
                        this.g.attr('transform', event.transform);
                    }
                }));
            
            this.g = this.svg.append('g');
            
            // Копируем узлы для симуляции
            const simulationNodes = this.nodes.map(n => ({ ...n }));
            const simulationLinks = this.links.map(l => ({ ...l }));
            
            this.simulation = d3.forceSimulation(simulationNodes)
                .force('link', d3.forceLink(simulationLinks).id(d => d.id).distance(150))
                .force('charge', d3.forceManyBody().strength(-300))
                .force('center', d3.forceCenter(this.width / 2, this.height / 2))
                .force('collision', d3.forceCollide().radius(50));
            
            // Рисуем связи
            this.linkElements = this.g.append('g')
                .selectAll('line')
                .data(simulationLinks)
                .enter()
                .append('line')
                .attr('stroke', '#999')
                .attr('stroke-width', 2);
            
            // Рисуем узлы
            this.nodeElements = this.g.append('g')
                .selectAll('g')
                .data(simulationNodes)
                .enter()
                .append('g')
                .call(d3.drag()
                    .on('start', (event, d) => {
                        if (!event.active) this.simulation.alphaTarget(0.3).restart();
                        d.fx = d.x;
                        d.fy = d.y;
                    })
                    .on('drag', (event, d) => {
                        d.fx = event.x;
                        d.fy = event.y;
                    })
                    .on('end', (event, d) => {
                        if (!event.active) this.simulation.alphaTarget(0);
                        d.fx = null;
                        d.fy = null;
                    }));
            
            // Круги
            this.nodeElements.append('circle')
                .attr('r', 30)
                .attr('fill', d => {
                    if (d.type === 'commissariat') return '#A60644';
                    if (d.type === 'department') return '#565A5B';
                    if (d.type === 'division') return '#7F7F7F';
                    return '#BFBFBF';
                })
                .attr('stroke', '#060606')
                .attr('stroke-width', 2);
            
            // Текст
            this.nodeElements.append('text')
                .text(d => d.name.length > 15 ? d.name.substring(0, 12) + '...' : d.name)
                .attr('text-anchor', 'middle')
                .attr('dy', '0.35em')
                .attr('fill', 'white')
                .style('font-size', '12px')
                .style('font-weight', 'bold');
            
            // Клик
            this.nodeElements.on('click', (event, d) => {
                event.stopPropagation();
                this.selectedNode = d;
            });
            
            // Обновление позиций
            this.simulation.on('tick', () => {
                this.linkElements
                    .attr('x1', d => d.source.x)
                    .attr('y1', d => d.source.y)
                    .attr('x2', d => d.target.x)
                    .attr('y2', d => d.target.y);
                
                this.nodeElements
                    .attr('transform', d => `translate(${d.x},${d.y})`);
            });
            
            console.log('Graph initialized with', simulationNodes.length, 'nodes');
        },
        
        zoomIn() {
            this.svg.transition().call(d3.zoom().scaleBy, 1.2);
        },
        
        zoomOut() {
            this.svg.transition().call(d3.zoom().scaleBy, 0.8);
        },
        
        resetView() {
            this.svg.transition().call(d3.zoom().transform, d3.zoomIdentity);
        },
        
        handleResize() {
            this.width = window.innerWidth;
            this.height = window.innerHeight;
            if (this.svg) {
                this.svg.attr('width', this.width).attr('height', this.height);
                if (this.simulation) {
                    this.simulation.force('center', d3.forceCenter(this.width / 2, this.height / 2));
                    this.simulation.alpha(0.3).restart();
                }
            }
        }
    }
};
</script>

<style scoped>
.graph-container {
    width: 100vw;
    height: 100vh;
    position: relative;
    background: linear-gradient(135deg, #f7f3f3 0%, #e8e4e4 100%);
    overflow: hidden;
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
    gap: 8px;
    background: white;
    padding: 8px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.controls button {
    width: 40px;
    height: 40px;
    border: none;
    background: #f5f5f5;
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.2s;
}

.controls button:hover {
    background: #A60644;
    color: white;
}

.info-panel {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    z-index: 200;
    min-width: 250px;
}

.info-panel h3 {
    margin: 0 0 10px 0;
    color: #A60644;
}

.info-panel button {
    margin-top: 10px;
    padding: 8px 16px;
    background: #A60644;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
</style>