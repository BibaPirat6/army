@if (session('success'))
    <div id="success-alert" style="
        position: fixed;
        top: 20px;
        right: 20px;
        max-width: 400px;
        background-color: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-left: 5px solid #10b981;
        color: #065f46;
        padding: 16px 20px;
        margin: 0;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        font-family: system-ui, -apple-system, sans-serif;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.4s ease-out;
    ">
        <strong style="display: block; margin-bottom: 10px; font-size: 1.1em;">
            Успешно!
        </strong>

        <div style="margin: 0;">
            {{ session('success') }}
        </div>

        <button type="button"
                onclick="closeSuccessAlert()"
                style="
                    position: absolute;
                    top: 12px;
                    right: 16px;
                    background: none;
                    border: none;
                    font-size: 1.6em;
                    line-height: 1;
                    color: #065f46;
                    cursor: pointer;
                    padding: 0;
                ">×</button>
    </div>

    <script>
     
        setTimeout(() => {
            const alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.opacity = '1';
                alert.style.transform = 'translateY(0)';
            }
        }, 100);

     
        setTimeout(() => {
            closeSuccessAlert();
        }, 10000);

      
        function closeSuccessAlert() {
            const alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alert.remove();
                }, 400); 
            }
        }
    </script>
@endif