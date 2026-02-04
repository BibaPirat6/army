document.addEventListener("DOMContentLoaded", () => {



    const viewport = document.getElementById("viewport");
    const canvas = document.getElementById("canvas");
    canvas.style.transformOrigin = "0 0";


    if (!viewport || !canvas) return;

    let isDragging = false;
    let spacePressed = false;

    let startX = 0;
    let startY = 0;

    let translateX = 0;
    let translateY = 0;

    let scale = 1;
    const MIN_SCALE = 0.4;
    const MAX_SCALE = 2;

    // Space
    window.addEventListener("keydown", (e) => {
        if (e.code === "Space") {
            spacePressed = true;
            viewport.style.cursor = "grab";
            e.preventDefault();
        }
    });

    window.addEventListener("keyup", (e) => {
        if (e.code === "Space") {
            spacePressed = false;
            viewport.style.cursor = "default";
        }
    });

    const MOVE_STEP = 80;

    window.addEventListener("keydown", (e) => {
        switch (e.code) {
            case "ArrowUp":
                translateY += MOVE_STEP;
                break;
            case "ArrowDown":
                translateY -= MOVE_STEP;
                break;
            case "ArrowLeft":
                translateX += MOVE_STEP;
                break;
            case "ArrowRight":
                translateX -= MOVE_STEP;
                break;
            default:
                return;
        }

        applyTransform();
    });


    /// grab
    viewport.addEventListener("mousedown", (e) => {
        if (!spacePressed || e.button !== 0) return;

        isDragging = true;
        startX = e.clientX - translateX;
        startY = e.clientY - translateY;

        viewport.style.cursor = "grabbing";
    });

    window.addEventListener("mouseup", () => {
        isDragging = false;
        viewport.style.cursor = spacePressed ? "grab" : "default";
    });

    // Move
    window.addEventListener("mousemove", (e) => {
        if (!isDragging) return;

        translateX = e.clientX - startX;
        translateY = e.clientY - startY;

        applyTransform();
    });

    //  Zoom
    viewport.addEventListener(
        "wheel",
        (e) => {
            e.preventDefault();

            const viewportRect = viewport.getBoundingClientRect();

            // центр экрана (viewport)
            const centerX = viewportRect.width / 2;
            const centerY = viewportRect.height / 2;

            // текущая точка canvas в центре экрана
            const canvasX = (centerX - translateX) / scale;
            const canvasY = (centerY - translateY) / scale;

            const zoomFactor = e.deltaY > 0 ? 0.9 : 1.1;
            const newScale = Math.min(
                MAX_SCALE,
                Math.max(MIN_SCALE, scale * zoomFactor)
            );

            // пересчёт translate, чтобы центр остался на месте
            translateX = centerX - canvasX * newScale;
            translateY = centerY - canvasY * newScale;

            scale = newScale;

            applyTransform();
        },
        { passive: false }
    );




    function applyTransform() {
        canvas.style.transform = `
            translate(${translateX}px, ${translateY}px)
            scale(${scale})
        `;
    }

    const resetBtn = document.getElementById("resetView");

    //  Reset view
    resetBtn.addEventListener("click", () => {
        translateX = 0;
        translateY = 0;
        scale = 1;
        applyTransform();
    });




    function fitToScreen() {
        const viewportRect = viewport.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();

        const scaleX = viewportRect.width / canvasRect.width;
        const scaleY = viewportRect.height / canvasRect.height;

        // берём меньший масштаб, чтобы ВСЁ влезло
        scale = Math.min(scaleX, scaleY);

        // ограничим, если надо
        scale = Math.min(MAX_SCALE, Math.max(MIN_SCALE, scale));

        // центрируем
        translateX = (viewportRect.width - canvasRect.width * scale) / 2;
        translateY = (viewportRect.height - canvasRect.height * scale) / 2;

        applyTransform();
    }

    // 🔥 запуск при загрузке
    fitToScreen();

});