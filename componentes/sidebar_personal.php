<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina_actual = basename($_SERVER['PHP_SELF']);
?>


  
<style>
.sidebar-pae {
    position: fixed;
    top: 0;
    left: 0;
    width: 72px;
    height: 100vh;
    height: 100dvh;

display: flex;
    flex-direction: column;

    background: linear-gradient(180deg, #4b2a78, #35215c);
    color: white;
    z-index: 1000;
    transition: width 0.3s ease, transform 0.3s ease;
    overflow-x: hidden;
    overflow-y: auto;
    box-shadow: 4px 0 18px rgba(50, 30, 80, 0.18);
}


.sidebar-menu {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 18px 10px;
    box-sizing: border-box;
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
    box-sizing: border-box;
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
    box-sizing: border-box;
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
    min-width: 52px;
    width: 52px;
    text-align: center;
    font-size: 22px;
    flex-shrink: 0;
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
    margin-top: auto;
    padding: 15px 0 -80px;
}


.sidebar-salir a {
    color: #ffd6df;
}

.sidebar-salir a:hover {
    background: rgba(255, 100, 130, 0.15);
    color: #fff;
}

/* Botón para abrir el menú en celular */
.boton-menu-pae {
    display: none;
    position: fixed;
    top: 12px;
    left: 12px;
    z-index: 1102;
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 12px;
    background: #4b2a78;
    color: white;
    font-size: 25px;
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(0,0,0,0.18);
}

.fondo-sidebar-pae {
    display: none;
}

/* Ajustes para celulares y tabletas */
@media (max-width: 768px) {
    .boton-menu-pae {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar-pae,
    .sidebar-pae:hover {
        width: 245px;
        max-width: 85vw;
        transform: translateX(-105%);
        transition: transform 0.3s ease;
    }

    .sidebar-pae.abierto {
        transform: translateX(0);
    }

    .sidebar-logo {
        padding-left: 18px;
    }

    .sidebar-logo-text,
    .sidebar-pae:hover .sidebar-logo-text,
    .sidebar-texto,
    .sidebar-pae:hover .sidebar-texto {
        opacity: 1;
    }

    .sidebar-icon {
        min-width: 52px;
        width: 52px;
    }

    .sidebar-item::after {
        display: none;
    }

    .fondo-sidebar-pae.visible {
        display: block;
        position: fixed;
        inset: 0;
        z-index: 999;
        background: rgba(20, 12, 35, 0.45);
    }

    body.sidebar-pae-abierto {
        overflow: hidden;
    }
}

/* Evitar animaciones innecesarias si el usuario las reduce */
@media (prefers-reduced-motion: reduce) {
    .sidebar-pae,
    .sidebar-item,
    .sidebar-logo-text,
    .sidebar-texto {
        transition: none;
    }
}
</style>



<button
    type="button"
    class="boton-menu-pae"
    id="botonMenuPae"
    aria-label="Abrir menú"
    aria-controls="sidebarPersonalPae"
    aria-expanded="false"
>
    ☰
</button>
<div class="fondo-sidebar-pae" id="fondoSidebarPae"></div>
<aside class="sidebar-pae" id="sidebarPersonalPae">

<!-- =====================================
     SIDEBAR PERSONAL PAE
     ===================================== -->


    <!-- LOGO -->

    <div class="sidebar-logo">

        <div class="logo-icon">
            🍎
        </div>

        <div class="sidebar-logo-text">
            <strong>GESTIÓN PAE</strong>
            <span>Panel del personal PAE</span>
        </div>

    </div>


    <!-- MENÚ -->

    <nav class="sidebar-menu">

        <!-- MI PERFIL -->

        <a
            href="/gestion-pae-nuevo/personal_pae/perfil.php"
            class="sidebar-item <?php echo ($pagina_actual === 'perfil.php') ? 'activo' : ''; ?>"
            data-tooltip="Mi perfil"
        >
            <span class="sidebar-icon">👨‍🍳</span>
            <span class="sidebar-texto">Mi perfil</span>
        </a>


        <!-- REGISTRAR ENTREGA -->

        <a
            href="/gestion-pae-nuevo/personal_pae/seleccionar_registro.php"
            class="sidebar-item <?php echo ($pagina_actual === 'seleccionar_registro.php' || $pagina_actual === 'registro_refrigerio.php') ? 'activo' : ''; ?>"
            data-tooltip="Registrar entrega"
        >
            <span class="sidebar-icon">🍽️</span>
            <span class="sidebar-texto">Registrar entrega</span>
        </a>


        <!-- MIS REGISTROS -->

        <a
            href="/gestion-pae-nuevo/personal_pae/mis_registros.php"
            class="sidebar-item <?php echo ($pagina_actual === 'mis_registros.php' || $pagina_actual === 'ver_grupo.php') ? 'activo' : ''; ?>"
            data-tooltip="Mis registros"
        >
            <span class="sidebar-icon">📋</span>
            <span class="sidebar-texto">Mis registros</span>
        </a>


        <div class="sidebar-separador"></div>


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

    </nav>

</aside>

<script>
(function () {
    const sidebar = document.getElementById("sidebarPersonalPae");
    const boton = document.getElementById("botonMenuPae");
    const fondo = document.getElementById("fondoSidebarPae");

    if (!sidebar || !boton || !fondo) return;

    function cambiarMenu(abierto) {
        sidebar.classList.toggle("abierto", abierto);
        fondo.classList.toggle("visible", abierto);
        document.body.classList.toggle("sidebar-pae-abierto", abierto);

        boton.setAttribute("aria-expanded", abierto ? "true" : "false");
        boton.setAttribute("aria-label", abierto ? "Cerrar menú" : "Abrir menú");
        boton.textContent = abierto ? "×" : "☰";
    }

    boton.addEventListener("click", function () {
        cambiarMenu(!sidebar.classList.contains("abierto"));
    });

    fondo.addEventListener("click", function () {
        cambiarMenu(false);
    });

    document.addEventListener("keydown", function (evento) {
        if (evento.key === "Escape") cambiarMenu(false);
    });

    sidebar.querySelectorAll("a").forEach(function (enlace) {
        enlace.addEventListener("click", function () {
            if (window.innerWidth <= 768) cambiarMenu(false);
        });
    });

    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) cambiarMenu(false);
    });
})();
</script>
