
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<style>
    .sidebar-pae,
    .sidebar-pae * {
        box-sizing: border-box;
    }

    .sidebar-pae {
        position: fixed;
        top: 0;
        left: 0;
        width: 72px;
        height: 100vh;
        background: linear-gradient(180deg, #4b2a78, #35215c);
        color: white;
        z-index: 1000;
        transition: width 0.3s ease, left 0.3s ease;
        overflow: hidden;
        box-shadow: 4px 0 18px rgba(50, 30, 80, 0.18);
    }

    .sidebar-pae:hover {
        width: 245px;
    }

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

    .sidebar-menu {
        padding: 18px 10px;
    }

    .sidebar-item {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        height: 48px;
        margin-bottom: 7px;
        border-radius: 13px;
        color: rgba(255,255,255,0.82);
        text-decoration: none;
        white-space: nowrap;
        transition: 0.25s ease;
    }

    .sidebar-item:hover {
        background: rgba(255,255,255,0.13);
        color: white;
        transform: translateX(2px);
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

    .sidebar-separador {
        height: 1px;
        background: rgba(255,255,255,0.12);
        margin: 15px 8px;
    }

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
        background: rgba(255, 100, 130, 0.15);
        color: #fff;
    }

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
        transition: 0.2s;
    }

    .sidebar-pae:not(:hover) .sidebar-item:hover::after {
        opacity: 1;
    }

    /* Botón hamburguesa: oculto en computador */
    .boton-menu-estudiante {
        display: none;
    }

    .fondo-menu-estudiante {
        display: none;
    }

    /* RESPONSIVE PARA CELULARES */
    @media (max-width: 768px) {
        .boton-menu-estudiante {
            display: flex;
            position: fixed;
            top: 12px;
            left: 12px;
            width: 45px;
            height: 45px;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 12px;
            background: #4b2a78;
            color: white;
            font-size: 25px;
            cursor: pointer;
            z-index: 1200;
            box-shadow: 0 3px 12px rgba(50, 30, 80, 0.2);
        }

        .sidebar-pae {
            width: 260px;
            max-width: 85vw;
            left: -280px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-pae:hover {
            width: 260px;
        }

        .sidebar-pae.menu-abierto {
            left: 0;
        }

        .sidebar-pae .sidebar-logo {
            padding: 0 18px;
        }

        .sidebar-pae .sidebar-logo-text,
        .sidebar-pae .sidebar-texto {
            opacity: 1;
        }

        .sidebar-pae .sidebar-icon {
            min-width: 52px;
            width: 52px;
        }

        .sidebar-pae .sidebar-item::after {
            display: none;
        }

        .sidebar-pae .sidebar-menu {
            padding-bottom: 85px;
        }

        .fondo-menu-estudiante.activo {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(20, 12, 35, 0.45);
            z-index: 900;
        }

        /* Espacio para el botón hamburguesa */
        body {
            padding-top: 65px;
        }

        .boton-volver {
            margin-top: 0;
        }
    }
</style>

<!-- Botón hamburguesa -->
<button
    type="button"
    class="boton-menu-estudiante"
    id="botonMenuEstudiante"
    aria-label="Abrir menú"
    aria-expanded="false"
    aria-controls="sidebarEstudiante"
>
    ☰
</button>

<!-- Fondo oscuro -->
<div
    class="fondo-menu-estudiante"
    id="fondoMenuEstudiante"
></div>

<!-- Menú lateral -->
<aside class="sidebar-pae" id="sidebarEstudiante">

    <div class="sidebar-logo">
        <div class="logo-icon">🍎</div>

        <div class="sidebar-logo-text">
            <strong>GESTIÓN PAE</strong>
            <span>Panel del estudiante</span>
        </div>
    </div>

    <nav class="sidebar-menu">

        <a
            href="/gestion-pae-nuevo/estudiantes/perfil.php"
            class="sidebar-item <?php echo ($pagina_actual === 'perfil.php') ? 'activo' : ''; ?>"
            data-tooltip="Inicio"
        >
            <span class="sidebar-icon">🏠</span>
            <span class="sidebar-texto">Inicio</span>
        </a>

        <a
            href="/gestion-pae-nuevo/estudiantes/mi_alimentacion.php"
            class="sidebar-item <?php echo ($pagina_actual === 'mi_alimentacion.php') ? 'activo' : ''; ?>"
            data-tooltip="Mi alimentación"
        >
            <span class="sidebar-icon">🍽️</span>
            <span class="sidebar-texto">Mi alimentación</span>
        </a>

        <a
            href="/gestion-pae-nuevo/estudiantes/historial.php"
            class="sidebar-item <?php echo ($pagina_actual === 'historial.php') ? 'activo' : ''; ?>"
            data-tooltip="Mi historial"
        >
            <span class="sidebar-icon">📅</span>
            <span class="sidebar-texto">Mi historial</span>
        </a>

        <div class="sidebar-separador"></div>

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

    </nav>
</aside>

<script>
(function () {
    const boton = document.getElementById('botonMenuEstudiante');
    const sidebar = document.getElementById('sidebarEstudiante');
    const fondo = document.getElementById('fondoMenuEstudiante');

    if (!boton || !sidebar || !fondo) return;

    function abrirMenu() {
        sidebar.classList.add('menu-abierto');
        fondo.classList.add('activo');
        boton.innerHTML = '✕';
        boton.setAttribute('aria-label', 'Cerrar menú');
        boton.setAttribute('aria-expanded', 'true');
    }

    function cerrarMenu() {
        sidebar.classList.remove('menu-abierto');
        fondo.classList.remove('activo');
        boton.innerHTML = '☰';
        boton.setAttribute('aria-label', 'Abrir menú');
        boton.setAttribute('aria-expanded', 'false');
    }

    boton.addEventListener('click', function () {
        if (sidebar.classList.contains('menu-abierto')) {
            cerrarMenu();
        } else {
            abrirMenu();
        }
    });

    fondo.addEventListener('click', cerrarMenu);

    sidebar.querySelectorAll('a').forEach(function (enlace) {
        enlace.addEventListener('click', cerrarMenu);
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarMenu();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            cerrarMenu();
        }
    });
})();
</script>
