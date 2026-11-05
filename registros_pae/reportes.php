<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["id_rol"] != 1) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";


/* =====================================================
   FECHA SELECCIONADA
===================================================== */

$fecha = isset($_GET["fecha"])
    ? $_GET["fecha"]
    : "";

$id_curso = isset($_GET["id_curso"])
    ? intval($_GET["id_curso"])
    : 0;


/* =====================================================
   HISTORIAL DE FECHAS
===================================================== */

$sql_fechas = "
    SELECT
        e.fecha,

        COUNT(
            DISTINCT
            CASE
                WHEN d.recibido = 1
                THEN d.id_estudiante
            END
        ) AS comieron,

        COUNT(
            DISTINCT
            CASE
                WHEN d.recibido = 0
                THEN d.id_estudiante
            END
        ) AS no_comieron,

        COUNT(
            DISTINCT e.id_curso
        ) AS grupos

    FROM entregas_pae e

    INNER JOIN detalle_entrega d
        ON e.id_entrega = d.id_entrega

    GROUP BY e.fecha

    ORDER BY e.fecha DESC
";

$resultado_fechas = $conexion->query($sql_fechas);

if (!$resultado_fechas) {
    die(
        "Error al consultar el historial: "
        . $conexion->error
    );
}


/* =====================================================
   INFORMACIÓN DE LA FECHA SELECCIONADA
===================================================== */

$total_comieron = 0;
$total_no_comieron = 0;
$total_grupos = 0;

$grupos = null;

if (!empty($fecha)) {

    /* -----------------------------------------------
       TOTAL INSTITUCIÓN
    ------------------------------------------------ */

    $sql_totales = "
        SELECT

            COUNT(
                DISTINCT
                CASE
                    WHEN d.recibido = 1
                    THEN d.id_estudiante
                END
            ) AS comieron,

            COUNT(
                DISTINCT
                CASE
                    WHEN d.recibido = 0
                    THEN d.id_estudiante
                END
            ) AS no_comieron,

            COUNT(
                DISTINCT e.id_curso
            ) AS grupos

        FROM entregas_pae e

        INNER JOIN detalle_entrega d
            ON e.id_entrega = d.id_entrega

        WHERE e.fecha = ?
    ";

    $stmt_totales =
        $conexion->prepare($sql_totales);

    $stmt_totales->bind_param(
        "s",
        $fecha
    );

    $stmt_totales->execute();

    $resultado_totales =
        $stmt_totales->get_result();

    if ($resultado_totales->num_rows > 0) {

        $totales =
            $resultado_totales->fetch_assoc();

        $total_comieron =
            $totales["comieron"] ?? 0;

        $total_no_comieron =
            $totales["no_comieron"] ?? 0;

        $total_grupos =
            $totales["grupos"] ?? 0;
    }


    /* -----------------------------------------------
       GRUPOS DE ESA FECHA
    ------------------------------------------------ */

    
$sql_grupos = "
    SELECT
        e.id_curso,
        c.nombre AS curso,
        j.nombre AS jornada,

        COALESCE(
    MAX(
        CASE
            WHEN p.id_profesor IS NOT NULL
            THEN CONCAT(TRIM(p.nombres), ' ', TRIM(p.apellidos))
        END
    ),
    'Profesor eliminado'
) AS profesores,

        COUNT(
            DISTINCT CASE
                WHEN d.recibido = 1
                THEN d.id_estudiante
            END
        ) AS comieron,

        COUNT(
            DISTINCT CASE
                WHEN d.recibido = 0
                THEN d.id_estudiante
            END
        ) AS no_comieron,

        COUNT(
            DISTINCT d.id_estudiante
        ) AS total_estudiantes,

        COUNT(
            DISTINCT e.id_entrega
        ) AS registros

    FROM entregas_pae e

    INNER JOIN detalle_entrega d
        ON e.id_entrega = d.id_entrega

    INNER JOIN cursos c
        ON e.id_curso = c.id_curso

    INNER JOIN jornadas j
        ON e.id_jornada = j.id_jornada

    LEFT JOIN profesores p
        ON e.id_profesor = p.id_profesor

    WHERE e.fecha = ?

    GROUP BY
        e.id_curso,
        c.nombre,
        j.nombre

    ORDER BY c.nombre ASC
";


    $stmt_grupos =
        $conexion->prepare($sql_grupos);

    $stmt_grupos->bind_param(
        "s",
        $fecha
    );

    $stmt_grupos->execute();

    $grupos =
        $stmt_grupos->get_result();
}


/* =====================================================
   GRUPO SELECCIONADO
===================================================== */

$estudiantes_grupo = null;

$nombre_grupo = "";
$nombre_jornada = "";

if (
    !empty($fecha) &&
    $id_curso > 0
) {

    /* -----------------------------------------------
       DATOS DEL GRUPO
    ------------------------------------------------ */

    $sql_grupo = "
        SELECT
            c.nombre AS curso,
            j.nombre AS jornada

        FROM cursos c

        INNER JOIN jornadas j
            ON c.id_jornada = j.id_jornada

        WHERE c.id_curso = ?
    ";

    $stmt_grupo =
        $conexion->prepare($sql_grupo);

    $stmt_grupo->bind_param(
        "i",
        $id_curso
    );

    $stmt_grupo->execute();

    $resultado_grupo =
        $stmt_grupo->get_result();

    if ($resultado_grupo->num_rows > 0) {

        $grupo =
            $resultado_grupo->fetch_assoc();

        $nombre_grupo =
            $grupo["curso"];

        $nombre_jornada =
            $grupo["jornada"];
    }


    /* -----------------------------------------------
       ESTUDIANTES DEL GRUPO
    ------------------------------------------------ */

    $sql_estudiantes = "
        SELECT

            es.id_estudiante,

            es.documento,

            es.nombres,

            es.apellidos,

            MAX(d.recibido) AS recibido,

            GROUP_CONCAT(
                DISTINCT
                NULLIF(d.observacion, '')
                SEPARATOR ' | '
            ) AS observacion

        FROM detalle_entrega d

        INNER JOIN entregas_pae e
            ON d.id_entrega = e.id_entrega

        INNER JOIN estudiantes es
            ON d.id_estudiante =
               es.id_estudiante

        WHERE
            e.fecha = ?

            AND e.id_curso = ?

        GROUP BY

            es.id_estudiante,
            es.documento,
            es.nombres,
            es.apellidos

        ORDER BY
            es.apellidos ASC,
            es.nombres ASC
    ";

    $stmt_estudiantes =
        $conexion->prepare(
            $sql_estudiantes
        );

    $stmt_estudiantes->bind_param(
        "si",
        $fecha,
        $id_curso
    );

    $stmt_estudiantes->execute();

    $estudiantes_grupo =
        $stmt_estudiantes->get_result();
}


/* =====================================================
   FORMATO DE FECHA
===================================================== */

function fechaBonita($fecha)
{

    if (empty($fecha)) {
        return "";
    }

    $partes =
        explode("-", $fecha);

    if (count($partes) != 3) {
        return $fecha;
    }

    return
        $partes[2]
        . "/"
        . $partes[1]
        . "/"
        . $partes[0];
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Historial PAE
    </title>


    <style>

        * {

            margin: 0;
            padding: 0;

            box-sizing: border-box;

            font-family: Arial, sans-serif;

        }


        body {

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #eef3ff,
                    #fdfcff
                );

        }


        .contenido {

            min-height: 100vh;

            padding:
                80px 25px 50px;

            margin-left: 220px;

        }


        .contenedor {

            width: 100%;

            max-width: 1200px;

            margin: auto;

        }


        /* =====================================
           ENCABEZADO
        ===================================== */

        .encabezado {

            margin-bottom: 25px;

        }


        .encabezado h1 {

            color: #6d3bd1;

            font-size: 32px;

            margin-bottom: 8px;

        }


        .encabezado p {

            color: #666;

        }


        /* =====================================
           BUSCADOR
        ===================================== */

        .buscador {

            background: white;

            padding: 22px;

            border-radius: 20px;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,.07);

            margin-bottom: 25px;

        }


        .buscador h2 {

            color: #5b2c83;

            margin-bottom: 15px;

        }


        .fila-busqueda {

            display: flex;

            gap: 12px;

            align-items: end;

        }


        .campo {

            flex: 1;

        }


        .campo label {

            display: block;

            color: #555;

            font-weight: bold;

            margin-bottom: 7px;

        }


        .campo input {

            width: 100%;

            padding: 13px;

            border: 1px solid #ddd;

            border-radius: 10px;

            font-size: 15px;

        }


        .btn {

            border: none;

            padding: 13px 20px;

            border-radius: 10px;

            background: #6d3bd1;

            color: white;

            font-weight: bold;

            cursor: pointer;

            text-decoration: none;

            display: inline-block;

        }


        .btn:hover {

            background: #5725b5;

        }


        .btn-secundario {

            background: #eeeeee;

            color: #555;

        }


        .btn-secundario:hover {

            background: #dddddd;

        }


        /* =====================================
           RESUMEN
        ===================================== */

        .resumen {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;

            margin-bottom: 25px;

        }


        .resumen-card {

            background: white;

            padding: 22px;

            border-radius: 18px;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,.07);

        }


        .resumen-icono {

            font-size: 30px;

            margin-bottom: 8px;

        }


        .resumen-card h3 {

            color: #666;

            font-size: 15px;

            margin-bottom: 8px;

        }


        .numero {

            color: #6d3bd1;

            font-size: 30px;

            font-weight: bold;

        }


        /* =====================================
           TARJETA GENERAL
        ===================================== */

        .tarjeta {

            background: white;

            padding: 25px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

            margin-bottom: 25px;

        }


        .tarjeta h2 {

            color: #6d3bd1;

            margin-bottom: 8px;

        }


        .tarjeta p {

            color: #666;

        }


        /* =====================================
           TABLA FECHAS
        ===================================== */

        .tabla-contenedor {

            width: 100%;

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 700px;

            margin-top: 15px;

        }


        th {

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #00bcd4
                );

            color: white;

            padding: 14px;

            text-align: left;

            font-size: 14px;

        }


        td {

            padding: 13px;

            border-bottom:
                1px solid #eeeeee;

            color: #555;

        }


        tbody tr:hover {

            background: #faf8ff;

        }


        /* =====================================
           GRUPOS
        ===================================== */

        .grupos {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 18px;

            margin-top: 20px;

        }


        .grupo {

            background: #faf8ff;

            border: 1px solid #eee5ff;

            border-radius: 18px;

            padding: 20px;

            text-decoration: none;

            color: inherit;

            transition: .2s;

        }


        .grupo:hover {

            transform:
                translateY(-3px);

            box-shadow:
                0 8px 20px
                rgba(109,59,209,.15);

        }


        .grupo h3 {

            color: #5b2c83;

            font-size: 22px;

            margin-bottom: 5px;

        }


        .jornada {

            color: #777;

            margin-bottom: 15px;

        }


        .datos-grupo {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 8px;

        }


        .dato {

            background: white;

            padding: 10px;

            border-radius: 10px;

            text-align: center;

        }


        .dato strong {

            display: block;

            color: #6d3bd1;

            font-size: 19px;

        }


        .dato span {

            color: #777;

            font-size: 12px;

        }


        .ver-grupo {

            margin-top: 15px;

            color: #6d3bd1;

            font-weight: bold;

        }


        /* =====================================
           ESTUDIANTES
        ===================================== */

        .encabezado-grupo {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 15px;

            margin-bottom: 15px;

        }


        .recibio {

            display: inline-block;

            background: #e8f8ee;

            color: #218838;

            padding: 6px 10px;

            border-radius: 15px;

            font-weight: bold;

            font-size: 13px;

        }


        .no-recibio {

            display: inline-block;

            background: #fff0f0;

            color: #d33;

            padding: 6px 10px;

            border-radius: 15px;

            font-weight: bold;

            font-size: 13px;

        }


        .sin-dato {

            color: #999;

            font-style: italic;

        }


        /* =====================================
           VACÍO
        ===================================== */

        .vacio {

            text-align: center;

            padding: 40px 20px;

            color: #777;

        }


        .vacio .emoji {

            font-size: 45px;

            margin-bottom: 10px;

        }


        .vacio h2 {

            color: #6d3bd1;

            margin-bottom: 8px;

        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 900px) {

            .contenido {

                margin-left: 0;

                padding:
                    80px 15px 40px;

            }


            .resumen {

                grid-template-columns: 1fr;

            }


            .grupos {

                grid-template-columns: 1fr;

            }


            .fila-busqueda {

                flex-direction: column;

                align-items: stretch;

            }


            .datos-grupo {

                grid-template-columns:
                    repeat(3, 1fr);

            }

        }


        @media (max-width: 600px) {

            .encabezado h1 {

                font-size: 26px;

            }


            .tarjeta,
            .buscador {

                padding: 17px;

                border-radius: 16px;

            }


            .datos-grupo {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<?php include("../componentes/sidebar.php"); ?>


<main class="contenido">


<div class="contenedor">


    <!-- =====================================
         ENCABEZADO
    ====================================== -->

    <div class="encabezado">

        <h1>
            📊 Historial PAE
        </h1>

        <p>
            Consulta los registros históricos
            de alimentación de la institución.
        </p>

    </div>


    <!-- =====================================
         BUSCADOR POR FECHA
    ====================================== -->

    <div class="buscador">

        <h2>
            🔎 Buscar por fecha
        </h2>


        <form method="GET">

            <div class="fila-busqueda">

                <div class="campo">

                    <label>
                        📅 Selecciona una fecha
                    </label>

                    <input
                        type="date"
                        name="fecha"
                        value="<?= htmlspecialchars($fecha) ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn"
                >
                    🔍 Buscar
                </button>


                <a
                    href="reportes.php"
                    class="btn btn-secundario"
                >
                    Limpiar
                </a>

            </div>

        </form>

    </div>


    <?php if (empty($fecha)) { ?>


        <!-- =====================================
             HISTORIAL DE FECHAS
        ====================================== -->

        <div class="tarjeta">

            <h2>
                📚 Registros anteriores
            </h2>

            <p>
                Aquí aparecen las fechas que ya tienen
                registros PAE guardados.
            </p>


            <?php if ($resultado_fechas->num_rows > 0) { ?>


                <div class="tabla-contenedor">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    📅 Fecha
                                </th>

                                <th>
                                    🍽️ Comieron
                                </th>

                                <th>
                                    ❌ No comieron
                                </th>

                                <th>
                                    🏫 Grupos
                                </th>

                                <th>
                                    🔎 Acción
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while (
                            $fila =
                            $resultado_fechas->fetch_assoc()
                        ) { ?>


                            <tr>

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            fechaBonita(
                                                $fila["fecha"]
                                            )
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    🍽️

                                    <?= $fila["comieron"] ?>

                                </td>


                                <td>

                                    <?= $fila["no_comieron"] ?>

                                </td>


                                <td>

                                    🏫

                                    <?= $fila["grupos"] ?>

                                </td>


                                <td>

                                    <a
                                        href="reportes.php?fecha=<?= urlencode($fila["fecha"]) ?>"
                                        class="btn"
                                    >
                                        Ver fecha
                                    </a>

                                </td>

                            </tr>


                        <?php } ?>


                        </tbody>

                    </table>

                </div>


            <?php } else { ?>


                <div class="vacio">

                    <div class="emoji">
                        📭
                    </div>

                    <h2>
                        No hay registros todavía
                    </h2>

                    <p>
                        Cuando se guarde un registro PAE,
                        aparecerá aquí.
                    </p>

                </div>


            <?php } ?>


        </div>


    <?php } else { ?>


        <!-- =====================================
             RESUMEN DE LA FECHA
        ====================================== -->

        <div class="resumen">


            <div class="resumen-card">

                <div class="resumen-icono">
                    🍽️
                </div>

                <h3>
                    Estudiantes que comieron
                </h3>

                <div class="numero">
                    <?= $total_comieron ?>
                </div>

            </div>


            <div class="resumen-card">

                <div class="resumen-icono">
                    ❌
                </div>

                <h3>
                    No registraron comida
                </h3>

                <div class="numero">
                    <?= $total_no_comieron ?>
                </div>

            </div>


            <div class="resumen-card">

                <div class="resumen-icono">
                    🏫
                </div>

                <h3>
                    Grupos registrados
                </h3>

                <div class="numero">
                    <?= $total_grupos ?>
                </div>

            </div>


        </div>


        <!-- =====================================
             GRUPOS
        ====================================== -->

        <div class="tarjeta">

            <h2>
                🏫 Grupos del <?= htmlspecialchars(
                    fechaBonita($fecha)
                ) ?>
            </h2>

            <p>
                Selecciona un grupo para consultar
                sus estudiantes.
            </p>


            <?php if (
                $grupos &&
                $grupos->num_rows > 0
            ) { ?>


                <div class="grupos">


                <?php while (
                    $grupo =
                    $grupos->fetch_assoc()
                ) { ?>


                    <a
                        href="reportes.php?fecha=<?= urlencode($fecha) ?>&id_curso=<?= $grupo["id_curso"] ?>"
                        class="grupo"
                    >


                        <h3>

                            🏫

                            <?= htmlspecialchars(
                                $grupo["curso"]
                            ) ?>

                        </h3>


                        <div class="jornada">

                            🌅

                            <?= htmlspecialchars(
                                $grupo["jornada"]
                            ) ?>

                        </div>

                        
<div class="jornada">
    👨‍🏫
    <?= htmlspecialchars(
        !empty($grupo["profesores"])
            ? $grupo["profesores"]
            : "Profesor eliminado"
    ) ?>
</div>



                        <div class="datos-grupo">


                            <div class="dato">

                                <strong>
                                    <?= $grupo["comieron"] ?>
                                </strong>

                                <span>
                                    🍽️ Comieron
                                </span>

                            </div>


                            <div class="dato">

                                <strong>
                                    <?= $grupo["no_comieron"] ?>
                                </strong>

                                <span>
                                    ❌ No comieron
                                </span>

                            </div>


                            <div class="dato">

                                <strong>
                                    <?= $grupo["total_estudiantes"] ?>
                                </strong>

                                <span>
                                    👨‍🎓 Total
                                </span>

                            </div>


                        </div>


                        <div class="ver-grupo">

                            👆 Ver estudiantes →

                        </div>


                    </a>


                <?php } ?>


                </div>


            <?php } else { ?>


                <div class="vacio">

                    <div class="emoji">
                        📭
                    </div>

                    <h2>
                        No hay grupos registrados
                    </h2>

                    <p>
                        No existen entregas PAE para
                        esta fecha.
                    </p>

                </div>


            <?php } ?>


        </div>


        <?php if (
            $id_curso > 0 &&
            $estudiantes_grupo
        ) { ?>


            <!-- =====================================
                 ESTUDIANTES DEL GRUPO
            ====================================== -->

            <div class="tarjeta">


                <div class="encabezado-grupo">

                    <div>

                        <h2>

                            👨‍🎓

                            <?= htmlspecialchars(
                                $nombre_grupo
                            ) ?>

                        </h2>

                        <p>

                            🌅

                            <?= htmlspecialchars(
                                $nombre_jornada
                            ) ?>

                            ·

                            📅

                            <?= htmlspecialchars(
                                fechaBonita($fecha)
                            ) ?>

                        </p>

                    </div>


                    <a
                        href="reportes.php?fecha=<?= urlencode($fecha) ?>"
                        class="btn btn-secundario"
                    >
                        ← Volver a grupos
                    </a>

                </div>


                <?php if (
                    $estudiantes_grupo->num_rows > 0
                ) { ?>


                    <div class="tabla-contenedor">

                        <table>

                            <thead>

                                <tr>

                                    <th>
                                        👨‍🎓 Estudiante
                                    </th>

                                    <th>
                                        🪪 Documento
                                    </th>

                                    <th>
                                        🍽️ Estado
                                    </th>

                                    <th>
                                        📝 Observación
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php while (
                                $estudiante =
                                $estudiantes_grupo
                                    ->fetch_assoc()
                            ) { ?>


                                <tr>

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $estudiante["apellidos"]
                                            ) ?>

                                            <?= htmlspecialchars(
                                                $estudiante["nombres"]
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $estudiante["documento"]
                                        ) ?>

                                    </td>


                                    <td>


                                        <?php if (
                                            $estudiante["recibido"] == 1
                                        ) { ?>

                                            <span
                                                class="recibio"
                                            >
                                                ✅ Recibió PAE
                                            </span>

                                        <?php } else { ?>

                                            <span
                                                class="no-recibio"
                                            >
                                                ❌ No recibió
                                            </span>

                                        <?php } ?>


                                    </td>


                                    <td>

                                        <?php

                                        if (
                                            !empty(
                                                $estudiante[
                                                    "observacion"
                                                ]
                                            )
                                        ) {

                                            echo htmlspecialchars(
                                                $estudiante[
                                                    "observacion"
                                                ]
                                            );

                                        } else {

                                            echo '
                                                <span
                                                    class="sin-dato"
                                                >
                                                    Sin observación
                                                </span>
                                            ';

                                        }

                                        ?>

                                    </td>

                                </tr>


                            <?php } ?>


                            </tbody>

                        </table>

                    </div>


                <?php } else { ?>


                    <div class="vacio">

                        <div class="emoji">
                            📭
                        </div>

                        <h2>
                            No hay estudiantes registrados
                        </h2>

                    </div>


                <?php } ?>


            </div>


        <?php } ?>


    <?php } ?>


</div>

</main>


</body>

</html>