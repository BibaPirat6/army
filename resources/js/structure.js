document.addEventListener("DOMContentLoaded", () => {
    const viewport = document.getElementById("viewport");
    const canvas = document.getElementById("canvas");

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

    // 🔑 Space
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

    // 🖱 Start drag (Space + LMB)
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

    // 🖱 Move
    window.addEventListener("mousemove", (e) => {
        if (!isDragging) return;

        translateX = e.clientX - startX;
        translateY = e.clientY - startY;

        applyTransform();
    });

    // 🔍 Zoom wheel
    viewport.addEventListener("wheel", (e) => {
        e.preventDefault();

        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        scale += delta;
        scale = Math.min(MAX_SCALE, Math.max(MIN_SCALE, scale));

        applyTransform();
    }, {
        passive: false
    });


    function applyTransform() {
        canvas.style.transform = `
            translate(${translateX}px, ${translateY}px)
            scale(${scale})
        `;
    }

    const resetBtn = document.getElementById("resetView");

    // 🧭 Reset view
    resetBtn.addEventListener("click", () => {
        translateX = 0;
        translateY = 0;
        scale = 1;
        applyTransform();
    });
});