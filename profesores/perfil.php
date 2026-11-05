
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["id_rol"] != 3) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";

$id_usuario = $_SESSION["id_usuario"];

try {
    // Buscar información del profesor
    $sql = "SELECT
                p.id_profesor,
                p.documento,
                p.nombres,
                p.apellidos,
                p.correo,
                p.telefono,
                p.estado,
                p.foto
            FROM profesores p
            WHERE p.id_usuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo "No se encontró la información del profesor.";
        exit();
    }

    $profesor = $resultado->fetch_assoc();
    $stmt->close();

    // Contar participaciones del profesor
    $sql = "SELECT COUNT(*) AS total
            FROM entregas_pae
            WHERE id_profesor = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $profesor["id_profesor"]);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $datos = $resultado->fetch_assoc();

    $total_registros = (int) $datos["total"];
    $stmt->close();

    // Consultar fechas y grupos
    $sql_grupos = "SELECT
                       e.fecha,
                       e.id_curso,
                       c.nombre AS nombre_curso,
                       j.nombre AS nombre_jornada,
                       COUNT(*) AS total_registros
                   FROM entregas_pae e
                   INNER JOIN cursos c
                       ON e.id_curso = c.id_curso
                   INNER JOIN jornadas j
                       ON e.id_jornada = j.id_jornada
                   WHERE e.id_profesor = ?
                   GROUP BY
                       e.fecha,
                       e.id_curso,
                       c.nombre,
                       j.nombre
                   ORDER BY e.fecha DESC, c.nombre ASC";

    $stmt = $conexion->prepare($sql_grupos);
    $stmt->bind_param("i", $profesor["id_profesor"]);
    $stmt->execute();

    $resultado_grupos = $stmt->get_result();
    $grupos_por_fecha = [];

    while ($grupo = $resultado_grupos->fetch_assoc()) {
        $fecha = $grupo["fecha"];

        if (!isset($grupos_por_fecha[$fecha])) {
            $grupos_por_fecha[$fecha] = [];
        }

        $grupos_por_fecha[$fecha][] = $grupo;
    }

    $stmt->close();

} catch (mysqli_sql_exception $e) {
    error_log("Error en perfil.php: " . $e->getMessage());
    exit("Ocurrió un error al cargar la información del perfil.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi perfil | Profesor</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f3ff;
        }

        .contenido {
            margin-left: 250px;
            padding: 40px;
            box-sizing: border-box;
        }

        .encabezado {
            margin-bottom: 25px;
        }

        .encabezado h1 {
            color: #6335bd;
            margin-bottom: 8px;
        }

        .encabezado p {
            color: #666;
        }

        .perfil {
            max-width: 900px;
            margin: auto;
            width: 100%;
        }

        .tarjeta-principal {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            box-sizing: border-box;
        }

        .cabecera-perfil {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            min-width: 0;
        }

        /* Foto de perfil */
        .foto-contenedor {
            width: 105px;
            height: 105px;
            position: relative;
            flex-shrink: 0;
        }

        .foto-perfil,
        .avatar {
            width: 105px;
            height: 105px;
            border-radius: 50%;
            box-sizing: border-box;
        }

        .foto-perfil {
            object-fit: cover;
            border: 4px solid #7e57c2;
            display: block;
        }

        .avatar {
            background: #7e57c2;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            border: 4px solid #7e57c2;
        }

        .cambiar-foto {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(79, 50, 142, 0.82);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            opacity: 0;
            cursor: pointer;
            transition: 0.25s;
        }

        .foto-contenedor:hover .cambiar-foto,
        .foto-contenedor:focus-within .cambiar-foto {
            opacity: 1;
        }

        #foto {
            display: none;
        }

        /* Menú de la foto */
        .menu-foto {
            position: absolute;
            top: 115px;
            left: 0;
            width: 180px;
            max-width: calc(100vw - 30px);
            background: white;
            border-radius: 14px;
            padding: 8px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 20;
            box-sizing: border-box;
        }

        .menu-foto button,
        .menu-foto label {
            width: 100%;
            border: none;
            background: transparent;
            padding: 11px;
            border-radius: 9px;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
            display: block;
            box-sizing: border-box;
            color: #333;
        }

        .menu-foto button:hover,
        .menu-foto label:hover {
            background: #f1edff;
        }

        .opcion-quitar {
            color: #d9534f !important;
        }

        .cabecera-perfil h2 {
            margin: 0 0 8px;
            color: #4f328e;
            overflow-wrap: anywhere;
        }

        .cabecera-perfil span {
            color: #777;
        }

        /* Información personal */
        .datos {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .dato {
            background: #f8f6ff;
            padding: 16px;
            border-radius: 12px;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .dato strong {
            display: block;
            color: #6335bd;
            margin-bottom: 6px;
        }

        .dato span {
            color: #444;
        }

        /* Estadística PAE */
        .estadistica {
            background: linear-gradient(135deg, #8c52ff, #00bcd4);
            color: white;
            border-radius: 18px;
            padding: 25px;
            text-align: center;
        }

        .boton-estadistica {
            width: 100%;
            border: none;
            font-family: inherit;
            cursor: pointer;
            display: block;
            box-sizing: border-box;
        }

        .boton-estadistica:hover {
            filter: brightness(1.04);
        }

        .boton-estadistica:focus-visible {
            outline: 3px solid #4f328e;
            outline-offset: 4px;
        }

        .estadistica .numero {
            display: block;
            font-size: 38px;
            font-weight: bold;
        }

        .estadistica p {
            margin: 5px 0 0;
        }

        .titulo-estadistica {
            display: block;
            margin-top: 5px;
            font-size: 16px;
        }

        .texto-ver-grupos {
            display: block;
            margin-top: 12px;
            font-size: 13px;
            color: white;
            opacity: 0.9;
        }

        /* Fechas y grupos */
        .fecha-participacion {
            margin-top: 14px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .fecha-participacion summary {
            padding: 18px;
            cursor: pointer;
            color: #6335bd;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            overflow-wrap: anywhere;
        }

        .grupos-fecha {
            padding: 0 14px 14px;
            display: grid;
            gap: 12px;
        }

        .tarjeta-grupo {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 16px;
            border-radius: 12px;
            background: #f8f6ff;
            color: #444;
            text-decoration: none;
            overflow-wrap: anywhere;
        }

        .tarjeta-grupo strong,
        .ver-detalle {
            color: #6335bd;
        }

        .tarjeta-grupo:hover {
            background: #eee7ff;
        }

        .sin-participaciones {
            margin-top: 15px;
            padding: 20px;
            background: white;
            border-radius: 14px;
            color: #666;
        }

        /* Adaptación a tabletas */
        @media (max-width: 900px) {
            .contenido {
                margin-left: 0;
                padding: 25px;
            }

            .perfil {
                max-width: 750px;
            }
        }

        /* Adaptación a celulares */
        @media (max-width: 600px) {
            .contenido {
                padding: 16px;
                width: 100%;
            }

            .encabezado h1 {
                font-size: 25px;
            }

            .tarjeta-principal {
                padding: 20px;
                border-radius: 16px;
            }

            .cabecera-perfil {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .menu-foto {
                left: 50%;
                transform: translateX(-50%);
            }

            .datos {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            .estadistica {
                padding: 20px 14px;
            }

            .estadistica .numero {
                font-size: 32px;
            }

            .fecha-participacion summary {
                padding: 14px;
                align-items: flex-start;
                font-size: 14px;
            }

            .grupos-fecha {
                padding: 0 10px 10px;
            }

            .tarjeta-grupo {
                padding: 14px;
            }
        }

        @media (max-width: 360px) {
            .contenido {
                padding: 10px;
            }

            .tarjeta-principal {
                padding: 14px;
            }

            .encabezado h1 {
                font-size: 22px;
            }

            .fecha-participacion summary {
                flex-direction: column;
                gap: 6px;
            }
        }


/* =====================================
   RESPONSIVE - PERFIL DEL PROFESOR
   ===================================== */

/* Evitar desbordamientos */
* {
    box-sizing: border-box;
}

.contenido {
    min-width: 0;
    width: calc(100% - 250px);
}

.perfil,
.tarjeta-principal,
.cabecera-perfil,
.datos,
.dato {
    min-width: 0;
    max-width: 100%;
}

.dato span,
.cabecera-perfil h2,
.tarjeta-grupo {
    overflow-wrap: anywhere;
}

/* Portátiles y tabletas */
@media (max-width: 900px) {
    .contenido {
        width: 100%;
        margin-left: 0;
        padding: 24px;
    }

    .perfil {
        width: 100%;
        max-width: 750px;
    }

    .tarjeta-principal {
        padding: 24px;
    }
}

/* Celulares */
@media (max-width: 600px) {
    .contenido {
        width: 100%;
        padding: 14px;
    }

    .encabezado {
        margin-bottom: 18px;
    }

    .encabezado h1 {
        font-size: 24px;
        line-height: 1.3;
    }

    .encabezado p {
        font-size: 14px;
        line-height: 1.5;
    }

    .tarjeta-principal {
        padding: 18px;
        border-radius: 15px;
    }

    .cabecera-perfil {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 15px;
        margin-bottom: 22px;
    }

    .cabecera-perfil h2 {
        font-size: 21px;
        line-height: 1.4;
    }

    .foto-contenedor {
        flex-shrink: 0;
    }

    .datos {
        grid-template-columns: minmax(0, 1fr);
        gap: 12px;
    }

    .dato {
        padding: 14px;
    }

    .estadistica {
        padding: 20px 14px;
        border-radius: 15px;
    }

    .estadistica .numero {
        font-size: 32px;
    }

    .titulo-estadistica {
        font-size: 15px;
        line-height: 1.5;
    }

    .texto-ver-grupos {
        font-size: 12px;
        line-height: 1.5;
    }

    .fecha-participacion summary {
        padding: 14px;
        font-size: 14px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .grupos-fecha {
        padding: 0 10px 10px;
    }

    .tarjeta-grupo {
        padding: 14px;
        font-size: 14px;
    }

    .menu-foto {
        left: 50%;
        transform: translateX(-50%);
    }
}

/* Celulares pequeños */
@media (max-width: 380px) {
    .contenido {
        padding: 10px;
    }

    .tarjeta-principal {
        padding: 14px;
    }

    .encabezado h1 {
        font-size: 21px;
    }

    .cabecera-perfil h2 {
        font-size: 19px;
    }

    .dato {
        padding: 12px;
    }

    .estadistica .numero {
        font-size: 28px;
    }

    .fecha-participacion summary {
        flex-direction: column;
        gap: 7px;
    }
}


    </style>
</head>

<body>

    <?php include "../componentes/sidebar_profesor.php"; ?>

    <main class="contenido">
        <div class="perfil">

            <header class="encabezado">
                <h1>👨‍🏫 Mi perfil</h1>
                <p>Información personal y actividad en Gestión PAE.</p>
            </header>

            <!-- Tarjeta principal del profesor -->
            <section class="tarjeta-principal">

                <div class="cabecera-perfil">

                    <!-- Foto de perfil -->
                    <div class="foto-contenedor">

                        <?php if (!empty($profesor["foto"])): ?>
                            <img
                                id="imagenPerfil"
                                src="../fotos_profesores/<?php echo htmlspecialchars($profesor["foto"], ENT_QUOTES, "UTF-8"); ?>"
                                alt="Foto de perfil"
                                class="foto-perfil"
                            >
                        <?php else: ?>
                            <div id="imagenPerfil" class="avatar">
                                👨‍🏫
                            </div>
                        <?php endif; ?>

                        <div
                            class="cambiar-foto"
                            onclick="abrirMenuFoto()"
                            role="button"
                            tabindex="0"
                            onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); abrirMenuFoto(); }"
                        >
                            📷<br>
                            Cambiar foto
                        </div>

                        <!-- Formulario para subir foto -->
                        <form
                            id="formFoto"
                            action="guardar_foto.php"
                            method="POST"
                            enctype="multipart/form-data"
                        >
                            <input
                                type="file"
                                id="foto"
                                name="foto"
                                accept="image/*"
                                onchange="enviarFoto()"
                            >
                        </form>

                        <!-- Opciones de la foto -->
                        <div id="menuFoto" class="menu-foto">

                            <label for="foto">
                                📷 Cambiar foto
                            </label>

                            <?php if (!empty($profesor["foto"])): ?>
                                <button
                                    type="button"
                                    class="opcion-quitar"
                                    onclick="quitarFoto()"
                                >
                                    🗑️ Quitar foto
                                </button>
                            <?php endif; ?>

                            <button
                                type="button"
                                onclick="cerrarMenuFoto()"
                            >
                                ❌ Cancelar
                            </button>

                        </div>
                    </div>

                    <!-- Datos del profesor -->
                    <div>
                        <h2>
                            <?php
                            echo htmlspecialchars(
                                ($profesor["nombres"] ?? "") . " " .
                                ($profesor["apellidos"] ?? ""),
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </h2>

                        <span>Profesor</span>
                    </div>

                </div>

                <!-- Información personal -->
                <div class="datos">

                    <div class="dato">
                        <strong>🪪 Documento</strong>
                        <span>
                            <?php
                            echo htmlspecialchars(
                                $profesor["documento"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </span>
                    </div>

                    <div class="dato">
                        <strong>📧 Correo</strong>
                        <span>
                            <?php
                            echo htmlspecialchars(
                                $profesor["correo"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </span>
                    </div>

                    <div class="dato">
                        <strong>📱 Teléfono</strong>
                        <span>
                            <?php
                            echo htmlspecialchars(
                                $profesor["telefono"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </span>
                    </div>

                    <div class="dato">
                        <strong>🔵 Estado</strong>
                        <span>
                            <?php
                            echo htmlspecialchars(
                                $profesor["estado"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </span>
                    </div>

                </div>

            </section>

            <!-- Participaciones PAE -->
            <button
                type="button"
                class="estadistica boton-estadistica"
                id="botonParticipaciones"
                aria-expanded="false"
                aria-controls="detalleParticipaciones"
            >
                <span class="numero">
                    <?= $total_registros ?>
                </span>

                <span class="titulo-estadistica">
                    🍽️ Registros PAE asociados
                </span>

                <span class="texto-ver-grupos">
                    Pulsa para consultar fechas y grupos ↓
                </span>
            </button>

            <!-- Detalle de fechas y grupos -->
            <div id="detalleParticipaciones" hidden>

                <?php if (!empty($grupos_por_fecha)): ?>

                    <?php foreach ($grupos_por_fecha as $fecha => $grupos): ?>

                        <details class="fecha-participacion">

                            <summary>
                                📅 <?= htmlspecialchars($fecha, ENT_QUOTES, "UTF-8") ?>

                                <span>
                                    <?= count($grupos) ?>
                                    <?= count($grupos) === 1 ? "grupo" : "grupos" ?>
                                </span>
                            </summary>

                            <div class="grupos-fecha">

                                <?php foreach ($grupos as $grupo): ?>

                                    <a
                                        class="tarjeta-grupo"
                                        href="ver_grupo.php?id_curso=<?= (int) $grupo["id_curso"] ?>&fecha=<?= urlencode($fecha) ?>"
                                    >
                                        <strong>
                                            🏫 <?= htmlspecialchars($grupo["nombre_curso"], ENT_QUOTES, "UTF-8") ?>
                                        </strong>

                                        <span>
                                            Jornada:
                                            <?= htmlspecialchars($grupo["nombre_jornada"], ENT_QUOTES, "UTF-8") ?>
                                        </span>

                                        <span>
                                            Registros de esta participación:
                                            <?= (int) $grupo["total_registros"] ?>
                                        </span>

                                        <span class="ver-detalle">
                                            Ver información del grupo →
                                        </span>
                                    </a>

                                <?php endforeach; ?>

                            </div>

                        </details>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="sin-participaciones">
                        Todavía no tienes participaciones registradas.
                    </div>

                <?php endif; ?>

            </div>

        </div>
    </main>

    <script>
        function abrirMenuFoto() {
            const menu = document.getElementById("menuFoto");

            if (menu) {
                menu.style.display = "block";
            }
        }

        function cerrarMenuFoto() {
            const menu = document.getElementById("menuFoto");

            if (menu) {
                menu.style.display = "none";
            }
        }

        function enviarFoto() {
            const input = document.getElementById("foto");

            if (input && input.files.length > 0) {
                document.getElementById("formFoto").submit();
            }
        }

        function quitarFoto() {
            if (confirm("¿Quieres quitar tu foto de perfil?")) {
                window.location.href = "quitar_foto.php";
            }
        }

        // Cerrar el menú al hacer clic fuera de la foto
        document.addEventListener("click", function(event) {
            const contenedor = document.querySelector(".foto-contenedor");
            const menu = document.getElementById("menuFoto");

            if (
                contenedor &&
                menu &&
                !contenedor.contains(event.target)
            ) {
                menu.style.display = "none";
            }
        });

        // Mostrar u ocultar las participaciones PAE
        const botonParticipaciones =
            document.getElementById("botonParticipaciones");

        const detalleParticipaciones =
            document.getElementById("detalleParticipaciones");

        if (botonParticipaciones && detalleParticipaciones) {
            botonParticipaciones.addEventListener("click", function() {
                const abierto =
                    this.getAttribute("aria-expanded") === "true";

                this.setAttribute("aria-expanded", String(!abierto));
                detalleParticipaciones.hidden = abierto;
            });
        }
    </script>

</body>
</html>
