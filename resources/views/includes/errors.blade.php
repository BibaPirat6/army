@if ($errors->any())
    <div class="fixed top-4 right-4 z-[999] max-w-md w-full" id="error-alert-container">
        <div
            class="transition-opacity transition-transform duration-300 transform border border-red-300 shadow-2xl bg-gradient-to-r from-red-500 via-red-600 to-red-700 opacity-90 hover:opacity-100 rounded-xl hover:-translate-y-1">
            <div class="p-5">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <h3 class="mb-2 text-lg font-bold text-white">Ошибки в форме</h3>
                            <button type="button" onclick="closeAlert(this)"
                                class="text-white transition-colors hover:text-red-200 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <ul class="space-y-1.5">
                            @foreach ($errors->all() as $error)
                                <li
                                    class="px-3 py-2 text-sm text-red-100 border rounded-lg bg-red-600/30 border-red-400/30">
                                    <div class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span>{{ $error }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.closeAlert = function(button) {
            const alertContainer = button.closest('.fixed');
            if (alertContainer) {
                fadeAndRemove(alertContainer);
            }
        };

        window.fadeAndRemove = function(element) {
            element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            element.style.opacity = '0';
            element.style.transform = 'translateX(100%)';
            setTimeout(() => element.remove(), 300);
        };

        setTimeout(function() {
            const errorAlertContainer = document.getElementById('error-alert-container');
            if (errorAlertContainer) {
                fadeAndRemove(errorAlertContainer);
            }
        }, 5000);
    </script>
@endif
