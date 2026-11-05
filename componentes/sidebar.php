
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
        height: 100dvh;
        background: linear-gradient(180deg, #4b2a78, #35215c);
        color: white;
        z-index: 1000;
        transition: width .3s ease;
       overflow-x: hidden;
overflow-y: auto;
scrollbar-width: none;
        box-shadow: 4px 0 18px rgba(50,30,80,.18);
    }

.sidebar-pae::-webkit-scrollbar {
    display: none;
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
        border-bottom: 1px solid rgba(255,255,255,.12);
        white-space: nowrap;
    }

    .sidebar-logo .logo-icon {
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(255,255,255,.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .sidebar-logo-text,
    .sidebar-texto {
        opacity: 0;
        transition: opacity .2s ease;
    }

    .sidebar-pae:hover .sidebar-logo-text,
    .sidebar-pae:hover .sidebar-texto {
        opacity: 1;
    }

    .sidebar-logo-text strong {
        display: block;
        font-size: 15px;
    }

    .sidebar-logo-text span {
        display: block;
        font-size: 11px;
        opacity: .7;
        margin-top: 2px;
    }

    .sidebar-menu {
    padding: 12px 10px 85px;
}

    .sidebar-item {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 44px;
margin-bottom: 4px;
        border-radius: 13px;
        color: rgba(255,255,255,.88);
        text-decoration: none;
        white-space: nowrap;
        transition: background .2s ease, color .2s ease;
    }

    .sidebar-item:hover {
        background: rgba(255,255,255,.13);
        color: white;
    }

    .sidebar-item.activo {
        background: linear-gradient(
            90deg,
            rgba(255,255,255,.20),
            rgba(255,255,255,.08)
        );
        color: white;
    }

    .sidebar-icon {
        flex: 0 0 52px;
        width: 52px;
        text-align: center;
        font-size: 22px;
    }

    .sidebar-texto {
        font-size: 14px;
        font-weight: 500;
    }

    .sidebar-separador {
        height: 1px;
        background: rgba(255,255,255,.12);
        margin: 15px 8px;
    }

    .sidebar-salir {
        position: sticky;
        bottom: 0;
        padding: 10px;
        background: #35215c;
    }

    .sidebar-salir a {
        color: #ffd6df;
    }

    .sidebar-salir a:hover {
        background: rgba(255,100,130,.15);
        color: white;
    }

    .contenido-con-sidebar {
        margin-left: 72px;
        min-height: 100vh;
        transition: margin-left .3s ease;
    }

    .sidebar-pae:hover ~ .contenido-con-sidebar {
        margin-left: 245px;
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
    }

    .sidebar-pae:not(:hover) .sidebar-item:hover::after {
        opacity: 1;
    }

    /* Botón móvil: oculto en computador */

    .boton-menu-movil {
        display: none;
    }

    .fondo-menu-movil {
        display: none;
    }

    /* TABLETAS Y CELULARES */

    @media (max-width: 768px) {

        .boton-menu-movil {
            position: fixed;
            top: 12px;
            left: 12px;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 12px;
            background: #4b2a78;
            color: white;
            font-size: 25px;
            cursor: pointer;
            z-index: 1200;
            box-shadow: 0 4px 12px rgba(0,0,0,.18);
        }

        .sidebar-pae,
        .sidebar-pae:hover {
            width: 245px;
            left: -260px;
            transition: left .3s ease;
        }

        .sidebar-pae.menu-abierto {
            left: 0;
        }

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
            flex-basis: 52px;
            width: 52px;
        }

        .sidebar-item::after {
            display: none;
        }

        .contenido-dashboard,
        .contenido-con-sidebar {
            margin-left: 0 !important;
            width: 100%;
            min-width: 0;
        }

        .fondo-menu-movil.visible {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(25,15,40,.4);
            z-index: 900;
        }

        body.menu-movil-abierto {
            overflow: hidden;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .sidebar-pae,
        .contenido-con-sidebar {
            transition: none;
        }
    }
</style>

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

<div
    class="fondo-menu-movil"
    id="fondoMenuMovil"
></div>

<aside class="sidebar-pae" id="sidebarPae">

    <div class="sidebar-logo">
        <div class="logo-icon">🍎</div>

        <div class="sidebar-logo-text">
            <strong>GESTIÓN PAE</strong>
            <span>Programa de Alimentación Escolar</span>
        </div>
    </div>

    <nav class="sidebar-menu">

        <a
            href="/gestion-pae-nuevo/dashboard.php"
            class="sidebar-item <?php echo ($pagina_actual === 'dashboard.php') ? 'activo' : ''; ?>"
            data-tooltip="Inicio"
        >
            <span class="sidebar-icon">🏠</span>
            <span class="sidebar-texto">Inicio</span>
        </a>

        <a
            href="/gestion-pae-nuevo/estudiantes/listar.php"
            class="sidebar-item <?php echo (strpos($_SERVER['PHP_SELF'], '/estudiantes/') !== false) ? 'activo' : ''; ?>"
            data-tooltip="Estudiantes"
        >
            <span class="sidebar-icon">👨‍🎓</span>
            <span class="sidebar-texto">Estudiantes</span>
        </a>

        <a
            href="/gestion-pae-nuevo/cursos/listar.php"
            class="sidebar-item <?php echo (strpos($_SERVER['PHP_SELF'], '/cursos/') !== false) ? 'activo' : ''; ?>"
            data-tooltip="Cursos / Grupos"
        >
            <span class="sidebar-icon">🏫</span>
            <span class="sidebar-texto">Cursos / Grupos</span>
        </a>

        <a
            href="/gestion-pae-nuevo/profesores/listar.php"
            class="sidebar-item <?php echo (strpos($_SERVER['PHP_SELF'], '/profesores/') !== false) ? 'activo' : ''; ?>"
            data-tooltip="Profesores"
        >
            <span class="sidebar-icon">👨‍🏫</span>
            <span class="sidebar-texto">Profesores</span>
        </a>

        <a
            href="/gestion-pae-nuevo/personal_pae/listar.php"
            class="sidebar-item <?php echo (strpos($_SERVER['PHP_SELF'], '/personal_pae/') !== false) ? 'activo' : ''; ?>"
            data-tooltip="Personal PAE"
        >
            <span class="sidebar-icon">👨‍🍳</span>
            <span class="sidebar-texto">Personal PAE</span>
        </a>

        <div class="sidebar-separador"></div>


        <a
            href="/gestion-pae-nuevo/registros_pae/reportes.php"
            class="sidebar-item <?php echo (strpos($_SERVER['PHP_SELF'], '/registros_pae/reportes.php') !== false) ? 'activo' : ''; ?>"
            data-tooltip="Historial PAE"
        >
            <span class="sidebar-icon">📊</span>
            <span class="sidebar-texto">Historial PAE</span>
        </a>


<a
    href="/gestion-pae-nuevo/eliminar_historial.php"
    class="sidebar-item <?php echo ($pagina_actual === 'eliminar_historial.php') ? 'activo' : ''; ?>"
    data-tooltip="Eliminar historial"
>
    <span class="sidebar-icon">🗑️</span>
    <span class="sidebar-texto">Eliminar historial</span>
</a>


<a
    href="/gestion-pae-nuevo/registros_pae/informes.php"
    class="sidebar-item <?php echo (strpos($_SERVER['PHP_SELF'], '/registros_pae/informes.php') !== false) ? 'activo' : ''; ?>"
    data-tooltip="Informes PAE"
>
    <span class="sidebar-icon">📈</span>
    <span class="sidebar-texto">Informes PAE</span>
</a>


    </nav>

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
    const boton = document.getElementById('botonMenuMovil');
    const menu = document.getElementById('sidebarPae');
    const fondo = document.getElementById('fondoMenuMovil');

    if (!boton || !menu || !fondo) return;

    function cerrarMenu() {
        menu.classList.remove('menu-abierto');
        fondo.classList.remove('visible');
        document.body.classList.remove('menu-movil-abierto');
        boton.setAttribute('aria-expanded', 'false');
        boton.setAttribute('aria-label', 'Abrir menú');
        boton.textContent = '☰';
    }

    function alternarMenu() {
        const abierto = menu.classList.toggle('menu-abierto');

        fondo.classList.toggle('visible', abierto);
        document.body.classList.toggle('menu-movil-abierto', abierto);
        boton.setAttribute('aria-expanded', String(abierto));
        boton.setAttribute(
            'aria-label',
            abierto ? 'Cerrar menú' : 'Abrir menú'
        );
        boton.textContent = abierto ? '✕' : '☰';
    }

    boton.addEventListener('click', alternarMenu);
    fondo.addEventListener('click', cerrarMenu);

    menu.querySelectorAll('a').forEach(function (enlace) {
        enlace.addEventListener('click', cerrarMenu);
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') cerrarMenu();
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) cerrarMenu();
    });
})();
</script>
