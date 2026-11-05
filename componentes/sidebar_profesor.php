
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<style>
    /* =====================================
       SIDEBAR DEL PROFESOR
    ===================================== */

    .sidebar-pae {
        position: fixed;
        top: 0;
        left: 0;
        width: 72px;
        height: 100vh;
        height: 100dvh;
        background: linear-gradient(180deg, #4b2a78, #35215c);
        color: white;
        z-index: 1000;
        transition: width 0.3s ease;
        overflow-x: hidden;
        overflow-y: auto;
        box-shadow: 4px 0 18px rgba(50, 30, 80, 0.18);
    }

    .sidebar-pae:hover {
        width: 245px;
    }

    /* LOGO */

    .sidebar-logo {
        height: 82px;
        display: flex;
        align-items: center;
        padding: 0 18px;
        gap: 13px;
        border-bottom: 1px solid rgba(255,255,255,0.12);
        white-space: nowrap;
    }

    .sidebar-logo .logo-icon {
        min-width: 38px;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .sidebar-logo-text {
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .sidebar-pae:hover .sidebar-logo-text {
        opacity: 1;
    }

    .sidebar-logo-text strong {
        display: block;
        font-size: 15px;
    }

    .sidebar-logo-text span {
        display: block;
        font-size: 11px;
        opacity: 0.7;
        margin-top: 2px;
    }

    /* ENLACES DEL MENÚ */

    .sidebar-menu {
        padding: 18px 10px;
    }

    .sidebar-item {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 48px;
        margin-bottom: 7px;
        border-radius: 13px;
        color: rgba(255,255,255,0.82);
        text-decoration: none;
        white-space: nowrap;
        transition: background 0.25s ease, color 0.25s ease;
        box-sizing: border-box;
    }

    .sidebar-item:hover {
        background: rgba(255,255,255,0.13);
        color: white;
    }

    .sidebar-item.activo {
        background: linear-gradient(
            90deg,
            rgba(255,255,255,0.20),
            rgba(255,255,255,0.08)
        );
        color: white;
    }

    .sidebar-icon {
        flex-shrink: 0;
        min-width: 52px;
        width: 52px;
        text-align: center;
        font-size: 22px;
    }

    .sidebar-texto {
        opacity: 0;
        transition: opacity 0.2s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .sidebar-pae:hover .sidebar-texto {
        opacity: 1;
    }

    /* SEPARADOR */

    .sidebar-separador {
        height: 1px;
        background: rgba(255,255,255,0.12);
        margin: 15px 8px;
    }

    /* CERRAR SESIÓN */

    .sidebar-salir {
        position: absolute;
        bottom: 15px;
        left: 10px;
        right: 10px;
    }

    .sidebar-salir a {
        color: #ffd6df;
    }

    .sidebar-salir a:hover {
        background: rgba(255,100,130,0.15);
        color: white;
    }

    /* AYUDA VISUAL EN COMPUTADOR */

    .sidebar-item::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 64px;
        background: #25183f;
        color: white;
        padding: 7px 10px;
        border-radius: 7px;
        font-size: 11px;
        opacity: 0;
        pointer-events: none;
        white-space: nowrap;
        transition: opacity 0.2s ease;
    }

    .sidebar-pae:not(:hover) .sidebar-item:hover::after {
        opacity: 1;
    }

    /* BOTÓN Y FONDO MÓVIL: OCULTOS EN COMPUTADOR */

    .boton-menu-movil,
    .fondo-menu-movil {
        display: none;
    }

    /* =====================================
       TABLETAS Y CELULARES
    ===================================== */

    @media (max-width: 768px) {

        /* Botón de tres líneas */

        .boton-menu-movil {
            position: fixed;
            top: 12px;
            left: 12px;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 12px;
            background: #4b2a78;
            color: white;
            font-size: 27px;
            line-height: 1;
            cursor: pointer;
            z-index: 1200;
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        }

        .boton-menu-movil:hover {
            background: #35215c;
        }

        /* Menú oculto fuera de la pantalla */

        .sidebar-pae,
        .sidebar-pae:hover {
            width: 245px;
            height: 100vh;
            height: 100dvh;
            left: -260px;
            top: 0;
            overflow-x: hidden;
            overflow-y: auto;
            transition: left 0.3s ease;
        }

        /* Menú abierto */

        .sidebar-pae.menu-abierto {
            left: 0;
        }

        /* Mostrar textos en móvil */

        .sidebar-logo {
            padding: 0 18px;
        }

        .sidebar-logo-text,
        .sidebar-texto,
        .sidebar-pae:hover .sidebar-logo-text,
        .sidebar-pae:hover .sidebar-texto {
            opacity: 1;
        }

        .sidebar-icon {
            min-width: 52px;
            width: 52px;
        }

        /* Desactivar tooltips */

        .sidebar-item::after {
            display: none;
        }

        /* Fondo oscuro detrás del menú */

        .fondo-menu-movil.visible {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(25,15,40,0.45);
            z-index: 900;
        }

        /* Evitar que el fondo se desplace con el menú abierto */

        body.menu-movil-abierto {
            overflow: hidden;
        }

        /* Dar espacio al contenido para el botón */

        .contenido,
        .contenido-dashboard,
        .contenido-con-sidebar {
            margin-left: 0 !important;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
            padding-top: 72px;
        }

        main.contenido {
            padding-top: 72px;
        }
    }

    /* Respetar preferencias de movimiento */

    @media (prefers-reduced-motion: reduce) {
        .sidebar-pae,
        .sidebar-item,
        .sidebar-logo-text,
        .sidebar-texto {
            transition: none;
        }
    }
</style>

<!-- BOTÓN HAMBURGUESA -->

<button
    type="button"
    class="boton-menu-movil"
    id="botonMenuMovil"
    aria-label="Abrir menú"
    aria-expanded="false"
    aria-controls="sidebarPae"
>
    ☰
</button>

<!-- FONDO OSCURO -->

<div
    class="fondo-menu-movil"
    id="fondoMenuMovil"
></div>

<!-- SIDEBAR DEL PROFESOR -->

<aside class="sidebar-pae" id="sidebarPae">

    <!-- LOGO -->

    <div class="sidebar-logo">

        <div class="logo-icon">
            🍎
        </div>

        <div class="sidebar-logo-text">
            <strong>GESTIÓN PAE</strong>
            <span>Panel del profesor</span>
        </div>

    </div>

    <!-- MENÚ -->

    <nav class="sidebar-menu">

        <!-- PERFIL -->

        <a
            href="/gestion-pae-nuevo/profesores/perfil.php"
            class="sidebar-item <?= ($pagina_actual === 'perfil.php') ? 'activo' : ''; ?>"
            data-tooltip="Mi perfil"
        >
            <span class="sidebar-icon">👨‍🏫</span>
            <span class="sidebar-texto">Mi perfil</span>
        </a>

        <!-- MIS REGISTROS -->

        <a
            href="/gestion-pae-nuevo/profesores/mis_registros.php"
            class="sidebar-item <?= ($pagina_actual === 'mis_registros.php') ? 'activo' : ''; ?>"
            data-tooltip="Mis registros PAE"
        >
            <span class="sidebar-icon">📋</span>
            <span class="sidebar-texto">Mis registros PAE</span>
        </a>

        <div class="sidebar-separador"></div>

    </nav>

    <!-- CERRAR SESIÓN -->

    <div class="sidebar-salir">

        <a
            href="/gestion-pae-nuevo/logout.php"
            class="sidebar-item"
            data-tooltip="Cerrar sesión"
        >
            <span class="sidebar-icon">🚪</span>
            <span class="sidebar-texto">Cerrar sesión</span>
        </a>

    </div>

</aside>

<script>
(function () {
    const boton = document.getElementById("botonMenuMovil");
    const menu = document.getElementById("sidebarPae");
    const fondo = document.getElementById("fondoMenuMovil");

    if (!boton || !menu || !fondo) {
        return;
    }

    function cerrarMenu() {
        menu.classList.remove("menu-abierto");
        fondo.classList.remove("visible");
        document.body.classList.remove("menu-movil-abierto");

        boton.setAttribute("aria-expanded", "false");
        boton.setAttribute("aria-label", "Abrir menú");
        boton.textContent = "☰";
    }

    function alternarMenu() {
        const abierto = menu.classList.toggle("menu-abierto");

        fondo.classList.toggle("visible", abierto);
        document.body.classList.toggle("menu-movil-abierto", abierto);

        boton.setAttribute("aria-expanded", String(abierto));
        boton.setAttribute(
            "aria-label",
            abierto ? "Cerrar menú" : "Abrir menú"
        );

        boton.textContent = abierto ? "✕" : "☰";
    }

    boton.addEventListener("click", alternarMenu);
    fondo.addEventListener("click", cerrarMenu);

    // Cerrar el menú después de seleccionar una opción
    menu.querySelectorAll("a").forEach(function (enlace) {
        enlace.addEventListener("click", cerrarMenu);
    });

    // Cerrar con Escape
    document.addEventListener("keydown", function (evento) {
        if (evento.key === "Escape") {
            cerrarMenu();
        }
    });

    // Cerrar si se cambia de móvil a computador
    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            cerrarMenu();
        }
    });
})();
</script>
