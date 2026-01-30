@if (session('success'))
    <div class="fixed top-4 right-4 z-[999] max-w-md w-full" id="alert-container">
        <div
            class="transition-opacity transition-transform duration-300 transform border shadow-2xl bg-gradient-to-r from-emerald-500 via-emerald-600 to-emerald-700 opacity-90 hover:opacity-100 rounded-xl border-emerald-300 hover:-translate-y-1">
            <div class="p-5">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <h3 class="mb-2 text-lg font-bold text-white">Успешно!</h3>
                            <button type="button" onclick="closeAlert(this)"
                                class="text-white transition-colors hover:text-emerald-200 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div
                            class="px-3 py-2 text-sm border rounded-lg text-emerald-100 bg-emerald-600/30 border-emerald-400/30">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeAlert(button) {
            const alertContainer = button.closest('.fixed');
            if (alertContainer) {
                fadeAndRemove(alertContainer);
            }
        }

        function fadeAndRemove(element) {
            element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            element.style.opacity = '0';
            element.style.transform = 'translateX(100%)';
            setTimeout(() => element.remove(), 300);
        }

        setTimeout(function() {
            const alertContainer = document.getElementById('alert-container');
            if (alertContainer) {
                fadeAndRemove(alertContainer);
            }
        }, 5000);
    </script>
@endif
