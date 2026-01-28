@if (session('success'))
    <div class="fixed top-4 right-4 z-[999] max-w-md w-full">
        <div
            class="bg-gradient-to-r from-emerald-500 via-emerald-600 to-emerald-700 opacity-90 hover:opacity-100 transition-opacity duration-300 rounded-xl shadow-2xl border border-emerald-300 transform hover:-translate-y-1 transition-transform duration-300">
            <div class="p-5">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h3 class="text-white font-bold text-lg mb-2">Успешно!</h3>
                            <button type="button"
                                onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                                class="text-white hover:text-emerald-200 focus:outline-none transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div
                            class="text-emerald-100 text-sm bg-emerald-600/30 px-3 py-2 rounded-lg border border-emerald-400/30">
                            {{ session('success') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        setTimeout(function() {
            const successAlert = document.querySelector('div[class*="from-emerald-500"]');
            if (successAlert) {
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateX(100%)';
                setTimeout(() => successAlert.remove(), 300);
            }
        }, 5000);
    </script>
@endif
