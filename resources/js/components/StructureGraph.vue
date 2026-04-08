<template>
    <div class="graph-container">
        <div ref="svgContainer" class="svg-container"></div>

        <!-- Фильтры -->
        <div class="filters-panel">
            <div v-show="showFilters" class="filters-content">
                <div class="filter-group">
                    <div class="filter-title">Структура</div>
                    <label class="filter-checkbox">
                        <input type="checkbox" v-model="filters.departments" @change="applyFilters">
                        <span>📁 Отделы</span>
                        <span class="filter-count-badge">{{ counts.departments }}</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" v-model="filters.divisions" @change="applyFilters">
                        <span>📂 Отделения (обычные)</span>
                        <span class="filter-count-badge">{{ counts.divisions }}</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" v-model="filters.independentDivisions" @change="applyFilters">
                        <span>🔗 Отделения (самостоятельные)</span>
                        <span class="filter-count-badge">{{ counts.independentDivisions }}</span>
                    </label>
                </div>

                <div class="filter-group">
                    <div class="filter-title">Сотрудники</div>
                    <label class="filter-checkbox">
                        <input type="checkbox" v-model="filters.employees" @change="applyFilters">
                        <span>👥 Все сотрудники</span>
                        <span class="filter-count-badge">{{ counts.employees + counts.independentEmployees +
                            counts.chiefs }}</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" v-model="filters.regularEmployees" @change="applyFilters">
                        <span>👤 Обычные сотрудники</span>
                        <span class="filter-count-badge">{{ counts.regularEmployees }}</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" v-model="filters.independentEmployees" @change="applyFilters">
                        <span>⭐ Самостоятельные сотрудники</span>
                        <span class="filter-count-badge">{{ counts.independentEmployees }}</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" v-model="filters.chiefs" @change="applyFilters">
                        <span>👨‍💼 Начальники</span>
                        <span class="filter-count-badge">{{ counts.chiefs }}</span>
                    </label>
                </div>

                <div class="filter-actions">
                    <button @click="resetFilters" class="filter-reset">Сбросить все</button>
                    <span class="filter-count">Показано: {{ visibleNodesCount }}</span>
                </div>
            </div>
            <div class="filters-header" @click="showFilters = !showFilters">
                <h3>🔍 Фильтры</h3>
                <svg class="filters-toggle" :class="{ rotated: showFilters }" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>

        <!-- Поиск -->
        <div class="search-panel">
            <div class="search-header">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" v-model="searchQuery" @input="searchNodes"
                    placeholder="Поиск по сотрудникам, отделам, отделениям..." class="search-input" />
                <button v-if="searchQuery" @click="clearSearch" class="search-clear">×</button>
            </div>
            <div v-if="searchResults.length > 0 && searchQuery" class="search-results">
                <div v-for="result in searchResults" :key="result.id" class="search-result-item"
                    @click="focusOnNode(result)">
                    <span class="result-type">{{ getNodeTypeLabel(result.type) }}</span>
                    <span class="result-name">{{ result.name }}</span>
                </div>
            </div>
            <div v-if="searchResults.length === 0 && searchQuery" class="search-no-results">
                Ничего не найдено
            </div>
        </div>

        <!-- Управление -->
        <div class="controls">
            <button @mousedown="startZoomIn" @mouseup="stopZoom" @mouseleave="stopZoom" @touchstart="startZoomIn"
                @touchend="stopZoom" class="control-btn" title="Приблизить">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
            <button @mousedown="startZoomOut" @mouseup="stopZoom" @mouseleave="stopZoom" @touchstart="startZoomOut"
                @touchend="stopZoom" class="control-btn" title="Отдалить">
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
            <button @click="fitToScreen" class="control-btn" title="Вписать в экран">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4">
                    </path>
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
                    <div class="modal-buttons">
                        <button @click="addEmployee" class="btn-add">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Добавить сотрудника
                        </button>
                        <button v-if="selectedNode.type !== 'employee' && selectedNode.type !== 'group'"
                            @click="addSubdivision" class="btn-add">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
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
        },
        backUrl: {
            type: String,
            default: ''
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
            zoomInterval: null,
            // search
            searchQuery: '',
            searchResults: [],
            originalNodeColors: new Map(), // для хранения оригинальных цветов
            // фильтры
            showFilters: false,
            filters: {
                departments: true,
                divisions: true,
                independentDivisions: true,
                employees: true,
                regularEmployees: true,
                independentEmployees: true,
                chiefs: true,
            },
            visibleNodesCount: 0,

            currentBackUrl: this.backUrl || window.location.href,
        }
    },
    computed: {
        filteredNodeIds() {
            const filtered = [];
            this.nodes.forEach(node => {
                if (this.shouldShowNode(node)) {
                    filtered.push(node.id);
                }
            });
            return filtered;
        },
        // Счетчики для фильтров
        counts() {
            return {
                departments: this.nodes.filter(n => n.type === 'department').length,
                divisions: this.nodes.filter(n => n.type === 'division' && !n.id.includes('independent') && !n.id.includes('group')).length,
                independentDivisions: this.nodes.filter(n => n.type === 'division' && n.id.includes('independent')).length,
                employees: this.nodes.filter(n => n.type === 'employee' && !n.isChief && !n.isIndependent && !n.id.includes('independent')).length,
                regularEmployees: this.nodes.filter(n => n.type === 'employee' && !n.isChief && !n.isIndependent && !n.id.includes('independent')).length,
                independentEmployees: this.nodes.filter(n => n.type === 'employee' && (n.isIndependent === true || n.id.includes('independent'))).length,
                chiefs: this.nodes.filter(n => n.type === 'employee' && n.isChief === true).length,
            };
        },
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
                employee: '#BFBFBF',
                group: '#3a86ff'
            };
            return colors[type] || '#999';
        },

        getNodeTypeLabel(type) {
            const labels = {
                commissariat: 'Комиссариат',
                department: 'Отдел',
                division: 'Отделение',
                employee: 'Сотрудник',
                group: 'Группа'
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

            // СОЗДАЕМ nodeMap ПЕРЕД ИСПОЛЬЗОВАНИЕМ
            const nodeMap = new Map();
            simulationNodes.forEach(node => {
                nodeMap.set(node.id, node);
            });

            // ДОБАВЛЯЕМ back_url В УЗЛЫ
            simulationNodes.forEach(node => {
                if (node.url && node.url.includes('?')) {
                    node.url = node.url + (node.url.includes('back_url') ? '' : `&back_url=${encodeURIComponent(this.backUrl)}`);
                } else if (node.url) {
                    node.url = node.url + `?back_url=${encodeURIComponent(this.backUrl)}`;
                }
            });

            // ПРЕОБРАЗУЕМ ССЫЛКИ ИЗ ID В ОБЪЕКТЫ
            simulationLinks.forEach(link => {
                if (typeof link.source === 'string') {
                    link.source = nodeMap.get(link.source);
                }
                if (typeof link.target === 'string') {
                    link.target = nodeMap.get(link.target);
                }
            });

            // ========== РАСЧЕТ НАЧАЛЬНЫХ ПОЗИЦИЙ ==========
            const centerX = this.width / 2;
            const centerY = this.height / 2;

            // Находим главный узел (комиссариат)
            const mainNode = simulationNodes.find(n => n.type === 'commissariat');

            if (mainNode) {
                // Комиссариат в центре
                mainNode.x = centerX;
                mainNode.y = centerY;
                mainNode.fx = centerX;
                mainNode.fy = centerY;

                // Группируем узлы по типам
                const departments = simulationNodes.filter(n => n.type === 'department');

                // Располагаем отделы по кругу
                const deptRadius = 250;
                const deptAngleStep = (Math.PI * 2) / Math.max(departments.length, 1);
                departments.forEach((dept, index) => {
                    const angle = index * deptAngleStep;
                    dept.x = centerX + Math.cos(angle) * deptRadius;
                    dept.y = centerY + Math.sin(angle) * deptRadius;
                });

                // Располагаем отделения вокруг их отделов
                departments.forEach(dept => {
                    const relatedDivisions = simulationNodes.filter(n =>
                        n.type === 'division' &&
                        simulationLinks.some(l => l.source === dept && l.target === n)
                    );

                    const divRadius = 150;
                    const divAngleStep = (Math.PI * 2) / Math.max(relatedDivisions.length, 1);
                    relatedDivisions.forEach((div, idx) => {
                        const angle = idx * divAngleStep;
                        div.x = dept.x + Math.cos(angle) * divRadius;
                        div.y = dept.y + Math.sin(angle) * divRadius;
                    });
                });

                // Располагаем сотрудников вокруг их отделений
                simulationNodes.forEach(node => {
                    if (node.type === 'division') {
                        const relatedEmployees = simulationNodes.filter(n =>
                            n.type === 'employee' &&
                            simulationLinks.some(l => l.source === node && l.target === n)
                        );

                        const empRadius = 120;
                        const empAngleStep = (Math.PI * 2) / Math.max(relatedEmployees.length, 1);
                        relatedEmployees.forEach((emp, idx) => {
                            const angle = idx * empAngleStep;
                            emp.x = node.x + Math.cos(angle) * empRadius;
                            emp.y = node.y + Math.sin(angle) * empRadius;
                        });
                    }
                });

                // Сотрудники, связанные напрямую с комиссариатом
                const directEmployees = simulationNodes.filter(n =>
                    n.type === 'employee' &&
                    simulationLinks.some(l => l.source === mainNode && l.target === n)
                );

                const directEmpRadius = 200;
                const directEmpAngleStep = (Math.PI * 2) / Math.max(directEmployees.length, 1);
                directEmployees.forEach((emp, idx) => {
                    const angle = idx * directEmpAngleStep;
                    emp.x = centerX + Math.cos(angle) * directEmpRadius;
                    emp.y = centerY + Math.sin(angle) * directEmpRadius;
                });

                // Группы (контейнеры сотрудников)
                const groups = simulationNodes.filter(n => n.type === 'group');
                groups.forEach(group => {
                    const parentDept = simulationNodes.find(d =>
                        simulationLinks.some(l => l.source === d && l.target === group)
                    );
                    if (parentDept) {
                        const groupRadius = 140;
                        const angle = Math.random() * Math.PI * 2;
                        group.x = parentDept.x + Math.cos(angle) * groupRadius;
                        group.y = parentDept.y + Math.sin(angle) * groupRadius;
                    } else {
                        group.x = centerX + (Math.random() - 0.5) * 400;
                        group.y = centerY + (Math.random() - 0.5) * 300;
                    }
                });
            }

            // ========== НАСТРОЙКА СИЛ ==========
            this.simulation = d3.forceSimulation(simulationNodes)
                .force('link', d3.forceLink(simulationLinks)
                    .id(d => d.id)
                    .distance(d => {
                        if (d.source.type === 'commissariat' || d.target.type === 'commissariat') return 180;
                        if (d.source.type === 'department' || d.target.type === 'department') return 140;
                        if (d.source.type === 'division' || d.target.type === 'division') return 110;
                        if (d.source.type === 'group' || d.target.type === 'group') return 100;
                        return 80;
                    })
                    .strength(d => {
                        if (d.source.type === 'commissariat' || d.target.type === 'commissariat') return 0.3;
                        if (d.source.type === 'department' || d.target.type === 'department') return 0.4;
                        return 0.5;
                    }))
                .force('charge', d3.forceManyBody()
                    .strength(d => {
                        if (d.type === 'commissariat') return -500;
                        if (d.type === 'department') return -300;
                        if (d.type === 'division') return -200;
                        if (d.type === 'group') return -150;
                        return -120;
                    }))
                .force('center', d3.forceCenter(this.width / 2, this.height / 2))
                .force('collision', d3.forceCollide().radius(60))
                .alphaDecay(0.02)
                .velocityDecay(0.4);

            // Фиксируем комиссариат в центре на время стабилизации
            if (mainNode) {
                setTimeout(() => {
                    if (this.simulation) {
                        mainNode.fx = null;
                        mainNode.fy = null;
                    }
                }, 3000);
            }

            this.draw(simulationNodes, simulationLinks);

            this.simulation.on('tick', () => {
                this.updatePositions();
            });
        },

        draw(nodesData, linksData) {
            this.g.selectAll('.links').remove();
            this.g.selectAll('.nodes').remove();

            this.linkElements = this.g.append('g')
                .attr('class', 'links')
                .selectAll('line')
                .data(linksData)
                .enter()
                .append('line')
                .attr('stroke', '#999')
                .attr('stroke-width', 1.5)
                .attr('stroke-opacity', 0.4);

            this.nodeElements = this.g.append('g')
                .attr('class', 'nodes')
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

            this.nodeElements.each((d, i, group) => {
                const nodeGroup = d3.select(group[i]);
                const isChief = d.isChief === true;

                if (d.type === 'group') {
                    nodeGroup.append('rect')
                        .attr('x', -45)
                        .attr('y', -20)
                        .attr('width', 90)
                        .attr('height', 40)
                        .attr('rx', 10)
                        .attr('fill', this.getNodeColor(d.type))
                        .attr('stroke', '#060606')
                        .attr('stroke-width', 2)
                        .attr('stroke-dasharray', '4,2');

                    // ДОБАВЛЯЕМ TITLE ДЛЯ ГРУППЫ
                    nodeGroup.append('title')
                        .text(() => {
                            let titleText = d.name;
                            if (d.type === 'group') {
                                titleText = `Группа: ${d.name}`;
                            }
                            return titleText;
                        });

                    nodeGroup.append('text')
                        .attr('text-anchor', 'middle')
                        .attr('dy', '0.35em')
                        .attr('fill', 'white')
                        .style('font-size', '12px')
                        .style('font-weight', 'bold')
                        .text(() => {
                            let name = d.name;
                            if (name.length > 14) name = name.substring(0, 11) + '...';
                            return name;
                        });
                } else {
                    let radius = 28;
                    let fillColor = '#BFBFBF';
                    let strokeColor = '#060606';
                    let iconSize = '16px';
                    let iconColor = 'white';
                    let iconText = '👤';
                    let textDy = '2.2em';
                    let textSize = '10px';
                    let textWeight = '500';

                    if (d.type === 'commissariat') {
                        radius = 48;
                        fillColor = '#A60644';
                        iconSize = '22px';
                        iconText = '★';
                        textDy = '2.5em';
                    } else if (d.type === 'department') {
                        radius = 40;
                        fillColor = '#565A5B';
                        iconText = '◆';
                        textDy = '2.3em';
                    } else if (d.type === 'division') {
                        radius = 34;
                        fillColor = '#7F7F7F';
                        iconText = '●';
                        textDy = '2.2em';
                    }

                    if (isChief) {
                        radius = 36;
                        fillColor = '#FFD700';
                        strokeColor = '#DAA520';
                        iconColor = '#060606';
                        iconSize = '18px';
                        iconText = '👨‍💼';
                        textDy = '2.3em';
                        textSize = '11px';
                        textWeight = 'bold';
                    }

                    // Сохраняем оригинальные цвета и радиус в данных узла
                    d.originalFill = fillColor;
                    d.originalStroke = strokeColor;
                    d.originalRadius = radius;

                    // ДОБАВЛЯЕМ TITLE ДЛЯ ОБЫЧНЫХ УЗЛОВ
                    nodeGroup.append('title')
                        .text(() => {
                            let titleText = '';
                            if (d.type === 'commissariat') {
                                titleText = `Комиссариат: ${d.name}`;
                            } else if (d.type === 'department') {
                                titleText = `Отдел: ${d.name}`;
                            } else if (d.type === 'division') {
                                titleText = `Отделение: ${d.name}`;
                            } else if (d.type === 'employee') {
                                if (isChief) {
                                    titleText = `Начальник: ${d.name}`;
                                } else {
                                    titleText = `Сотрудник: ${d.name}`;
                                }
                            }
                            return titleText;
                        });

                    nodeGroup.append('circle')
                        .attr('r', radius)
                        .attr('fill', fillColor)
                        .attr('stroke', strokeColor)
                        .attr('stroke-width', 2.5)
                        .attr('filter', 'url(#shadow)');

                    nodeGroup.append('text')
                        .attr('text-anchor', 'middle')
                        .attr('dy', '0.35em')
                        .attr('fill', iconColor)
                        .style('font-size', iconSize)
                        .style('font-weight', 'bold')
                        .text(iconText);

                    nodeGroup.append('text')
                        .attr('text-anchor', 'middle')
                        .attr('dy', textDy)
                        .attr('fill', '#060606')
                        .style('font-size', textSize)
                        .style('font-weight', textWeight)
                        .text(() => {
                            let name = d.name;
                            if (name.length > 20) name = name.substring(0, 17) + '...';
                            return name;
                        });
                }
            });

            this.nodeElements.on('click', (event, d) => {
                event.stopPropagation();
                if (d.url) {
                    // Добавляем back_url если его нет
                    let url = d.url;
                    if (!url.includes('back_url')) {
                        const separator = url.includes('?') ? '&' : '?';
                        url = `${url}${separator}back_url=${encodeURIComponent(this.backUrl || window.location.href)}`;
                    }
                    window.location.href = url;
                } else if (d.type !== 'group') {
                    this.selectedNode = d;
                }
            });

            this.nodeElements.filter(d => d.type !== 'group')
                .on('mouseenter', function (event, d) {
                    const isChief = d.isChief === true;
                    let newRadius = 34;
                    if (d.type === 'commissariat') newRadius = 55;
                    else if (d.type === 'department') newRadius = 46;
                    else if (d.type === 'division') newRadius = 40;
                    else if (isChief) newRadius = 42;
                    else newRadius = 34;

                    d3.select(this).select('circle')
                        .transition().duration(200)
                        .attr('r', newRadius)
                        .attr('stroke-width', 3.5);
                }).on('mouseleave', function (event, d) {
                    const isChief = d.isChief === true;
                    let newRadius = 28;
                    if (d.type === 'commissariat') newRadius = 48;
                    else if (d.type === 'department') newRadius = 40;
                    else if (d.type === 'division') newRadius = 34;
                    else if (isChief) newRadius = 36;
                    else newRadius = 28;

                    d3.select(this).select('circle')
                        .transition().duration(200)
                        .attr('r', newRadius)
                        .attr('stroke-width', 2.5);
                });

            this.nodeElements.filter(d => d.type === 'group')
                .on('mouseenter', function () {
                    d3.select(this).select('rect')
                        .transition().duration(200)
                        .attr('width', 100)
                        .attr('height', 46)
                        .attr('x', -50)
                        .attr('y', -23)
                        .attr('stroke-width', 3);
                }).on('mouseleave', function () {
                    d3.select(this).select('rect')
                        .transition().duration(200)
                        .attr('width', 90)
                        .attr('height', 40)
                        .attr('x', -45)
                        .attr('y', -20)
                        .attr('stroke-width', 2);
                });
        },

        fitToScreen() {
            if (!this.nodeElements) return;

            let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
            this.nodeElements.each(function (d) {
                minX = Math.min(minX, d.x);
                minY = Math.min(minY, d.y);
                maxX = Math.max(maxX, d.x);
                maxY = Math.max(maxY, d.y);
            });

            const padding = 100;
            const scaleX = this.width / (maxX - minX + padding * 2);
            const scaleY = this.height / (maxY - minY + padding * 2);
            const scale = Math.min(scaleX, scaleY, 1.2);
            const translateX = (this.width - (minX + maxX) * scale) / 2;
            const translateY = (this.height - (minY + maxY) * scale) / 2;

            const transform = d3.zoomIdentity.translate(translateX, translateY).scale(scale);
            this.svg.transition().duration(500).call(this.zoomBehavior.transform, transform);
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

        startZoomIn() {
            this.zoomIn();
            this.zoomInterval = setInterval(() => {
                this.zoomIn();
            }, 50);
        },

        startZoomOut() {
            this.zoomOut();
            this.zoomInterval = setInterval(() => {
                this.zoomOut();
            }, 50);
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
                const backUrl = encodeURIComponent(this.backUrl || window.location.href);
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
                const backUrl = encodeURIComponent(this.backUrl || window.location.href);
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
        },

        searchNodes() {
            const query = this.searchQuery.toLowerCase().trim();

            if (!query) {
                this.searchResults = [];
                this.clearHighlight();
                return;
            }

            // Ищем среди всех узлов
            this.searchResults = this.nodes.filter(node => {
                return node.name.toLowerCase().includes(query);
            });

            // Подсвечиваем найденные узлы
            this.highlightNodes(this.searchResults);
        },

        highlightNodes(nodesToHighlight) {
            // Сначала сбрасываем все подсветки
            this.clearHighlight();

            // Сохраняем оригинальные цвета и подсвечиваем найденные
            nodesToHighlight.forEach(node => {
                const nodeElement = this.nodeElements?.filter(d => d.id === node.id);
                if (nodeElement && nodeElement.size() > 0) {
                    // Сохраняем оригинальный цвет если еще не сохранен
                    if (!this.originalNodeColors.has(node.id)) {
                        const circle = nodeElement.select('circle');
                        this.originalNodeColors.set(node.id, {
                            fill: circle.attr('fill'),
                            stroke: circle.attr('stroke'),
                            r: circle.attr('r')
                        });
                    }

                    // Подсвечиваем ярким цветом
                    nodeElement.select('circle')
                        .transition().duration(200)
                        .attr('fill', '#ffeb3b') // Ярко-желтый
                        .attr('stroke', '#ff9800')
                        .attr('stroke-width', 4)
                        .attr('r', d => {
                            const currentR = parseInt(d3.select(this).attr('r'));
                            return currentR + 5;
                        });

                    // Добавляем пульсацию
                    nodeElement.select('circle')
                        .transition().duration(500)
                        .attr('r', d => {
                            const currentR = parseInt(d3.select(this).attr('r'));
                            return currentR - 2;
                        })
                        .transition().duration(500)
                        .attr('r', d => {
                            const currentR = parseInt(d3.select(this).attr('r'));
                            return currentR + 2;
                        });
                }
            });
        },

        clearHighlight() {
            // Восстанавливаем оригинальные цвета у всех подсвеченных узлов
            this.originalNodeColors.forEach((originalColor, nodeId) => {
                const nodeElement = this.nodeElements?.filter(d => d.id === nodeId);
                if (nodeElement && nodeElement.size() > 0) {
                    nodeElement.select('circle')
                        .transition().duration(200)
                        .attr('fill', originalColor.fill)
                        .attr('stroke', originalColor.stroke)
                        .attr('stroke-width', 2.5)
                        .attr('r', originalColor.r);
                }
            });
            this.originalNodeColors.clear();
        },

        clearSearch() {
            this.searchQuery = '';
            this.searchResults = [];
            this.clearHighlight();
        },

        focusOnNode(node) {
            const nodeElement = this.nodeElements?.filter(d => d.id === node.id);
            if (nodeElement && nodeElement.size() > 0) {
                const nodeData = nodeElement.data()[0];
                const x = nodeData.x;
                const y = nodeData.y;
                const scale = this.zoomBehavior.scaleExtent()[1];
                const transform = d3.zoomIdentity
                    .translate(this.width / 2 - x * scale, this.height / 2 - y * scale)
                    .scale(scale);
                this.svg.transition()
                    .duration(500)
                    .call(this.zoomBehavior.transform, transform);
                this.highlightNodes([node]);
                setTimeout(() => {
                    if (this.searchQuery === '') {
                        this.clearHighlight();
                    }
                }, 3000);
            }
        },

        shouldShowNode(node) {
            // Комиссариат всегда показываем
            if (node.type === 'commissariat') {
                return true;
            }

            // Отделы
            if (node.type === 'department') {
                return this.filters.departments;
            }

            // Отделения (обычные) - не самостоятельные и не группы
            if (node.type === 'division' && !node.id.includes('independent') && node.type !== 'group') {
                return this.filters.divisions;
            }

            // Отделения (самостоятельные)
            if (node.type === 'division' && node.id.includes('independent')) {
                return this.filters.independentDivisions;
            }

            // Группы (контейнеры сотрудников) - показываем если есть видимые сотрудники
            if (node.type === 'group') {
                // Проверяем, есть ли у этой группы видимые сотрудники
                const hasVisibleEmployees = this.nodes.some(emp => {
                    if (emp.type !== 'employee') return false;
                    const isLinkedToGroup = this.links.some(link => link.source === node.id && link.target === emp.id);
                    return isLinkedToGroup && this.shouldShowNode(emp);
                });
                return hasVisibleEmployees;
            }

            // Сотрудники
            if (node.type === 'employee') {
                const isChief = node.isChief === true;
                const isIndependent = node.id && node.id.includes('independent');

                // Проверяем включен ли общий фильтр сотрудников
                if (!this.filters.employees && !isChief && !isIndependent) {
                    return false;
                }

                // Самостоятельные сотрудники
                if (isIndependent) {
                    return this.filters.independentEmployees;
                }

                // Начальники
                if (isChief) {
                    return this.filters.chiefs;
                }

                // Обычные сотрудники (не начальники и не самостоятельные)
                if (!isChief && !isIndependent) {
                    return this.filters.regularEmployees;
                }
            }

            return true;
        },

        applyFilters() {
            if (!this.nodeElements) return;

            let visibleCount = 0;

            this.nodeElements.each((d, i, group) => {
                const nodeGroup = d3.select(group[i]);
                const shouldShow = this.shouldShowNode(d);

                if (shouldShow) {
                    nodeGroup.style('opacity', 1);
                    nodeGroup.style('display', null);
                    visibleCount++;

                    // Восстанавливаем оригинальные цвета
                    const circle = nodeGroup.select('circle');
                    if (d.originalFill && circle.size()) {
                        circle.attr('fill', d.originalFill)
                            .attr('stroke', d.originalStroke)
                            .attr('stroke-width', 2.5);
                    }
                    // Для групп (прямоугольников)
                    const rect = nodeGroup.select('rect');
                    if (rect.size() && d.type === 'group') {
                        rect.attr('fill', this.getNodeColor(d.type));
                    }
                } else {
                    nodeGroup.style('opacity', 0.15);
                }
            });

            this.visibleNodesCount = visibleCount;

            // Обновляем связи - ПОЛНОСТЬЮ ВОССТАНАВЛИВАЕМ все линии, а потом затемняем ненужные
            if (this.linkElements) {
                this.linkElements.each((d, i, group) => {
                    const linkGroup = d3.select(group[i]);
                    const sourceVisible = this.shouldShowNode(d.source);
                    const targetVisible = this.shouldShowNode(d.target);

                    // Сначала восстанавливаем оригинальный стиль
                    linkGroup.attr('stroke', '#999')
                        .attr('stroke-width', 1.5);

                    // Затем затемняем если нужно
                    if (sourceVisible && targetVisible) {
                        linkGroup.style('opacity', 0.4);
                    } else {
                        linkGroup.style('opacity', 0.05);
                    }
                });
            }
        },

        updateVisibleCount() {
            let count = 0;
            if (this.nodeElements) {
                this.nodeElements.each((d) => {
                    if (this.shouldShowNode(d)) {
                        count++;
                    }
                });
            }
            this.visibleNodesCount = count;
        },

        resetFilters() {
            this.filters = {
                departments: true,
                divisions: true,
                independentDivisions: true,
                employees: true,
                regularEmployees: true,
                independentEmployees: true,
                chiefs: true,
            };
            this.applyFilters();
        },
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


/* Управление */
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
    width: 320px;
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
    padding: 14px 18px;
    color: white;
}

.modal-header h3 {
    margin: 0;
    font-size: 16px;
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
    padding: 18px;
}

.modal-body p {
    margin: 8px 0;
    color: #060606;
}

.modal-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 16px;
}

.btn-add,
.btn-cancel {
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

/* Поиск */
.search-panel {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 100;
    width: 320px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    backdrop-filter: blur(8px);
    background: rgba(255, 255, 255, 0.95);
}

.search-header {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    gap: 8px;
    border-bottom: 1px solid #e0e0e0;
}

.search-icon {
    width: 18px;
    height: 18px;
    color: #A60644;
    flex-shrink: 0;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
    padding: 8px 0;
    background: transparent;
}

.search-input::placeholder {
    color: #999;
}

.search-clear {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #999;
    padding: 0 4px;
    transition: color 0.2s;
}

.search-clear:hover {
    color: #A60644;
}

.search-results {
    max-height: 300px;
    overflow-y: auto;
    border-top: 1px solid #e0e0e0;
}

.search-result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid #f0f0f0;
}

.search-result-item:hover {
    background: #f5f5f5;
}

.result-type {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 20px;
    background: #f0f0f0;
    color: #565A5B;
    font-weight: 500;
    flex-shrink: 0;
}

.result-name {
    font-size: 13px;
    color: #060606;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.search-no-results {
    padding: 20px;
    text-align: center;
    color: #999;
    font-size: 13px;
}


/* Панель фильтров */
.filters-panel {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 100;
    width: 260px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    backdrop-filter: blur(8px);
    background: rgba(255, 255, 255, 0.95);
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #A60644;
    color: white;
    cursor: pointer;
    transition: background 0.2s;
}

.filters-header:hover {
    background: #6b0229;
}

.filters-header h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
}

.filters-toggle {
    width: 16px;
    height: 16px;
    transition: transform 0.3s;
}

.filters-toggle.rotated {
    transform: rotate(180deg);
}

.filters-content {
    padding: 12px;
    border-top: 1px solid #e0e0e0;
    max-height: 400px;
    overflow-y: auto;
}

.filter-group {
    margin-bottom: 16px;
}

.filter-title {
    font-size: 12px;
    font-weight: 600;
    color: #A60644;
    margin-bottom: 8px;
    padding-bottom: 4px;
    border-bottom: 1px solid #e0e0e0;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    cursor: pointer;
    font-size: 13px;
    color: #060606;
    transition: color 0.2s;
}

.filter-checkbox:hover {
    color: #A60644;
}

.filter-checkbox input {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #A60644;
}

.filter-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    margin-top: 8px;
    border-top: 1px solid #e0e0e0;
}

.filter-reset {
    padding: 6px 12px;
    background: #f0f0f0;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    color: #060606;
    transition: all 0.2s;
}

.filter-reset:hover {
    background: #A60644;
    color: white;
}

.filter-count {
    font-size: 12px;
    color: #565A5B;
}

/* Анимация для затемнения */
.node-fade-enter-active,
.node-fade-leave-active {
    transition: opacity 0.3s ease;
}

.node-fade-enter,
.node-fade-leave-to {
    opacity: 0;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    cursor: pointer;
    font-size: 13px;
    color: #060606;
    transition: color 0.2s;
}

.filter-count-badge {
    margin-left: auto;
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    color: #565A5B;
    min-width: 28px;
    text-align: center;
}
</style>