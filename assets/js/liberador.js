/* ============================================================
   APLICAR TEMA ANTES DEL RENDER (FIX)
   ============================================================ */

(function() {
    const theme = localStorage.getItem("theme") || "dark";
    if (theme === "light") {
        document.documentElement.classList.add("light");
        document.body.classList.add("light");
    }
})();

/* ============================================================
   CARGAR RENDER DEL PERSONAJE
   ============================================================ */

function loadCharacterRender(guid) {
    document.querySelectorAll(".character-item").forEach(el => {
        el.classList.remove("selected");
    });

    const item = document.querySelector(`.character-item[data-guid="${guid}"]`);
    if (item) item.classList.add("selected");

    const panel = document.getElementById("render-panel");
    const output = document.getElementById("render-output");

    if (panel) panel.style.display = "block";

    fetch("render_character.php?guid=" + guid)
        .then(r => r.text())
        .then(html => {
            if (output) output.innerHTML = html;
        })
        .catch(() => {
            if (output) output.innerHTML = "<p>Error loading character.</p>";
        });
}

/* ============================================================
   SELECTOR DE IDIOMAS
   ============================================================ */

function toggleLangMenu() {
    const menu = document.getElementById("lang-menu");
    if (!menu) return;

    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}

document.addEventListener("click", function (e) {
    const selector = document.querySelector(".lang-selector");
    const menu = document.getElementById("lang-menu");

    if (!selector || !menu) return;

    if (!e.target.closest(".lang-selector")) {
        menu.style.display = "none";
    }
});

/* ============================================================
   TEMA OSCURO / CLARO
   ============================================================ */

function applyTheme() {
    const theme = localStorage.getItem("theme") || "dark";

    // documentElement siempre existe
    document.documentElement.classList.toggle("light", theme === "light");

    // body puede ser null si el script está en <head>
    if (document.body) {
        document.body.classList.toggle("light", theme === "light");
    }
}

function toggleTheme() {
    const current = localStorage.getItem("theme") || "dark";
    const next = current === "dark" ? "light" : "dark";
    localStorage.setItem("theme", next);
    applyTheme();
}

// Esperar a que el DOM esté listo
document.addEventListener("DOMContentLoaded", applyTheme);

