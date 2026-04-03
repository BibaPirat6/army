<template>
    <div class="graph-container">
        <div ref="svgContainer" class="svg-container"></div>
        
        <!-- Управление -->
        <div class="controls">
            <button 
                @mousedown="startZoomIn" 
                @mouseup="stopZoom" 
                @mouseleave="stopZoom"
                @touchstart="startZoomIn" 
                @touchend="stopZoom"
                class="control-btn" 
                title="Приблизить">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            <button 
                @mousedown="startZoomOut" 
                @mouseup="stopZoom" 
                @mouseleave="stopZoom"
                @touchstart="startZoomOut" 
                @touchend="stopZoom"
                class="control-btn" 
                title="Отдалить">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
            <button @click="resetView" class="control-btn" title="Сбросить вид">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
        
        <!-- Модальное окно для узлов -->
        <div v-if="selectedNode && selectedNode.type !== 'employee'" class="modal-overlay" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header" :style="{ backgroundColor: getNodeColor(selectedNode.type) }">
                    <h3>{{ selectedNode.name }}</h3>
                    <button @click="closeModal" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p><strong>Тип:</strong> {{ getNodeTypeLabel(selectedNode.type) }}</p>
                    <p><strong>ID:</strong> {{ selectedNode.id }}</p>
                    <div class="modal-buttons">
                        <button @click="addEmployee" class="btn-add">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Добавить сотрудника
                        </button>
                        <button v-if="selectedNode.type !== 'employee'" @click="addSubdivision" class="btn-add">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Добавить {{ selectedNode.type === 'commissariat' ? 'отдел' : 'отделение' }}
                        </button>
                        <button @click="closeModal" class="btn-cancel">Закрыть</button>
                    </div>
                </div>
            </div>
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
            zoomBehavior: null,
            simulation: null,
            selectedNode: null,
            width: window.innerWidth,
            height: window.innerHeight,
            nodeElements: null,
            linkElements: null,
            zoomInterval: null  // Для интервала при зажатой кнопке
        };
    },
    mounted() {
        if (this.nodes.length > 0) {
            this.initGraph();
        }
        window.addEventListener('resize', this.handleResize);
    },
    beforeUnmount() {
        if (this.simulation) {
            this.simulation.stop();
        }
        window.removeEventListener('resize', this.handleResize);
        this.stopZoom();
    },
    methods: {
        getNodeColor(type) {
            const colors = {
                commissariat: '#A60644',
                department: '#565A5B',
                division: '#7F7F7F',
                employee: '#BFBFBF'
            };
            return colors[type] || '#999';
        },
        
        getNodeTypeLabel(type) {
            const labels = {
                commissariat: 'Комиссариат',
                department: 'Отдел',
                division: 'Отделение',
                employee: 'Сотрудник'
            };
            return labels[type] || type;
        },
        
        initGraph() {
            const container = this.$refs.svgContainer;
            if (!container) return;
            
            container.innerHTML = '';
            
            this.zoomBehavior = d3.zoom()
                .scaleExtent([0.1, 3])
                .on('zoom', (event) => {
                    if (this.g) {
                        this.g.attr('transform', event.transform);
                    }
                });
            
            this.svg = d3.select(container)
                .append('svg')
                .attr('width', this.width)
                .attr('height', this.height)
                .call(this.zoomBehavior);
            
            this.g = this.svg.append('g');
            
            const simulationNodes = this.nodes.map(n => ({ ...n }));
            const simulationLinks = this.links.map(l => ({ 
                source: l.source, 
                target: l.target 
            }));
            
            const nodeMap = new Map();
            simulationNodes.forEach(node => {
                nodeMap.set(node.id, node);
            });
            
            simulationLinks.forEach(link => {
                if (typeof link.source === 'string') {
                    link.source = nodeMap.get(link.source);
                }
                if (typeof link.target === 'string') {
                    link.target = nodeMap.get(link.target);
                }
            });
            
            this.simulation = d3.forceSimulation(simulationNodes)
                .force('link', d3.forceLink(simulationLinks).id(d => d.id).distance(150).strength(0.5))
                .force('charge', d3.forceManyBody().strength(-300))
                .force('center', d3.forceCenter(this.width / 2, this.height / 2))
                .force('collision', d3.forceCollide().radius(50));
            
            this.draw(simulationNodes, simulationLinks);
            
            this.simulation.on('tick', () => {
                this.updatePositions();
            });
        },
        
        draw(nodesData, linksData) {
            this.linkElements = this.g.append('g')
                .selectAll('line')
                .data(linksData)
                .enter()
                .append('line')
                .attr('stroke', '#999')
                .attr('stroke-width', 1.5)
                .attr('stroke-opacity', 0.5);
            
            this.nodeElements = this.g.append('g')
                .selectAll('g')
                .data(nodesData)
                .enter()
                .append('g')
                .attr('cursor', 'pointer')
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
            
            this.nodeElements.append('circle')
                .attr('r', d => {
                    if (d.type === 'commissariat') return 45;
                    if (d.type === 'department') return 38;
                    if (d.type === 'division') return 32;
                    return 28;
                })
                .attr('fill', d => this.getNodeColor(d.type))
                .attr('stroke', '#060606')
                .attr('stroke-width', 2);
            
            this.nodeElements.append('text')
                .attr('text-anchor', 'middle')
                .attr('dy', '0.35em')
                .attr('fill', 'white')
                .style('font-size', d => d.type === 'commissariat' ? '20px' : '16px')
                .style('font-weight', 'bold')
                .text(d => {
                    if (d.type === 'commissariat') return '★';
                    if (d.type === 'department') return '●';
                    if (d.type === 'division') return '◆';
                    return '👤';
                });
            
            this.nodeElements.append('text')
                .attr('text-anchor', 'middle')
                .attr('dy', d => d.type === 'commissariat' ? '2.5em' : '2.2em')
                .attr('fill', '#060606')
                .style('font-size', '10px')
                .style('font-weight', '500')
                .text(d => {
                    let name = d.name;
                    if (name.length > 18) name = name.substring(0, 15) + '...';
                    return name;
                });
            
            this.nodeElements.on('click', (event, d) => {
                event.stopPropagation();
                if (d.type === 'employee') {
                    if (d.url) window.location.href = d.url;
                } else {
                    this.selectedNode = d;
                }
            });
            
            this.nodeElements.on('mouseenter', function(event, d) {
                d3.select(this).select('circle')
                    .transition().duration(200)
                    .attr('r', d => {
                        if (d.type === 'commissariat') return 52;
                        if (d.type === 'department') return 44;
                        if (d.type === 'division') return 38;
                        return 34;
                    });
            }).on('mouseleave', function() {
                d3.select(this).select('circle')
                    .transition().duration(200)
                    .attr('r', d => {
                        if (d.type === 'commissariat') return 45;
                        if (d.type === 'department') return 38;
                        if (d.type === 'division') return 32;
                        return 28;
                    });
            });
        },
        
        updatePositions() {
            if (this.linkElements) {
                this.linkElements
                    .attr('x1', d => d.source.x)
                    .attr('y1', d => d.source.y)
                    .attr('x2', d => d.target.x)
                    .attr('y2', d => d.target.y);
            }
            if (this.nodeElements) {
                this.nodeElements.attr('transform', d => `translate(${d.x},${d.y})`);
            }
        },
        
        // ========== ДИНАМИЧЕСКИЙ ЗУМ ==========
        
        startZoomIn() {
            this.zoomIn();
            this.zoomInterval = setInterval(() => {
                this.zoomIn();
            }, 50); // Каждые 50мс приближаем
        },
        
        startZoomOut() {
            this.zoomOut();
            this.zoomInterval = setInterval(() => {
                this.zoomOut();
            }, 50); // Каждые 50мс отдаляем
        },
        
        stopZoom() {
            if (this.zoomInterval) {
                clearInterval(this.zoomInterval);
                this.zoomInterval = null;
            }
        },
        
        zoomIn() {
            if (this.svg && this.zoomBehavior) {
                this.svg.transition().duration(50).call(this.zoomBehavior.scaleBy, 1.05);
            }
        },
        
        zoomOut() {
            if (this.svg && this.zoomBehavior) {
                this.svg.transition().duration(50).call(this.zoomBehavior.scaleBy, 0.95);
            }
        },
        
        resetView() {
            if (this.svg && this.zoomBehavior) {
                this.svg.transition().duration(500).call(this.zoomBehavior.transform, d3.zoomIdentity);
            }
        },

        
        closeModal() {
            this.selectedNode = null;
        },
        
        addEmployee() {
            if (this.selectedNode) {
                let url = '';
                const backUrl = encodeURIComponent(window.location.href);
                if (this.selectedNode.type === 'commissariat') {
                    const id = this.selectedNode.id.replace('commissariat_', '');
                    url = `/employees/create?commissariat_id=${id}&back_url=${backUrl}`;
                } else if (this.selectedNode.type === 'department') {
                    const id = this.selectedNode.id.replace('department_', '');
                    url = `/employees/create?department_id=${id}&back_url=${backUrl}`;
                } else if (this.selectedNode.type === 'division') {
                    const id = this.selectedNode.id.replace('division_', '');
                    url = `/employees/create?division_id=${id}&back_url=${backUrl}`;
                }
                window.location.href = url;
            }
        },
        
        addSubdivision() {
            if (this.selectedNode) {
                let url = '';
                const backUrl = encodeURIComponent(window.location.href);
                if (this.selectedNode.type === 'commissariat') {
                    const id = this.selectedNode.id.replace('commissariat_', '');
                    url = `/departments/create?commissariat_id=${id}&back_url=${backUrl}`;
                } else if (this.selectedNode.type === 'department') {
                    const id = this.selectedNode.id.replace('department_', '');
                    url = `/divisions/create?department_id=${id}&back_url=${backUrl}`;
                }
                window.location.href = url;
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

.control-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: #f5f5f5;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.control-btn:hover {
    background: #A60644;
    color: white;
    transform: scale(1.05);
}

/* Модальное окно */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 16px;
    width: 350px;
    max-width: 90%;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.2s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    color: white;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.modal-body {
    padding: 20px;
}

.modal-body p {
    margin: 8px 0;
    color: #060606;
}

.modal-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

.btn-add, .btn-cancel {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-add {
    background: #A60644;
    color: white;
}

.btn-add:hover {
    background: #6b0229;
    transform: translateY(-1px);
}

.btn-cancel {
    background: #e0e0e0;
    color: #060606;
}

.btn-cancel:hover {
    background: #ccc;
}
</style>