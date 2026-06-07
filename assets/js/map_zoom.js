/* ============================================================
   MAPA INTERACTIVO — ZOOM + PAN + LIMITES + MARCADOR CORRECTO
   ============================================================ */

(function () {

    function initMap() {

        const wrapper = document.getElementById("mapWrapper");
        const inner   = document.getElementById("mapInner");
        const marker  = document.getElementById("mapMarker");
        const img     = document.getElementById("mapImage");

        if (!wrapper || !inner || !marker || !img) {
            setTimeout(initMap, 80);
            return;
        }

//        console.log("Mapa detectado, inicializando zoom…");

        let scale = 1;
        let offsetX = 0;
        let offsetY = 0;
        let isDragging = false;
        let startX, startY;

        /* ============================
           POSICIÓN DEL MARCADOR
           ============================ */
        function updateMarker() {
            const w = img.clientWidth;
            const h = img.clientHeight;

            const px = parseFloat(marker.dataset.px) * w;
            const py = parseFloat(marker.dataset.py) * h;

            marker.style.left = px + "px";
            marker.style.top  = py + "px";
        }

        window.addEventListener("resize", updateMarker);
        updateMarker();

        /* ============================
           APLICAR TRANSFORMACIÓN
           ============================ */
        function applyTransform() {
            inner.style.transform =
                `translate(${offsetX}px, ${offsetY}px) scale(${scale})`;
        }

        /* ============================
           LIMITAR MOVIMIENTO
           ============================ */
        function clamp() {
            const w = img.clientWidth * scale;
            const h = img.clientHeight * scale;

            const minX = wrapper.clientWidth - w;
            const minY = wrapper.clientHeight - h;

            if (w <= wrapper.clientWidth) {
                offsetX = (wrapper.clientWidth - w) / 2;
            } else {
                offsetX = Math.min(0, Math.max(minX, offsetX));
            }

            if (h <= wrapper.clientHeight) {
                offsetY = (wrapper.clientHeight - h) / 2;
            } else {
                offsetY = Math.min(0, Math.max(minY, offsetY));
            }
        }

        /* ============================
           ZOOM
           ============================ */
        wrapper.addEventListener("wheel", function(e) {
            e.preventDefault();

            const zoomIntensity = 0.15;
            const oldScale = scale;

            scale += (e.deltaY < 0 ? zoomIntensity : -zoomIntensity);
            scale = Math.min(Math.max(0.5, scale), 4);

            const rect = wrapper.getBoundingClientRect();
            const cx = e.clientX - rect.left;
            const cy = e.clientY - rect.top;

            offsetX = cx - (cx - offsetX) * (scale / oldScale);
            offsetY = cy - (cy - offsetY) * (scale / oldScale);

            clamp();
            applyTransform();
        }, { passive: false });

        /* ============================
           ARRASTRAR (PAN)
           ============================ */
        wrapper.addEventListener("mousedown", function(e) {
            isDragging = true;
            startX = e.clientX - offsetX;
            startY = e.clientY - offsetY;
            wrapper.style.cursor = "grabbing";
        });

        window.addEventListener("mouseup", function() {
            isDragging = false;
            wrapper.style.cursor = "grab";
        });

        window.addEventListener("mousemove", function(e) {
            if (!isDragging) return;

            offsetX = e.clientX - startX;
            offsetY = e.clientY - startY;

            clamp();
            applyTransform();
        });

        applyTransform();
//        console.log("Zoom + pan aplicados correctamente.");
    }

    /* ============================
       INICIALIZACIÓN SEGURA
       ============================ */

    document.addEventListener("DOMContentLoaded", initMap);

    const observer = new MutationObserver(() => initMap());

    function startObserver() {
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
        } else {
            setTimeout(startObserver, 50);
        }
    }

    startObserver();

})();

