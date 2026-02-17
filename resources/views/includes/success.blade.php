@if (session('success'))
    <div id="success-alert"
        class="fixed top-4 right-4 z-50 max-w-md w-full bg-emerald-600 text-white rounded-lg shadow-lg p-4 opacity-90 transition-all">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="font-bold mb-2">Успешно!</h3>
                <div class="text-sm">{{ session('success') }}</div>
            </div>
            <button onclick="closeSuccessAlert()" class="ml-4 text-white hover:text-emerald-200">&times;</button>
        </div>
    </div>

    <script>
        function closeSuccessAlert() {
            const alert = document.getElementById('success-alert');
            if (!alert) return;
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            setTimeout(() => alert.remove(), 300);
        }

        // авто-скрытие через 5 секунд
        setTimeout(closeSuccessAlert, 5000);
    </script>
@endif
