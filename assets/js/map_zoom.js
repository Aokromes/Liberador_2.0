/* ============================================================
   MAPA INTERACTIVO — ZOOM + PAN
   Compatible con carga dinámica vía innerHTML
   ============================================================ */

(function () {

    let initialized = false;

    function initMap() {
        if (initialized) return;

        const wrapper = document.getElementById("mapWrapper");
        const inner   = document.getElementById("mapInner");
        const marker  = document.getElementById("mapMarker");

        if (!wrapper || !inner) {
            // El mapa aún no existe → reintentar
            setTimeout(initMap, 100);
            return;
        }

        initialized = true;
        console.log("Mapa detectado, inicializando zoom…");

        let scale = 1;
        let posX = 0;
        let posY = 0;
        let isDragging = false;
        let startX, startY;

        /* ============================
           POSICIÓN DEL MARCADOR
           ============================ */
        function updateMarker() {
            if (!marker) return;

            const w = inner.clientWidth;
            const h = inner.clientHeight;

            const px = parseFloat(inner.dataset.px) * w;
            const py = parseFloat(inner.dataset.py) * h;

            marker.style.left = px + "px";
            marker.style.top  = py + "px";
        }

        window.addEventListener("resize", updateMarker);
        updateMarker();

        /* ============================
           ZOOM REAL
           ============================ */
        wrapper.addEventListener("wheel", function(e) {
            e.preventDefault();

            const zoomIntensity = 0.15;
            const oldScale = scale;

            scale += (e.deltaY < 0 ? zoomIntensity : -zoomIntensity);
            scale = Math.min(Math.max(0.5, scale), 4);

            inner.style.backgroundSize = (scale * 100) + "%";

            const rect = wrapper.getBoundingClientRect();
            const offsetX = e.clientX - rect.left;
            const offsetY = e.clientY - rect.top;

            posX -= (offsetX / oldScale - offsetX / scale);
            posY -= (offsetY / oldScale - offsetY / scale);

            inner.style.backgroundPosition = `${posX}px ${posY}px`;
        }, { passive: false });

        /* ============================
           ARRASTRAR (PAN)
           ============================ */
        inner.addEventListener("mousedown", function(e) {
            isDragging = true;
            startX = e.clientX - posX;
            startY = e.clientY - posY;
            inner.style.cursor = "grabbing";
        });

        window.addEventListener("mouseup", function() {
            isDragging = false;
            inner.style.cursor = "grab";
        });

        window.addEventListener("mousemove", function(e) {
            if (!isDragging) return;

            posX = e.clientX - startX;
            posY = e.clientY - startY;

            inner.style.backgroundPosition = `${posX}px ${posY}px`;
        });

        console.log("Zoom del mapa activado correctamente.");
    }

    /* ============================
       INICIALIZACIÓN SEGURA
       ============================ */

    // 1. Cuando el DOM está listo
    document.addEventListener("DOMContentLoaded", initMap);

    // 2. Cuando se cargue contenido dinámico (AJAX)
    const observer = new MutationObserver(() => initMap());

    function startObserver() {
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
            console.log("Observer iniciado");
        } else {
            setTimeout(startObserver, 50);
        }
    }

    startObserver();

})();

