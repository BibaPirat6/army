{{-- Модальное окно подзадачи --}}
<div id="subtaskModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-gray-900/50" onclick="closeSubtaskModal()"></div>
    <div class="flex items-center justify-center min-h-full p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-medium text-gray-800 mb-4" id="subtaskModalTitle">Добавить подзадачу</h3>
            
            <form id="subtaskForm">
                @csrf
                <input type="hidden" id="subtask_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                    <input type="text" id="subtask_title" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Мин (мин)</label>
                        <input type="number" id="subtask_min" required min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Сред (мин)</label>
                        <input type="number" id="subtask_avg" required min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Макс (мин)</label>
                        <input type="number" id="subtask_max" required min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeSubtaskModal()"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Отмена</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>