/**
 * StatsModal
 * 
 * Управление модальным окном статистики по комиссариатам
 * Изолированный модуль с собственной логикой
 */
export class StatsModal {
    constructor() {
        this.statsData = [];
        this.selectedCommissariat = null;
        this.isLoading = false;
        this.error = null;
        this.debounceTimer = null;
        this.DEBOUNCE_DELAY = 300;

        // DOM элементы (инициализируются при открытии)
        this.elements = {};
        
        // Привязка методов
        this.handleSearchInput = this.handleSearchInput.bind(this);
        this.handleClickOutside = this.handleClickOutside.bind(this);
        this.handleKeydown = this.handleKeydown.bind(this);
    }

    /**
     * Инициализация DOM элементов
     */
    cacheElements() {
        this.elements = {
            modal: document.getElementById('statsModal'),
            search: document.getElementById('statsCommissariatSearch'),
            list: document.getElementById('statsCommissariatList'),
            result: document.getElementById('statsResult'),
            resultContent: document.getElementById('statsResultContent'),
            clearBtn: document.getElementById('clearSearchBtn'),
            loadingIndicator: document.getElementById('statsLoadingIndicator'),
            errorMessage: document.getElementById('statsErrorMessage'),
            emptyState: document.getElementById('statsEmptyState'),
            block: document.getElementById('statsCommissariatBlock'),
        };
    }

    /**
     * Открыть модальное окно
     */
    async open() {
        this.cacheElements();
        
        const modal = this.elements.modal;
        if (!modal) return;

        // Показываем модальное окно
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        
        // Блокируем скролл
        document.body.style.overflow = 'hidden';
        
        // Загружаем данные если еще не загружены
        if (this.statsData.length === 0) {
            await this.loadData();
        }

        // Показываем список при открытии
        this.showAllCommissariats();
        
        // Фокусируемся на поле поиска
        setTimeout(() => {
            this.elements.search?.focus();
        }, 100);

        // Добавляем глобальные слушатели
        document.addEventListener('click', this.handleClickOutside);
        document.addEventListener('keydown', this.handleKeydown);
    }

    /**
     * Закрыть модальное окно
     */
    close() {
        const modal = this.elements.modal;
        if (!modal) return;

        // Анимация закрытия
        modal.classList.add('modal-closing');
        
        const onAnimationEnd = () => {
            modal.classList.add('hidden');
            modal.classList.remove('modal-closing');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            
            // Очищаем состояние
            this.resetState();
        };

        modal.addEventListener('animationend', onAnimationEnd, { once: true });
        
        // Удаляем глобальные слушатели
        document.removeEventListener('click', this.handleClickOutside);
        document.removeEventListener('keydown', this.handleKeydown);
    }

    /**
     * Загрузка данных с сервера
     */
    async loadData() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const response = await fetch('/calendar/stats', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            this.statsData = await response.json();
            this.error = null;
            
        } catch (error) {
            console.error('StatsModal: Ошибка загрузки данных:', error);
            this.error = 'Не удалось загрузить данные. Попробуйте позже.';
            this.statsData = [];
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }

    /**
     * Показать все комиссариаты
     */
    showAllCommissariats() {
        this.elements.search.value = '';
        this.hideResult();
        this.renderList(this.statsData);
        this.showList();
    }

    /**
     * Отрисовка списка комиссариатов
     */
    renderList(items) {
        const list = this.elements.list;
        if (!list) return;

        list.innerHTML = '';

        // Состояние ошибки
        if (this.error) {
            list.innerHTML = `
                <div class="px-4 py-6 text-center">
                    <div class="text-red-500 mb-2">
                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-red-600 text-sm mb-3">${this.escapeHtml(this.error)}</p>
                    <button onclick="statsModal.loadData()" class="text-indigo-600 text-sm hover:underline">
                        Попробовать снова
                    </button>
                </div>`;
            return;
        }

        // Состояние пустого результата
        if (items.length === 0) {
            list.innerHTML = `
                <div class="px-4 py-6 text-center">
                    <div class="text-gray-400 mb-2">
                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">Ничего не найдено</p>
                </div>`;
            return;
        }

        // Рендер элементов списка
        const fragment = document.createDocumentFragment();
        
        items.forEach(commissariat => {
            const li = document.createElement('li');
            li.className = 'px-4 py-3 cursor-pointer hover:bg-indigo-50 transition-colors duration-150 flex items-center justify-between border-b border-gray-100 last:border-b-0';
            
            // Подсветка выбранного элемента
            if (this.selectedCommissariat?.id === commissariat.id) {
                li.classList.add('bg-indigo-50', 'border-l-4', 'border-l-indigo-600');
            }
            
            li.innerHTML = `
                <div class="flex-1 min-w-0">
                    <span class="font-medium text-gray-800">${this.escapeHtml(commissariat.name)}</span>
                    ${commissariat.department ? `<p class="text-xs text-gray-500 mt-0.5 truncate">${this.escapeHtml(commissariat.department)}</p>` : ''}
                </div>
                <div class="flex items-center gap-3 ml-3 shrink-0">
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                        ${this.escapeHtml(String(commissariat.total))} задач
                    </span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            `;
            
            li.addEventListener('click', () => this.selectCommissariat(commissariat));
            fragment.appendChild(li);
        });

        list.appendChild(fragment);
    }

    /**
     * Выбор комиссариата
     */
    selectCommissariat(commissariat) {
        this.selectedCommissariat = commissariat;
        this.elements.search.value = commissariat.name;
        this.hideList();
        this.showResult(commissariat);
        this.updateClearButtonVisibility();
    }

    /**
     * Показать результат для выбранного комиссариата
     */
    showResult(commissariat) {
        const result = this.elements.result;
        const content = this.elements.resultContent;
        
        if (!result || !content) return;

        result.classList.remove('hidden');
        
        content.innerHTML = `
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-500 text-sm">Комиссариат</span>
                <span class="font-semibold text-gray-800">${this.escapeHtml(commissariat.name)}</span>
            </div>
            <div class="border-t border-gray-200 my-2"></div>
            <div class="flex justify-between items-center py-2">
                <span class="font-semibold text-gray-700">Всего задач</span>
                <span class="text-2xl font-bold text-indigo-600">${commissariat.total}</span>
            </div>
            ${commissariat.total > 0 ? `
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="/calendar/matrix/${commissariat.id}" 
                       class="inline-flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md active:scale-[0.98]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Открыть матрицу сотрудников
                    </a>
                </div>
            ` : ''}
            <button onclick="statsModal.clearSelection()" 
                    class="mt-3 w-full px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                Выбрать другой комиссариат
            </button>
        `;
    }

    /**
     * Очистить выбор
     */
    clearSelection() {
        this.selectedCommissariat = null;
        this.hideResult();
        this.elements.search.value = '';
        this.showAllCommissariats();
        this.elements.search?.focus();
    }

    /**
     * Обработчик ввода в поле поиска (с debounce)
     */
    handleSearchInput(event) {
        const query = event.target.value.trim();
        this.updateClearButtonVisibility();
        this.hideResult();
        
        // Очищаем предыдущий таймер
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        
        // Устанавливаем новый debounce таймер
        this.debounceTimer = setTimeout(() => {
            this.filterAndRender(query);
        }, this.DEBOUNCE_DELAY);
    }

    /**
     * Фильтрация и отрисовка списка
     */
    filterAndRender(query) {
        const q = query.toLowerCase();
        const filtered = this.statsData.filter(c => 
            c.name.toLowerCase().includes(q) || 
            (c.department && c.department.toLowerCase().includes(q))
        );
        this.renderList(filtered);
        this.showList();
    }

    /**
     * Показать список
     */
    showList() {
        if (this.elements.list) {
            this.elements.list.classList.remove('hidden');
        }
    }

    /**
     * Скрыть список
     */
    hideList() {
        if (this.elements.list) {
            this.elements.list.classList.add('hidden');
        }
    }

    /**
     * Показать результат
     */
    hideResult() {
        if (this.elements.result) {
            this.elements.result.classList.add('hidden');
        }
        if (this.elements.resultContent) {
            this.elements.resultContent.innerHTML = '';
        }
    }

    /**
     * Обновить видимость кнопки очистки
     */
    updateClearButtonVisibility() {
        if (!this.elements.clearBtn) return;
        
        if (this.elements.search.value.trim() !== '') {
            this.elements.clearBtn.classList.remove('hidden');
        } else {
            this.elements.clearBtn.classList.add('hidden');
        }
    }

    /**
     * Показать индикатор загрузки
     */
    showLoading() {
        // Можно добавить скелетон в список
        if (this.elements.list) {
            this.elements.list.innerHTML = `
                <div class="p-4 space-y-3">
                    ${Array(3).fill(0).map(() => `
                        <div class="animate-pulse flex items-center gap-3">
                            <div class="h-4 bg-gray-200 rounded flex-1"></div>
                            <div class="h-4 bg-gray-200 rounded w-16"></div>
                        </div>
                    `).join('')}
                </div>`;
        }
    }

    /**
     * Скрыть индикатор загрузки
     */
    hideLoading() {
        // Очищаем скелетон, перерисовываем список
        if (this.statsData.length > 0) {
            this.showAllCommissariats();
        }
    }

    /**
     * Сброс состояния
     */
    resetState() {
        this.selectedCommissariat = null;
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = null;
        }
        if (this.elements.search) {
            this.elements.search.value = '';
        }
        this.hideResult();
        this.hideList();
    }

    /**
     * Обработчик клика вне компонента
     */
    handleClickOutside(event) {
        if (this.elements.block && !this.elements.block.contains(event.target)) {
            this.hideList();
        }
    }

    /**
     * Обработчик клавиатуры
     */
    handleKeydown(event) {
        if (event.key === 'Escape') {
            if (this.elements.list && !this.elements.list.classList.contains('hidden')) {
                this.hideList();
            } else {
                this.close();
            }
        }
    }

    /**
     * Безопасное экранирование HTML
     * Защита от XSS атак
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Уничтожение модуля
     */
    destroy() {
        this.close();
        document.removeEventListener('click', this.handleClickOutside);
        document.removeEventListener('keydown', this.handleKeydown);
        this.statsData = [];
        this.selectedCommissariat = null;
    }
}