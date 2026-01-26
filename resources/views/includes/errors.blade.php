@if ($errors->any())
    <div class="fixed top-4 right-4 z-50 max-w-md w-full">
        <div
            class="bg-gradient-to-r from-red-500 via-red-600 to-red-700 opacity-90 hover:opacity-100 transition-opacity duration-300 rounded-xl shadow-2xl border border-red-300 transform hover:-translate-y-1 transition-transform duration-300">
            <div class="p-5">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h3 class="text-white font-bold text-lg mb-2">Ошибки в форме</h3>
                            <button type="button"
                                onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                                class="text-white hover:text-red-200 focus:outline-none transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <ul class="space-y-1.5">
                            @foreach ($errors->all() as $error)
                                <li
                                    class="text-red-100 text-sm bg-red-600/30 px-3 py-2 rounded-lg border border-red-400/30">
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
        setTimeout(function() {
            const errorAlert = document.querySelector('div[class*="from-red-500"]');
            if (errorAlert) {
                errorAlert.style.opacity = '0';
                errorAlert.style.transform = 'translateX(100%)';
                setTimeout(() => errorAlert.remove(), 300);
            }
        }, 5000);
    </script>
@endif
