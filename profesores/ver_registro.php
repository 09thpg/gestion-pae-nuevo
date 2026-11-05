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


/* =====================================================
   VALIDAR REGISTRO
===================================================== */

if (!isset($_GET["id_entrega"]) || empty($_GET["id_entrega"])) {
    header("Location: mis_registros.php");
    exit();
}

$id_entrega = intval($_GET["id_entrega"]);


/* =====================================================
   DATOS DEL REGISTRO PAE
===================================================== */

$sql_entrega = "
    SELECT
        e.id_entrega,
        e.fecha,
        e.estado,
        e.observaciones,

        c.nombre AS nombre_curso,
        j.nombre AS nombre_jornada,

        CONCAT(
            p.nombres,
            ' ',
            p.apellidos
        ) AS profesor,

        CONCAT(
            pp.nombres,
            ' ',
            pp.apellidos
        ) AS personal_pae

    FROM entregas_pae e

    INNER JOIN cursos c
        ON e.id_curso = c.id_curso

    INNER JOIN jornadas j
        ON e.id_jornada = j.id_jornada

    LEFT JOIN profesores p
        ON e.id_profesor = p.id_profesor

    LEFT JOIN personal_pae pp
        ON e.id_personal = pp.id_personal

    WHERE e.id_entrega = ?
";


$stmt_entrega = $conexion->prepare($sql_entrega);
$stmt_entrega->bind_param("i", $id_entrega);
$stmt_entrega->execute();

$resultado_entrega = $stmt_entrega->get_result();


if ($resultado_entrega->num_rows == 0) {
    header("Location: mis_registros.php");
    exit();
}


$entrega = $resultado_entrega->fetch_assoc();


/* =====================================================
   ESTUDIANTES DEL REGISTRO
===================================================== */

$sql_estudiantes = "
    SELECT

        de.id_detalle,
        de.recibido,
        de.observacion,

        es.id_estudiante,
        es.documento,
        es.nombres,
        es.apellidos

    FROM detalle_entrega de

    INNER JOIN estudiantes es
        ON de.id_estudiante = es.id_estudiante

    WHERE de.id_entrega = ?

    ORDER BY
        es.apellidos ASC,
        es.nombres ASC
";


$stmt_estudiantes = $conexion->prepare($sql_estudiantes);
$stmt_estudiantes->bind_param("i", $id_entrega);
$stmt_estudiantes->execute();

$estudiantes = $stmt_estudiantes->get_result();


/* =====================================================
   CONTADORES
===================================================== */

$total = $estudiantes->num_rows;

$recibieron = 0;
$no_recibieron = 0;


/*
 * Guardamos los estudiantes en un arreglo para
 * poder recorrerlos nuevamente después.
 */

$lista_estudiantes = [];

while ($estudiante = $estudiantes->fetch_assoc()) {

    $lista_estudiantes[] = $estudiante;

    if ($estudiante["recibido"] == 1) {
        $recibieron++;
    } else {
        $no_recibieron++;
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

    <title>Detalle del registro PAE</title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f4f1fc;
        }


        .contenido {

            margin-left: 250px;

            padding: 40px;
        }


        .volver {

            display: inline-block;

            margin-bottom: 20px;

            text-decoration: none;

            color: #5b2c83;

            font-weight: bold;
        }


        /* ==========================================
           ENCABEZADO
        ========================================== */

        .encabezado {

            background: linear-gradient(
                135deg,
                #7650d6,
                #19add1
            );

            color: white;

            padding: 28px;

            border-radius: 20px;

            margin-bottom: 25px;
        }


        .encabezado h1 {

            margin: 0 0 10px;
        }


        .encabezado p {

            margin: 6px 0;
        }


        /* ==========================================
           INFORMACIÓN
        ========================================== */

        .informacion {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 25px;
        }


        .dato {

            background: white;

            padding: 18px;

            border-radius: 15px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.06);
        }


        .dato strong {

            display: block;

            color: #5b2c83;

            margin-bottom: 7px;
        }


        /* ==========================================
           RESUMEN
        ========================================== */

        .resumen {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 25px;
        }


        .tarjeta-resumen {

            background: white;

            padding: 20px;

            border-radius: 16px;

            text-align: center;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.06);
        }


        .numero {

            display: block;

            font-size: 30px;

            font-weight: bold;

            color: #6f42c1;

            margin-bottom: 5px;
        }


        /* ==========================================
           TABLA
        ========================================== */

        .tabla-contenedor {

            background: white;

            padding: 20px;

            border-radius: 18px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.06);

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;
        }


        th {

            background: #f1ebfb;

            color: #5b2c83;

            padding: 14px;

            text-align: left;
        }


        td {

            padding: 14px;

            border-bottom: 1px solid #eee;
        }


        tr:hover {

            background: #faf8ff;
        }


        /* ==========================================
           ESTADOS
        ========================================== */

        .estado {

            display: inline-block;

            padding: 7px 12px;

            border-radius: 20px;

            font-weight: bold;

            font-size: 13px;
        }


        .recibido {

            background: #e8f7ed;

            color: #187a3d;
        }


        .no-recibido {

            background: #fbeaea;

            color: #a33;
        }


        .observacion {

            color: #666;

            font-size: 14px;
        }


        @media (max-width: 900px) {

            .contenido {

                margin-left: 0;

                padding: 20px;
            }


            .informacion {

                grid-template-columns: 1fr;
            }


            .resumen {

                grid-template-columns: 1fr;
            }


            table {

                min-width: 700px;
            }

        }

    </style>

</head>


<body>


<?php include "../componentes/sidebar_profesor.php"; ?>


<div class="contenido">


    <a
        href="mis_registros.php"
        class="volver"
    >
        ← Volver a registros PAE
    </a>


    <!-- ==========================================
         ENCABEZADO
    ========================================== -->

    <div class="encabezado">

        <h1>

            🍽️
            Registro PAE -
            <?= htmlspecialchars(
                $entrega["nombre_curso"]
            ) ?>

        </h1>


        <p>

            📅 Fecha:
            <?= htmlspecialchars(
                $entrega["fecha"]
            ) ?>

        </p>


        <p>

            🏫 Jornada:
            <?= htmlspecialchars(
                $entrega["nombre_jornada"]
            ) ?>

        </p>


        <p>

            📌 Estado:
            <?= htmlspecialchars(
                $entrega["estado"]
            ) ?>

        </p>

    </div>


    <!-- ==========================================
         INFORMACIÓN DEL REGISTRO
    ========================================== -->

    <div class="informacion">


        <div class="dato">

            <strong>
                👨‍🏫 Profesor
            </strong>

            <?= htmlspecialchars(
                $entrega["profesor"]
                ?? "No registrado"
            ) ?>

        </div>


        <div class="dato">

            <strong>
                👨‍🍳 Personal PAE
            </strong>

            <?= htmlspecialchars(
                $entrega["personal_pae"]
                ?? "No registrado"
            ) ?>

        </div>


        <div class="dato">

            <strong>
                📝 Observaciones
            </strong>

            <?= htmlspecialchars(
                $entrega["observaciones"]
                ?? "Sin observaciones"
            ) ?>

        </div>


    </div>


    <!-- ==========================================
         RESUMEN
    ========================================== -->

    <div class="resumen">


        <div class="tarjeta-resumen">

            <span class="numero">
                <?= $total ?>
            </span>

            👨‍🎓 Total estudiantes

        </div>


        <div class="tarjeta-resumen">

            <span class="numero">
                <?= $recibieron ?>
            </span>

            🍎 Recibieron

        </div>


        <div class="tarjeta-resumen">

            <span class="numero">
                <?= $no_recibieron ?>
            </span>

            ❌ No recibieron

        </div>


    </div>


    <!-- ==========================================
         LISTA DE ESTUDIANTES
    ========================================== -->

    <div class="tabla-contenedor">

        <h2 style="color:#5b2c83;">

            👨‍🎓 Estudiantes del registro

        </h2>


        <?php if ($total > 0): ?>


            <table>

                <thead>

                    <tr>

                        <th>#</th>

                        <th>👨‍🎓 Estudiante</th>

                        <th>🪪 Documento</th>

                        <th>🍎 Estado</th>

                        <th>📝 Observación</th>

                    </tr>

                </thead>


                <tbody>


                    <?php

                    $numero = 1;

                    foreach (
                        $lista_estudiantes
                        as $estudiante
                    ):

                    ?>


                        <tr>


                            <td>

                                <?= $numero++ ?>

                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $estudiante["nombres"]
                                    ) ?>

                                    <?= htmlspecialchars(
                                        $estudiante["apellidos"]
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $estudiante["documento"]
                                ) ?>

                            </td>


                            <td>


                                <?php
                                if (
                                    $estudiante["recibido"] == 1
                                ):
                                ?>

                                    <span
                                        class="estado recibido"
                                    >
                                        🍎 Recibió
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="estado no-recibido"
                                    >
                                        ❌ No recibió
                                    </span>

                                <?php endif; ?>


                            </td>


                            <td>

                                <span class="observacion">

                                    <?= !empty(
                                        $estudiante["observacion"]
                                    )
                                        ? htmlspecialchars(
                                            $estudiante["observacion"]
                                        )
                                        : "Sin observación"
                                    ?>

                                </span>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>

            </table>


        <?php else: ?>


            <p style="text-align:center; color:#666; padding:30px;">

                📭 No hay estudiantes registrados
                en esta entrega.

            </p>


        <?php endif; ?>


    </div>


</div>


</body>

</html>