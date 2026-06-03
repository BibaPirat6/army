{{-- Модальное окно статистики --}}
<div id="statsModal" class="fixed inset-0 z-[999] hidden overflow-y-auto" aria-hidden="true">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeStatsModal()"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-800">Статистика задач по подразделениям</h3>
                <button type="button" onclick="closeStatsModal()"
                    class="text-gray-400 hover:text-gray-600 transition p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Комиссариаты --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Комиссариат</label>
                <div class="relative" id="statsCommissariatBlock">
                    <div class="relative">
                        <input type="text" id="statsCommissariatSearch" placeholder="Поиск по комиссариатам..."
                            autocomplete="off"
                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <button id="clearSearchBtn" type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition p-1 rounded-full hover:bg-gray-100 hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <ul id="statsCommissariatList"
                        class="absolute left-0 right-0 z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto hidden">
                    </ul>
                </div>
            </div>

            {{-- Результат --}}
            <div id="statsResult" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                <h4 class="font-medium text-gray-700 mb-2">Сводка</h4>
                <div id="statsResultContent" class="space-y-2 text-sm"></div>
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeStatsModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let statsData = @json($taskStats);

    const commissariatSearch = document.getElementById('statsCommissariatSearch');
    const commissariatList = document.getElementById('statsCommissariatList');
    const statsResult = document.getElementById('statsResult');
    const statsResultContent = document.getElementById('statsResultContent');
    const clearSearchBtn = document.getElementById('clearSearchBtn');

    async function refreshStatsData() {
        try {
            const response = await fetch('/calendar/stats');
            if (response.ok) {
                statsData = await response.json();
                if (!document.getElementById('statsModal').classList.contains('hidden') && commissariatSearch.value) {
                    const selected = statsData.find(c => c.name === commissariatSearch.value);
                    if (selected) {
                        commissariatSearch.value = selected.name;
                        showStatsResult(selected);
                    }
                }
            }
        } catch (e) {
            console.error('Ошибка обновления статистики:', e);
        }
    }

    function renderCommissariatList(filter = '') {
        const q = filter.toLowerCase().trim();
        commissariatList.innerHTML = '';
        const filtered = statsData.filter(c => !q || c.name.toLowerCase().includes(q));
        
        if (filtered.length === 0) {
            commissariatList.innerHTML = '<div class="px-4 py-2 text-gray-400 text-sm">Ничего не найдено</div>';
        } else {
            filtered.forEach(c => {
                const li = document.createElement('li');
                li.className = 'px-4 py-2 cursor-pointer hover:bg-indigo-50 flex justify-between items-center';
                li.innerHTML = `<span>${c.name}</span><span class="text-xs text-gray-500">Задач: ${c.total}</span>`;
                li.addEventListener('click', () => {
                    commissariatSearch.value = c.name;
                    commissariatList.classList.add('hidden');
                    showStatsResult(c);
                    updateClearButtonVisibility();
                });
                commissariatList.appendChild(li);
            });
        }
    }

    function showStatsResult(c) {
        statsResult.classList.remove('hidden');
        let html = `
            <div class="flex justify-between"><span class="text-gray-500">🏛 Комиссариат:</span><span class="font-medium">${c.name}</span></div>
            <div class="flex justify-between border-t pt-1 mt-1"><span class="font-semibold text-gray-700">Общее количество задач:</span><span class="font-bold text-indigo-700">${c.total}</span></div>
        `;

        if (c.total > 0) {
            html += `
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <a href="/calendar/matrix/${c.id}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Матрица сотрудников
                    </a>
                </div>
            `;
        }

        statsResultContent.innerHTML = html;
    }

    function updateClearButtonVisibility() {
        if (commissariatSearch.value.trim() !== '') {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }
    }

    function clearSearch() {
        commissariatSearch.value = '';
        statsResult.classList.add('hidden');
        statsResultContent.innerHTML = '';
        updateClearButtonVisibility();
        renderCommissariatList('');
        commissariatList.classList.remove('hidden');
        commissariatSearch.focus();
    }

    // Обработчик для кнопки очистки
    clearSearchBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        clearSearch();
    });

    commissariatSearch.addEventListener('focus', () => {
        commissariatList.classList.remove('hidden');
        renderCommissariatList(commissariatSearch.value);
        updateClearButtonVisibility();
    });
    
    commissariatSearch.addEventListener('input', () => {
        commissariatList.classList.remove('hidden');
        renderCommissariatList(commissariatSearch.value);
        updateClearButtonVisibility();
    });

    document.addEventListener('click', (e) => {
        if (!document.getElementById('statsCommissariatBlock')?.contains(e.target)) {
            commissariatList.classList.add('hidden');
        }
    });

    window.refreshStatsData = refreshStatsData;
})();
</script>