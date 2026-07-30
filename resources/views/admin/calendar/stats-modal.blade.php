{{-- Модальное окно статистики по комиссариатам --}}
<div id="statsModal" 
     class="fixed inset-0 z-[999] hidden" 
     aria-hidden="true"
     role="dialog"
     aria-modal="true"
     aria-labelledby="statsModalTitle">
    
    {{-- Оверлей --}}
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300" 
         onclick="closeStatsModal()"
         aria-hidden="true"></div>
    
    {{-- Центрирование --}}
    <div class="fixed inset-0 flex items-center justify-center p-4 sm:p-6">
        
        {{-- Контейнер --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg 
                    max-h-[85vh] min-h-[500px]
                    flex flex-col
                    overflow-hidden transform transition-all duration-300 z-10">
            
            {{-- Хедер --}}
            <div class="flex-shrink-0 flex items-center justify-between p-5 sm:p-6 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 p-2.5 bg-gradient-to-br from-slate-700 to-slate-800 rounded-xl shadow-lg shadow-slate-700/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 id="statsModalTitle" class="text-lg sm:text-xl font-bold text-gray-900">
                        Статистика по подразделениям
                    </h3>
                </div>
                
                <button type="button" 
                        onclick="closeStatsModal()"
                        class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 
                               rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 
                               focus:ring-slate-500"
                        aria-label="Закрыть модальное окно">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            {{-- Тело (растягивается на всю доступную высоту) --}}
            <div class="flex-1 p-5 sm:p-6 overflow-y-auto">
                
                {{-- Поиск --}}
                <div class="mb-4">
                    <label for="statsCommissariatSearch" 
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Поиск комиссариата
                    </label>
                    
                    <div id="statsCommissariatBlock" class="relative">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            
                            <input type="text" 
                                   id="statsCommissariatSearch" 
                                   placeholder="Введите название комиссариата..."
                                   autocomplete="off"
                                   class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl 
                                          focus:ring-2 focus:ring-slate-500 focus:border-slate-500 
                                          outline-none transition-all duration-200 text-gray-800
                                          placeholder-gray-400 bg-white shadow-sm">
                            
                            <button id="clearSearchBtn" 
                                    type="button"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 
                                           hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all 
                                           duration-200 hidden"
                                    aria-label="Очистить поиск">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        <ul id="statsCommissariatList"
                            class="absolute left-0 right-0 z-50 mt-2 bg-white border border-gray-200 
                                   rounded-xl shadow-xl max-h-72 overflow-auto hidden"
                            role="listbox"
                            aria-label="Список комиссариатов">
                        </ul>
                    </div>
                </div>
                
                {{-- Результат --}}
                <div id="statsResult" 
                     class="mt-4 p-5 bg-gradient-to-br from-slate-50 to-white rounded-xl 
                            border border-gray-200 shadow-sm hidden">
                    <div id="statsResultContent" class="space-y-2"></div>
                </div>
            </div>
            
            {{-- Футер --}}
            <div class="flex-shrink-0 flex justify-end gap-3 px-5 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                <button type="button" 
                        onclick="closeStatsModal()"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 
                               hover:bg-gray-50 rounded-xl transition-all duration-200 
                               focus:outline-none focus:ring-2 focus:ring-slate-500 shadow-sm
                               active:scale-[0.98]">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Кастомный скроллбар */
    #statsCommissariatList::-webkit-scrollbar {
        width: 6px;
    }
    
    #statsCommissariatList::-webkit-scrollbar-track {
        background: transparent;
    }
    
    #statsCommissariatList::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    #statsCommissariatList::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush