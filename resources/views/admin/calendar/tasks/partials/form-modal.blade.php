{{-- Модальное окно задачи (создание/редактирование) --}}
<div id="taskModal" class="fixed inset-0 z-[999] hidden overflow-y-auto" aria-hidden="true">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" data-modal-overlay></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">Новая задача</h3>
                <button type="button" onclick="closeTaskModal()"
                    class="text-gray-400 hover:text-gray-600 transition p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="taskForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="task_id" name="id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                    <input type="text" id="title" name="title" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                    <textarea id="description" name="description" rows="2"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ответственный</label>
                    <select name="employee_position_id" id="employee_position_id"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Не назначен</option>
                        @foreach($employeePositions ?? [] as $ep)
                            <option value="{{ $ep->id }}">
                                {{ $ep->employee?->person?->фамилия }} {{ $ep->employee?->person?->имя }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Цвет</label>
                        <input type="color" id="color" name="color" value="#3788d8"
                            class="h-10 w-full rounded-lg border-gray-300 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Квота</label>
                        <input type="number" id="quota" name="quota" min="1" value="1"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Дата начала *</label>
                        <input type="date" id="start_date" name="start_date" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания *</label>
                        <input type="date" id="end_date" name="end_date" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeTaskModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Отмена</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>