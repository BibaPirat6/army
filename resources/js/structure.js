document.addEventListener("DOMContentLoaded", () => {
    const viewport = document.getElementById("viewport");
    const canvas = document.getElementById("canvas");
    canvas.style.transformOrigin = "0 0";

    if (!viewport || !canvas) return;

    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let translateX = 0;
    let translateY = 0;
    let scale = 1;

    const MIN_SCALE = 0.01;
    const MAX_SCALE = 2;

    // === 🖱️ Простое перетаскивание мышкой ===
    
    viewport.addEventListener("mousedown", (e) => {
        // Левая кнопка мыши
        if (e.button !== 0) return;
        
        // Не драгаем, если кликнули по ссылке или кнопке
        if (e.target.closest('a, button, .info-btn, .add-employee-btn, .dropdown-btn, .dropdown-menu')) {
            return;
        }

        isDragging = true;
        startX = e.clientX - translateX;
        startY = e.clientY - translateY;
        viewport.style.cursor = "grabbing";
        
        e.preventDefault();
    });

    window.addEventListener("mouseup", () => {
        isDragging = false;
        viewport.style.cursor = "grab";
    });

    window.addEventListener("mousemove", (e) => {
        if (!isDragging) return;

        translateX = e.clientX - startX;
        translateY = e.clientY - startY;
        applyTransform();
    });

    // === 🎡 Зум колёсиком ===
    viewport.addEventListener(
        "wheel",
        (e) => {
            e.preventDefault();

            const viewportRect = viewport.getBoundingClientRect();
            const centerX = viewportRect.width / 2;
            const centerY = viewportRect.height / 2;

            const canvasX = (centerX - translateX) / scale;
            const canvasY = (centerY - translateY) / scale;

            const zoomFactor = e.deltaY > 0 ? 0.9 : 1.1;
            const newScale = Math.min(
                MAX_SCALE,
                Math.max(MIN_SCALE, scale * zoomFactor)
            );

            translateX = centerX - canvasX * newScale;
            translateY = centerY - canvasY * newScale;
            scale = newScale;

            applyTransform();
        },
        { passive: false }
    );

    // === ⌨️ Опционально: стрелочки для точной навигации ===
    const MOVE_STEP = 80;
    window.addEventListener("keydown", (e) => {
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.code)) {
            e.preventDefault();
            switch (e.code) {
                case "ArrowUp": translateY += MOVE_STEP; break;
                case "ArrowDown": translateY -= MOVE_STEP; break;
                case "ArrowLeft": translateX += MOVE_STEP; break;
                case "ArrowRight": translateX -= MOVE_STEP; break;
            }
            applyTransform();
        }
    });

    // === 🔄 Применить трансформацию ===
    function applyTransform() {
        canvas.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
    }

    // === 🎯 Кнопка сброса вида ===
    const resetBtn = document.getElementById("resetView");
    resetBtn?.addEventListener("click", () => {
        translateX = 0;
        translateY = 0;
        scale = 1;
        applyTransform();
        fitToScreen();
    });

    // === 📐 Подогнать под экран при загрузке ===
    function fitToScreen() {
        const viewportRect = viewport.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();

        const scaleX = viewportRect.width / canvasRect.width;
        const scaleY = viewportRect.height / canvasRect.height;
        scale = Math.min(scaleX, scaleY, MAX_SCALE);
        scale = Math.max(scale, MIN_SCALE);

        translateX = (viewportRect.width - canvasRect.width * scale) / 2;
        translateY = (viewportRect.height - canvasRect.height * scale) / 2;
        applyTransform();
    }

    // Инициализация
    viewport.style.cursor = "grab";
    fitToScreen();
});