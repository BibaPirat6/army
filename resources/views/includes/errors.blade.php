@if ($errors->any())
    <div id="error-alert" style="
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 400px;
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            border-left: 5px solid #ef4444;
            color: #991b1b;
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
            Ошибки в форме:
        </strong>

        <ul style="margin: 0 0 8px 20px; padding: 0; list-style-type: disc;">
            @foreach ($errors->all() as $error)
                <li style="margin-bottom: 6px;">{{ $error }}</li>
            @endforeach
        </ul>

        <button type="button" onclick="closeErrorAlert()" style="
                        position: absolute;
                        top: 12px;
                        right: 16px;
                        background: none;
                        border: none;
                        font-size: 1.6em;
                        line-height: 1;
                        color: #991b1b;
                        cursor: pointer;
                        padding: 0;
                    ">×</button>
    </div>

    <script>
  
        setTimeout(() => {
            const alert = document.getElementById('error-alert');
            if (alert) {
                alert.style.opacity = '1';
                alert.style.transform = 'translateY(0)';
            }
        }, 100);

     
        setTimeout(() => {
            closeErrorAlert();
        }, 10000);

       
        function closeErrorAlert() {
            const alert = document.getElementById('error-alert');
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