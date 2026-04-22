<template>
    <div class="graph-container">
        <div ref="svgContainer" class="svg-container"></div>

        <!-- Управление -->
        <div class="controls">
            <button @click="zoomIn" class="control-btn" title="Приблизить">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            <button @click="zoomOut" class="control-btn" title="Отдалить">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
            <button @click="resetView" class="control-btn" title="Сбросить вид">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>
    </div>
</template>

<script>
import * as d3 from 'd3';

export default {
    name: 'StructureGraph',
    props: {
        nodes: { type: Array, default: () => [] },
        links: { type: Array, default: () => [] },
        backUrl: { type: String, default: '' }
    },
    data() {
        return {
            svg: null,
            g: null,
            zoomBehavior: null,
            simulation: null,
            width: window.innerWidth,
            height: window.innerHeight,
            nodeElements: null,
            linkElements: null,
        }
    },
    mounted() {
        if (this.nodes.length) this.initGraph();
        window.addEventListener('resize', this.handleResize);
    },
    beforeUnmount() {
        if (this.simulation) this.simulation.stop();
        window.removeEventListener('resize', this.handleResize);
    },
    methods: {
        getNodeColor(node) {
            if (node.type === 'position') {
                return node.isFullyOccupied ? '#4CAF50' : '#F44336';
            }
            const colors = {
                commissariat: '#A60644',
                department: '#565A5B',
                division: '#7F7F7F',
                group: '#3a86ff'
            };
            return colors[node.type] || '#999';
        },

        handleNodeClick(node) {
            if (node.type === 'position' && node.url) {
                let url = node.url;
                const separator = url.includes('?') ? '&' : '?';
                url = `${url}${separator}back_url=${encodeURIComponent(this.backUrl)}`;
                if (node.commissariatId) {
                    url = `${url}&commissariat_id=${node.commissariatId}`;
                }
                window.location.href = url;
            } else if (node.url && node.type !== 'group') {
                let url = node.url;
                const separator = url.includes('?') ? '&' : '?';
                url = `${url}${separator}back_url=${encodeURIComponent(this.backUrl)}`;
                window.location.href = url;
            }
        },

        initGraph() {
            const container = this.$refs.svgContainer;
            if (!container) return;
            container.innerHTML = '';

            this.zoomBehavior = d3.zoom()
                .scaleExtent([0.1, 3])
                .on('zoom', (event) => {
                    if (this.g) this.g.attr('transform', event.transform);
                });

            this.svg = d3.select(container)
                .append('svg')
                .attr('width', this.width)
                .attr('height', this.height)
                .call(this.zoomBehavior);

            this.g = this.svg.append('g');

            let simulationNodes = this.nodes.map(n => ({ ...n }));
            const simulationLinks = this.links.map(l => ({ source: l.source, target: l.target }));

            const nodeMap = new Map();
            simulationNodes.forEach(node => nodeMap.set(node.id, node));
            simulationLinks.forEach(link => {
                if (typeof link.source === 'string') link.source = nodeMap.get(link.source);
                if (typeof link.target === 'string') link.target = nodeMap.get(link.target);
            });

            this.calculateInitialPositions(simulationNodes, simulationLinks);

            this.simulation = d3.forceSimulation(simulationNodes)
                .force('link', d3.forceLink(simulationLinks).id(d => d.id).distance(120).strength(0.5))
                .force('charge', d3.forceManyBody().strength(d => {
                    if (d.type === 'commissariat') return -500;
                    if (d.type === 'department') return -300;
                    if (d.type === 'division') return -200;
                    if (d.type === 'group') return -150;
                    return -100;
                }))
                .force('center', d3.forceCenter(this.width / 2, this.height / 2))
                .force('collision', d3.forceCollide().radius(50))
                .alphaDecay(0.02);

            const mainNode = simulationNodes.find(n => n.type === 'commissariat');
            if (mainNode) {
                mainNode.fx = this.width / 2;
                mainNode.fy = this.height / 2;
            }

            this.draw(simulationNodes, simulationLinks);
            this.simulation.on('tick', () => this.updatePositions());
        },

        draw(nodesData, linksData) {
            this.g.selectAll('.links').remove();
            this.g.selectAll('.nodes').remove();

            this.linkElements = this.g.append('g').attr('class', 'links')
                .selectAll('line').data(linksData).enter()
                .append('line').attr('stroke', '#999').attr('stroke-width', 1.5).attr('stroke-opacity', 0.4);

            this.nodeElements = this.g.append('g').attr('class', 'nodes')
                .selectAll('g').data(nodesData).enter()
                .append('g').attr('cursor', 'pointer')
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

            this.nodeElements.each((d, i, group) => {
                const nodeGroup = d3.select(group[i]);

                if (d.type === 'group') {
                    nodeGroup.append('rect')
                        .attr('x', -45).attr('y', -20).attr('width', 90).attr('height', 40)
                        .attr('rx', 10).attr('fill', this.getNodeColor(d))
                        .attr('stroke', '#060606').attr('stroke-width', 2).attr('stroke-dasharray', '4,2');
                    nodeGroup.append('text')
                        .attr('text-anchor', 'middle').attr('dy', '0.35em')
                        .attr('fill', 'white').style('font-size', '12px').style('font-weight', 'bold')
                        .text(() => d.name.length > 14 ? d.name.substring(0, 11) + '...' : d.name);
                } else {
                    let radius = 28, iconText = '●', textDy = '2.2em';
                    if (d.type === 'commissariat') { radius = 48; iconText = '★'; textDy = '2.5em'; }
                    else if (d.type === 'department') { radius = 40; iconText = '◆'; textDy = '2.3em'; }
                    else if (d.type === 'division') { radius = 34; iconText = '●'; textDy = '2.2em'; }
                    else if (d.type === 'position') { radius = 32; iconText = '📋'; textDy = '2.3em'; }

                    nodeGroup.append('title').text(() => {
                        if (d.type === 'position') {
                            return `${d.name}\nВсего: ${d.totalRate}\nЗанято: ${d.occupiedRate}\nСвободно: ${d.availableRate}`;
                        }
                        return d.name;
                    });

                    nodeGroup.append('circle')
                        .attr('r', radius).attr('fill', this.getNodeColor(d))
                        .attr('stroke', '#060606').attr('stroke-width', 2.5);

                    nodeGroup.append('text')
                        .attr('text-anchor', 'middle').attr('dy', '0.35em')
                        .attr('fill', 'white').style('font-size', '16px').style('font-weight', 'bold')
                        .text(iconText);

                    nodeGroup.append('text')
                        .attr('text-anchor', 'middle').attr('dy', textDy)
                        .attr('fill', '#060606').style('font-size', '10px').style('font-weight', '500')
                        .text(() => d.name.length > 20 ? d.name.substring(0, 17) + '...' : d.name);
                }
            });

            this.nodeElements.on('click', (event, d) => {
                event.stopPropagation();
                this.handleNodeClick(d);
            });
        },

        updatePositions() {
            this.linkElements
                .attr('x1', d => d.source.x).attr('y1', d => d.source.y)
                .attr('x2', d => d.target.x).attr('y2', d => d.target.y);
            this.nodeElements.attr('transform', d => `translate(${d.x},${d.y})`);
        },

        zoomIn() {
            if (this.svg && this.zoomBehavior) {
                this.svg.transition().duration(150).call(this.zoomBehavior.scaleBy, 1.2);
            }
        },

        zoomOut() {
            if (this.svg && this.zoomBehavior) {
                this.svg.transition().duration(150).call(this.zoomBehavior.scaleBy, 0.8);
            }
        },

        resetView() {
            if (this.svg && this.zoomBehavior) {
                this.svg.transition().duration(500).call(this.zoomBehavior.transform, d3.zoomIdentity);
            }
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
        },

        calculateInitialPositions(simulationNodes, simulationLinks) {
            const centerX = this.width / 2;
            const centerY = this.height / 2;

            const mainNode = simulationNodes.find(n => n.type === 'commissariat');
            if (mainNode) {
                mainNode.x = centerX;
                mainNode.y = centerY;
            }

            const radius = 250;
            const departments = simulationNodes.filter(n => n.type === 'department');
            departments.forEach((dept, i) => {
                const angle = (i / Math.max(departments.length, 1)) * Math.PI * 2;
                dept.x = centerX + Math.cos(angle) * radius;
                dept.y = centerY + Math.sin(angle) * radius;
            });

            simulationNodes.forEach(node => {
                if (!node.x) {
                    node.x = centerX + (Math.random() - 0.5) * 400;
                    node.y = centerY + (Math.random() - 0.5) * 300;
                }
            });
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
.svg-container { width: 100%; height: 100%; }
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
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.control-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: #f5f5f5;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.control-btn:hover {
    background: #A60644;
    color: white;
    transform: scale(1.05);
}
</style>