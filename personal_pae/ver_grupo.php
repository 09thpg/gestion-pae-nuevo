<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");


/* ==========================================
   OBTENER ID DEL GRUPO
   ========================================== */

$id_entrega = isset($_GET["id_entrega"])
    ? intval($_GET["id_entrega"])
    : 0;


if ($id_entrega <= 0) {

    header("Location: mis_registros.php");
    exit();

}


/* ==========================================
   OBTENER PERSONAL PAE
   ========================================== */

$id_usuario = $_SESSION["id_usuario"];

$sqlPersonal = "
    SELECT id_personal
    FROM personal_pae
    WHERE id_usuario = ?
    LIMIT 1
";

$stmtPersonal = $conexion->prepare($sqlPersonal);

$stmtPersonal->bind_param(
    "i",
    $id_usuario
);

$stmtPersonal->execute();

$resultadoPersonal =
    $stmtPersonal->get_result();

$personal =
    $resultadoPersonal->fetch_assoc();


if (!$personal) {

    die("No se encontró el Personal PAE.");

}

$id_personal = $personal["id_personal"];


/* ==========================================
   OBTENER INFORMACIÓN DEL GRUPO
   ========================================== */

$sqlGrupo = "
    SELECT
        e.id_entrega,
        e.fecha,
        e.estado,
        e.observaciones,

        c.nombre AS curso,

        j.nombre AS jornada,

        CONCAT(
            p.nombres,
            ' ',
            p.apellidos
        ) AS profesor

    FROM entregas_pae e

    INNER JOIN cursos c
        ON e.id_curso = c.id_curso

    INNER JOIN jornadas j
        ON e.id_jornada = j.id_jornada

    INNER JOIN profesores p
        ON e.id_profesor = p.id_profesor

    WHERE e.id_entrega = ?
    AND e.id_personal = ?

    LIMIT 1
";

$stmtGrupo = $conexion->prepare($sqlGrupo);

$stmtGrupo->bind_param(
    "ii",
    $id_entrega,
    $id_personal
);

$stmtGrupo->execute();

$resultadoGrupo =
    $stmtGrupo->get_result();

$grupo =
    $resultadoGrupo->fetch_assoc();


if (!$grupo) {

    die("No se encontró el grupo.");

}


/* ==========================================
   OBTENER ESTUDIANTES DEL GRUPO
   ========================================== */

$sqlEstudiantes = "
    SELECT
        d.id_detalle,

        CONCAT(
            est.nombres,
            ' ',
            est.apellidos
        ) AS estudiante,

        est.documento,

        d.recibido,

        d.observacion

    FROM detalle_entrega d

    INNER JOIN estudiantes est
        ON d.id_estudiante = est.id_estudiante

    WHERE d.id_entrega = ?

    ORDER BY
        est.apellidos ASC,
        est.nombres ASC
";

$stmtEstudiantes =
    $conexion->prepare($sqlEstudiantes);

$stmtEstudiantes->bind_param(
    "i",
    $id_entrega
);

$stmtEstudiantes->execute();

$resultadoEstudiantes =
    $stmtEstudiantes->get_result();


/* ==========================================
   CONTADORES
   ========================================== */

$total = 0;
$recibieron = 0;
$noRecibieron = 0;


/* Guardamos los estudiantes en un arreglo */

$estudiantes = [];

while (
    $estudiante =
    $resultadoEstudiantes->fetch_assoc()
) {

    $estudiantes[] = $estudiante;

    $total++;

    if ($estudiante["recibido"] == 1) {

        $recibieron++;

    } else {

        $noRecibieron++;

    }

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
        Ver grupo
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
                50px 25px;

            margin-left: 240px;

        }


        .contenedor {

            width: 100%;

            max-width: 1100px;

            margin: auto;

        }


        /* =================================
           ENCABEZADO
           ================================= */

        .volver {

            display: inline-block;

            margin-bottom: 20px;

            color: #6d3bd1;

            text-decoration: none;

            font-weight: bold;

        }


        .volver:hover {

            text-decoration: underline;

        }


        .encabezado {

            margin-bottom: 25px;

        }


        .icono {

            font-size: 48px;

            margin-bottom: 8px;

        }


        .encabezado h1 {

            color: #6d3bd1;

            font-size: 31px;

            margin-bottom: 8px;

        }


        .encabezado p {

            color: #666;

            font-size: 15px;

        }


        /* =================================
           INFORMACIÓN DEL GRUPO
           ================================= */

        .informacion {

            background: white;

            padding: 22px;

            border-radius: 18px;

            margin-bottom: 20px;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,.06);

        }


        .informacion h2 {

            color: #6d3bd1;

            font-size: 20px;

            margin-bottom: 15px;

        }


        .datos {

            display: flex;

            flex-wrap: wrap;

            gap: 10px;

        }


        .dato {

            background: #f7f4ff;

            padding: 10px 14px;

            border-radius: 10px;

            color: #555;

            font-size: 14px;

        }


        .dato strong {

            color: #6d3bd1;

        }


        .estado {

            display: inline-block;

            margin-top: 15px;

            padding: 7px 12px;

            border-radius: 8px;

            font-weight: bold;

            font-size: 13px;

        }


        .estado.proceso {

            background: #fff4d6;

            color: #9a6b00;

        }


        .estado.terminado {

            background: #e8fff1;

            color: #16834b;

        }


        /* =================================
           RESUMEN
           ================================= */

        .resumen {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 20px;

        }


        .resumen-card {

            background: white;

            padding: 18px;

            border-radius: 15px;

            box-shadow:
                0 6px 20px
                rgba(0,0,0,.05);

            text-align: center;

        }


        .resumen-card .numero {

            display: block;

            font-size: 27px;

            font-weight: bold;

            color: #6d3bd1;

            margin-bottom: 5px;

        }


        .resumen-card span {

            color: #777;

            font-size: 13px;

        }


        /* =================================
           TABLA
           ================================= */

        .tarjeta {

            background: white;

            padding: 25px;

            border-radius: 20px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 750px;

        }


        thead {

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #6d3bd1
                );

            color: white;

        }


        th {

            padding: 14px 12px;

            text-align: left;

            font-size: 14px;

        }


        td {

            padding: 13px 12px;

            border-bottom:
                1px solid #eee;

            color: #555;

            font-size: 14px;

        }


        tbody tr:hover {

            background: #faf8ff;

        }


        /* =================================
           ESTADOS
           ================================= */

        .recibio {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 8px;

            background: #e8fff1;

            color: #16834b;

            font-weight: bold;

            font-size: 12px;

        }


        .no-recibio {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 8px;

            background: #fff0f0;

            color: #c0392b;

            font-weight: bold;

            font-size: 12px;

        }


        /* =================================
           ELIMINAR
           ================================= */

        .btn-eliminar {

            display: inline-block;

            padding: 7px 10px;

            border-radius: 8px;

            background: #fff0f0;

            color: #c0392b;

            text-decoration: none;

            font-weight: bold;

            font-size: 12px;

            border: 1px solid #ffd4d4;

        }


        .btn-eliminar:hover {

            background: #ffe0e0;

        }


        /* =================================
           SIN ESTUDIANTES
           ================================= */

        .sin-estudiantes {

            text-align: center;

            padding: 40px;

            color: #777;

        }


        .sin-estudiantes .emoji {

            font-size: 50px;

            margin-bottom: 10px;

        }


        /* =================================
           MÓVIL
           ================================= */

        @media (max-width: 700px) {

            .contenido {

                margin-left: 0;

                padding:
                    40px 15px;

            }


            .resumen {

                grid-template-columns: 1fr;

            }


            .tarjeta {

                padding: 18px;

            }

        }

    </style>

</head>


<body>


<?php include("../componentes/sidebar_personal.php"); ?>


<main class="contenido">


    <div class="contenedor">


        <a
            href="mis_registros.php"
            class="volver"
        >

            ← Volver a mis registros

        </a>


        <!-- ENCABEZADO -->

        <div class="encabezado">

            <div class="icono">
                🏫
            </div>

            <h1>
                Grupo <?php echo htmlspecialchars($grupo["curso"]); ?>
            </h1>

            <p>
                Detalle de los estudiantes registrados en este grupo.
            </p>

        </div>


        <!-- INFORMACIÓN -->

        <div class="informacion">

            <h2>
                📋 Información del grupo
            </h2>


            <div class="datos">


                <div class="dato">

                    📅

                    <strong>
                        Fecha:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $grupo["fecha"]
                    );
                    ?>

                </div>


                <div class="dato">

                    🌞

                    <strong>
                        Jornada:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $grupo["jornada"]
                    );
                    ?>

                </div>


                <div class="dato">

                    👨‍🏫

                    <strong>
                        Profesor:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $grupo["profesor"]
                    );
                    ?>

                </div>


            </div>


            <?php if ($grupo["estado"] === "Terminado") { ?>

                <span class="estado terminado">

                    🟢 Grupo terminado

                </span>

            <?php } else { ?>

                <span class="estado proceso">

                    🟡 Grupo en proceso

                </span>

            <?php } ?>


        </div>


        <!-- RESUMEN -->

        <div class="resumen">


            <div class="resumen-card">

                <span class="numero">
                    <?php echo $total; ?>
                </span>

                <span>
                    👥 Total estudiantes
                </span>

            </div>


            <div class="resumen-card">

                <span class="numero">
                    <?php echo $recibieron; ?>
                </span>

                <span>
                    ✅ Recibieron
                </span>

            </div>


            <div class="resumen-card">

                <span class="numero">
                    <?php echo $noRecibieron; ?>
                </span>

                <span>
                    ❌ No recibieron
                </span>

            </div>


        </div>


        <!-- TABLA -->

        <div class="tarjeta">


            <?php if ($total > 0) { ?>


                <table>

                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Estudiante
                            </th>

                            <th>
                                Documento
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Observación
                            </th>

                            <th>
                                Acción
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $numero = 1;

                    foreach (
                        $estudiantes
                        as $estudiante
                    ) {

                    ?>


                        <tr>


                            <td>

                                <?php
                                echo $numero++;
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $estudiante["estudiante"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $estudiante["documento"]
                                );
                                ?>

                            </td>


                            <td>


                                <?php if (
                                    $estudiante["recibido"] == 1
                                ) { ?>

                                    <span class="recibio">

                                        ✅ Recibió

                                    </span>

                                <?php } else { ?>

                                    <span class="no-recibio">

                                        ❌ No recibió

                                    </span>

                                <?php } ?>


                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $estudiante["observacion"]
                                    ?: "—"
                                );

                                ?>

                            </td>


                            <td>

                                <a
                                    href="eliminar_registro.php?id_detalle=<?php echo (int)$estudiante["id_detalle"]; ?>"
                                    class="btn-eliminar"
                                    onclick="return confirm('¿Seguro que deseas eliminar este registro?');"
                                >

                                    🗑️ Eliminar

                                </a>

                            </td>


                        </tr>


                    <?php } ?>


                    </tbody>

                </table>


            <?php } else { ?>


                <div class="sin-estudiantes">

                    <div class="emoji">
                        🍎
                    </div>

                    <p>
                        Este grupo todavía no tiene estudiantes registrados.
                    </p>

                </div>


            <?php } ?>


        </div>


    </div>


</main>


</body>

</html>