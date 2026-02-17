@if ($errors->any())
    <div id="error-alert" class="fixed top-4 right-4 z-50 max-w-md w-full bg-red-600 text-white rounded-lg shadow-lg p-4 opacity-90 transition-all">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="font-bold mb-2">Ошибки в форме</h3>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button onclick="closeErrorAlert()" class="ml-4 text-white hover:text-red-200">&times;</button>
        </div>
    </div>

    <script>
        function closeErrorAlert() {
            const alert = document.getElementById('error-alert');
            if (!alert) return;
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            setTimeout(() => alert.remove(), 300);
        }

        // авто-скрытие через 5 секунд
        setTimeout(closeErrorAlert, 5000);
    </script>
@endif
