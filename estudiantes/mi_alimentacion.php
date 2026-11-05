<?php
session_start();

/* =========================================================
   VALIDAR SESIÓN
========================================================= */
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["id_rol"] != 2) {
    header("Location: ../dashboard.php");
    exit();
}

include("../database/conexion.php");


/* =========================================================
   BUSCAR ESTUDIANTE ACTUAL
========================================================= */

$id_usuario = $_SESSION["id_usuario"];

$sql = "SELECT 
            e.id_estudiante,
            e.documento,
            e.nombres,
            e.apellidos,
            e.foto,
            c.nombre AS curso,
            j.nombre AS jornada
        FROM estudiantes e
        LEFT JOIN cursos c 
            ON e.id_curso = c.id_curso
        LEFT JOIN jornadas j 
            ON c.id_jornada = j.id_jornada
        WHERE e.id_usuario = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$resultado = $stmt->get_result();
$estudiante = $resultado->fetch_assoc();

if (!$estudiante) {
    die("No se encontró la información del estudiante.");
}

$id_estudiante = $estudiante["id_estudiante"];


/* =========================================================
   BUSCAR ALIMENTACIÓN DE HOY
========================================================= */

$sql_hoy = "SELECT 
                e.id_entrega,
                e.fecha,
                e.estado AS estado_entrega,
                e.observaciones,
                c.nombre AS curso,
                j.nombre AS jornada,
                d.recibido,
                d.observacion
            FROM detalle_entrega d
            INNER JOIN entregas_pae e 
                ON d.id_entrega = e.id_entrega
            INNER JOIN cursos c 
                ON e.id_curso = c.id_curso
            INNER JOIN jornadas j 
                ON e.id_jornada = j.id_jornada
            WHERE d.id_estudiante = ?
            AND e.fecha = CURDATE()
            ORDER BY e.id_entrega DESC
            LIMIT 1";

$stmt_hoy = $conexion->prepare($sql_hoy);
$stmt_hoy->bind_param("i", $id_estudiante);
$stmt_hoy->execute();

$resultado_hoy = $stmt_hoy->get_result();
$alimentacion_hoy = $resultado_hoy->fetch_assoc();

$fecha_hoy = date("d/m/Y");

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi alimentación | Gestión PAE</title>


    <style>

        /* =====================================================
           GENERAL
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f3ff;
            color: #333;
            min-height: 100vh;
        }


        /* =====================================================
           CONTENIDO
        ===================================================== */

        .contenido {
            margin-left: 72px;
            min-height: 100vh;
            padding: 35px;

            display: flex;
            justify-content: center;

            transition: 0.3s;
        }


        /* =====================================================
           PANEL PRINCIPAL
        ===================================================== */

        .panel-principal {
            width: 100%;
            max-width: 950px;

            background: white;

            border-radius: 28px;

            padding: 35px;

            box-shadow: 0 10px 30px rgba(84, 39, 168, 0.10);

            align-self: flex-start;
        }


        /* =====================================================
           ENCABEZADO
        ===================================================== */

        .encabezado {
            width: 100%;
            margin-bottom: 25px;
        }

        .encabezado h1 {
            color: #5427a8;
            font-size: 30px;
            margin-bottom: 8px;
        }

        .encabezado p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }


        /* =====================================================
           TARJETA ALIMENTACIÓN
        ===================================================== */

        .tarjeta-alimentacion {
            width: 100%;

            background: #ffffff;

            border-radius: 22px;

            padding: 30px;

            margin-bottom: 20px;

            border: 1px solid #eee8ff;
        }


        /* =====================================================
           TÍTULO DE TARJETA
        ===================================================== */

        .titulo-tarjeta {
            display: flex;
            align-items: center;

            gap: 15px;

            margin-bottom: 25px;
        }


        .icono-comida {
            width: 65px;
            height: 65px;

            border-radius: 18px;

            background: #eee7ff;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 32px;

            flex-shrink: 0;
        }


        .titulo-tarjeta h2 {
            color: #5427a8;
            font-size: 24px;
            margin-bottom: 4px;
        }


        .titulo-tarjeta span {
            color: #777;
            font-size: 14px;
        }


        /* =====================================================
           INFORMACIÓN CURSO / JORNADA
        ===================================================== */

        .informacion {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 15px;

            margin-bottom: 25px;
        }


        .dato {
            background: #f8f6ff;

            border-radius: 14px;

            padding: 17px;
        }


        .dato strong {
            display: block;

            color: #5427a8;

            margin-bottom: 5px;

            font-size: 14px;
        }


        .dato span {
            color: #444;
            font-size: 16px;
        }


        /* =====================================================
           ESTADOS
        ===================================================== */

        .estado {
            border-radius: 18px;

            padding: 25px;

            text-align: center;

            margin-top: 10px;
        }


        .estado .icono {
            font-size: 45px;

            margin-bottom: 10px;
        }


        .estado h3 {
            font-size: 22px;

            margin-bottom: 8px;
        }


        .estado p {
            color: #555;

            line-height: 1.5;
        }


        /* =====================================================
           RECIBIÓ
        ===================================================== */

        .estado-recibido {
            background: #e8f8ef;

            border: 1px solid #b9e8cc;
        }


        .estado-recibido h3 {
            color: #168344;
        }


        /* =====================================================
           NO RECIBIÓ
        ===================================================== */

        .estado-no-recibido {
            background: #fff0ef;

            border: 1px solid #f2c1bd;
        }


        .estado-no-recibido h3 {
            color: #c0392b;
        }


        /* =====================================================
           PENDIENTE
        ===================================================== */

        .estado-pendiente {
            background: #fff8df;

            border: 1px solid #f1df99;
        }


        .estado-pendiente h3 {
            color: #9a7500;
        }


        /* =====================================================
           OBSERVACIÓN
        ===================================================== */

        .observacion {
            margin-top: 18px;

            padding: 15px;

            background: rgba(255, 255, 255, 0.7);

            border-radius: 12px;

            text-align: left;

            line-height: 1.5;
        }


        .observacion strong {
            color: #5427a8;
        }


        /* =====================================================
           INFORMACIÓN DEL ESTUDIANTE
        ===================================================== */

        .tarjeta-estudiante {
            width: 100%;

            background: #ffffff;

            border-radius: 22px;

            padding: 25px;

            border: 1px solid #eee8ff;
        }


        .tarjeta-estudiante h3 {
            color: #5427a8;

            margin-bottom: 15px;

            font-size: 19px;
        }


        .datos-estudiante {
            display: flex;

            flex-wrap: wrap;

            gap: 20px 30px;
        }


        .dato-estudiante {
            color: #555;

            font-size: 15px;
        }


        .dato-estudiante strong {
            color: #5427a8;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 700px) {

            .contenido {
                margin-left: 0;

                padding: 20px 15px;
            }


            .panel-principal {
                padding: 20px;

                border-radius: 20px;
            }


            .encabezado h1 {
                font-size: 25px;
            }


            .encabezado p {
                font-size: 14px;
            }


            .tarjeta-alimentacion {
                padding: 20px;
            }


            .tarjeta-estudiante {
                padding: 20px;
            }


            .informacion {
                grid-template-columns: 1fr;
            }


            .titulo-tarjeta h2 {
                font-size: 20px;
            }


            .estado {
                padding: 20px;
            }


            .estado h3 {
                font-size: 19px;
            }


            .datos-estudiante {
                flex-direction: column;

                gap: 10px;
            }

        }

    </style>

</head>


<body>


    <?php include("../componentes/sidebar_estudiante.php"); ?>


    <main class="contenido">


        <div class="panel-principal">


            <!-- =================================================
                 ENCABEZADO
            ================================================== -->

            <div class="encabezado">

                <h1>🍽️ Mi alimentación</h1>

                <p>
                    Consulta el estado de tu alimentación PAE
                    correspondiente al día de hoy.
                </p>

            </div>


            <!-- =================================================
                 TARJETA DE ALIMENTACIÓN
            ================================================== -->

            <section class="tarjeta-alimentacion">


                <div class="titulo-tarjeta">

                    <div class="icono-comida">
                        🍎
                    </div>


                    <div>

                        <h2>
                            Alimentación de hoy
                        </h2>

                        <span>
                            Fecha: <?php echo $fecha_hoy; ?>
                        </span>

                    </div>

                </div>


                <?php if ($alimentacion_hoy): ?>


                    <!-- =================================================
                         INFORMACIÓN DE ENTREGA
                    ================================================== -->

                    <div class="informacion">


                        <div class="dato">

                            <strong>
                                🏫 Curso
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $alimentacion_hoy["curso"]
                                );
                                ?>
                            </span>

                        </div>


                        <div class="dato">

                            <strong>
                                🕐 Jornada
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $alimentacion_hoy["jornada"]
                                );
                                ?>
                            </span>

                        </div>


                    </div>


                    <?php if ($alimentacion_hoy["recibido"] == 1): ?>


                        <!-- =================================================
                             RECIBIÓ
                        ================================================== -->

                        <div class="estado estado-recibido">

                            <div class="icono">
                                ✅
                            </div>


                            <h3>
                                Alimentación recibida
                            </h3>


                            <p>
                                Tu entrega de alimentación PAE de hoy
                                aparece registrada correctamente.
                            </p>

                        </div>


                    <?php else: ?>


                        <!-- =================================================
                             NO RECIBIÓ
                        ================================================== -->

                        <div class="estado estado-no-recibido">

                            <div class="icono">
                                ⚠️
                            </div>


                            <h3>
                                No se registró como recibida
                            </h3>


                            <p>
                                En el sistema aparece que tu alimentación
                                de hoy no fue registrada como recibida.
                            </p>


                            <?php

                            $observacion = trim(
                                $alimentacion_hoy["observacion"] ?? ""
                            );


                            if ($observacion == "") {

                                $observacion = trim(
                                    $alimentacion_hoy["observaciones"] ?? ""
                                );

                            }


                            if ($observacion != ""):

                            ?>

                                <div class="observacion">

                                    <strong>
                                        📝 Observación:
                                    </strong>

                                    <br>

                                    <?php
                                    echo htmlspecialchars(
                                        $observacion
                                    );
                                    ?>

                                </div>

                            <?php endif; ?>


                        </div>


                    <?php endif; ?>


                <?php else: ?>


                    <!-- =================================================
                         SIN REGISTRO
                    ================================================== -->

                    <div class="estado estado-pendiente">

                        <div class="icono">
                            🕐
                        </div>


                        <h3>
                            Aún no hay un registro para hoy
                        </h3>


                        <p>
                            Todavía no aparece registrada tu
                            alimentación PAE del día de hoy.
                        </p>


                        <p style="margin-top: 8px;">
                            Cuando el personal PAE registre la entrega,
                            podrás verla aquí.
                        </p>

                    </div>


                <?php endif; ?>


            </section>


            <!-- =================================================
                 INFORMACIÓN DEL ESTUDIANTE
            ================================================== -->

            <section class="tarjeta-estudiante">


                <h3>
                    👨‍🎓 Mi información
                </h3>


                <div class="datos-estudiante">


                    <div class="dato-estudiante">

                        <strong>
                            Nombre:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $estudiante["nombres"] .
                            " " .
                            $estudiante["apellidos"]
                        );
                        ?>

                    </div>


                    <div class="dato-estudiante">

                        <strong>
                            Documento:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $estudiante["documento"]
                        );
                        ?>

                    </div>


                    <div class="dato-estudiante">

                        <strong>
                            Curso:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $estudiante["curso"] ?? "Sin curso"
                        );
                        ?>

                    </div>


                    <div class="dato-estudiante">

                        <strong>
                            Jornada:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $estudiante["jornada"] ?? "Sin jornada"
                        );
                        ?>

                    </div>


                </div>

            </section>


        </div>


    </main>


</body>

</html>