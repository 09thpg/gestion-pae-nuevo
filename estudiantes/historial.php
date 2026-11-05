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
   BUSCAR ESTUDIANTE
========================================================= */

$id_usuario = $_SESSION["id_usuario"];

$sql_estudiante = "SELECT
                        e.id_estudiante,
                        e.documento,
                        e.nombres,
                        e.apellidos,
                        c.nombre AS curso,
                        j.nombre AS jornada
                   FROM estudiantes e
                   LEFT JOIN cursos c
                       ON e.id_curso = c.id_curso
                   LEFT JOIN jornadas j
                       ON c.id_jornada = j.id_jornada
                   WHERE e.id_usuario = ?
                   LIMIT 1";

$stmt_estudiante = $conexion->prepare($sql_estudiante);
$stmt_estudiante->bind_param("i", $id_usuario);
$stmt_estudiante->execute();

$resultado_estudiante = $stmt_estudiante->get_result();
$estudiante = $resultado_estudiante->fetch_assoc();


if (!$estudiante) {
    die("No se encontró la información del estudiante.");
}


$id_estudiante = $estudiante["id_estudiante"];


/* =========================================================
   BUSCAR HISTORIAL PAE
========================================================= */

$sql_historial = "SELECT
                      e.id_entrega,
                      e.fecha,
                      e.estado AS estado_entrega,
                      e.observaciones AS observaciones_entrega,
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
                  ORDER BY e.fecha DESC, e.id_entrega DESC";

$stmt_historial = $conexion->prepare($sql_historial);
$stmt_historial->bind_param("i", $id_estudiante);
$stmt_historial->execute();

$resultado_historial = $stmt_historial->get_result();


/* =========================================================
   CONTADORES
========================================================= */

$total_registros = 0;
$total_recibidos = 0;
$total_no_recibidos = 0;

$historial = [];


while ($registro = $resultado_historial->fetch_assoc()) {

    $historial[] = $registro;

    $total_registros++;

    if ($registro["recibido"] == 1) {

        $total_recibidos++;

    } else {

        $total_no_recibidos++;

    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi historial | Gestión PAE</title>


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

            max-width: 1050px;

            background: white;

            border-radius: 28px;

            padding: 35px;

            box-shadow:
                0 10px 30px rgba(84, 39, 168, 0.10);

            align-self: flex-start;
        }


        /* =====================================================
           ENCABEZADO
        ===================================================== */

        .encabezado {

            width: 100%;

            margin-bottom: 28px;
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
           RESUMEN
        ===================================================== */

        .resumen {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;

            margin-bottom: 25px;
        }


        .tarjeta-resumen {

            background: #faf9ff;

            border-radius: 18px;

            padding: 20px;

            border: 1px solid #eee8ff;

            display: flex;

            align-items: center;

            gap: 15px;
        }


        .icono-resumen {

            width: 52px;

            height: 52px;

            border-radius: 15px;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 25px;

            flex-shrink: 0;
        }


        .morado {

            background: #eee7ff;
        }


        .verde {

            background: #e4f8ec;
        }


        .rojo {

            background: #ffebea;
        }


        .tarjeta-resumen strong {

            display: block;

            color: #777;

            font-size: 13px;

            margin-bottom: 4px;
        }


        .tarjeta-resumen span {

            font-size: 24px;

            font-weight: bold;

            color: #5427a8;
        }


        /* =====================================================
           INFORMACIÓN DEL ESTUDIANTE
        ===================================================== */

        .info-estudiante {

            background: #ffffff;

            border-radius: 20px;

            padding: 23px;

            margin-bottom: 28px;

            border: 1px solid #eee8ff;
        }


        .info-estudiante h2 {

            color: #5427a8;

            font-size: 19px;

            margin-bottom: 16px;
        }


        .datos {

            display: flex;

            flex-wrap: wrap;

            gap: 15px 30px;
        }


        .dato {

            color: #555;

            font-size: 15px;
        }


        .dato strong {

            color: #5427a8;
        }


        /* =====================================================
           TÍTULO DEL HISTORIAL
        ===================================================== */

        .titulo-historial {

            color: #5427a8;

            font-size: 22px;

            margin-bottom: 18px;
        }


        /* =====================================================
           LISTA DE REGISTROS
        ===================================================== */

        .lista-historial {

            display: flex;

            flex-direction: column;

            gap: 18px;
        }


        /* =====================================================
           REGISTRO
        ===================================================== */

        .registro {

            background: #ffffff;

            border-radius: 20px;

            padding: 24px;

            border: 1px solid #eee8ff;

            border-left:
                6px solid #5427a8;
        }


        .registro-cabecera {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 15px;

            margin-bottom: 20px;
        }


        .fecha {

            color: #5427a8;

            font-weight: bold;

            font-size: 18px;
        }


        .fecha small {

            display: block;

            color: #777;

            font-size: 13px;

            font-weight: normal;

            margin-top: 4px;
        }


        /* =====================================================
           ESTADOS
        ===================================================== */

        .estado {

            padding: 8px 14px;

            border-radius: 30px;

            font-size: 13px;

            font-weight: bold;

            white-space: nowrap;
        }


        .estado-recibido {

            background: #e4f8ec;

            color: #168344;
        }


        .estado-no-recibido {

            background: #ffebea;

            color: #c0392b;
        }


        /* =====================================================
           DATOS DEL REGISTRO
        ===================================================== */

        .datos-registro {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 14px;
        }


        .dato-registro {

            background: #f8f6ff;

            border-radius: 12px;

            padding: 14px;
        }


        .dato-registro strong {

            display: block;

            color: #5427a8;

            font-size: 13px;

            margin-bottom: 5px;
        }


        .dato-registro span {

            color: #444;

            line-height: 1.4;
        }


        /* =====================================================
           OBSERVACIÓN
        ===================================================== */

        .observacion {

            margin-top: 15px;

            padding: 15px;

            border-radius: 12px;

            background: #faf9ff;

            border: 1px solid #e7e0ff;

            line-height: 1.5;
        }


        .observacion strong {

            color: #5427a8;
        }


        /* =====================================================
           SIN REGISTROS
        ===================================================== */

        .sin-registros {

            background: #faf9ff;

            border: 1px solid #eee8ff;

            border-radius: 20px;

            padding: 50px 25px;

            text-align: center;
        }


        .sin-registros .icono {

            font-size: 55px;

            margin-bottom: 15px;
        }


        .sin-registros h2 {

            color: #5427a8;

            margin-bottom: 8px;
        }


        .sin-registros p {

            color: #777;

            line-height: 1.5;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 850px) {

            .resumen {

                grid-template-columns: 1fr;

            }


            .datos-registro {

                grid-template-columns: 1fr;

            }

        }


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


            .tarjeta-resumen {

                padding: 18px;
            }


            .registro {

                padding: 20px;
            }


            .registro-cabecera {

                align-items: flex-start;

                flex-direction: column;
            }


            .datos {

                flex-direction: column;

                gap: 10px;
            }


            .estado {

                white-space: normal;

                text-align: center;
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

                <h1>
                    📅 Mi historial PAE
                </h1>

                <p>
                    Aquí puedes consultar tus registros
                    de alimentación anteriores.
                </p>

            </div>


            <!-- =================================================
                 RESUMEN
            ================================================== -->

            <section class="resumen">


                <div class="tarjeta-resumen">

                    <div class="icono-resumen morado">
                        📋
                    </div>


                    <div>

                        <strong>
                            Total de registros
                        </strong>

                        <span>
                            <?php echo $total_registros; ?>
                        </span>

                    </div>

                </div>


                <div class="tarjeta-resumen">

                    <div class="icono-resumen verde">
                        ✅
                    </div>


                    <div>

                        <strong>
                            Alimentaciones recibidas
                        </strong>

                        <span>
                            <?php echo $total_recibidos; ?>
                        </span>

                    </div>

                </div>


                <div class="tarjeta-resumen">

                    <div class="icono-resumen rojo">
                        ⚠️
                    </div>


                    <div>

                        <strong>
                            No recibidas
                        </strong>

                        <span>
                            <?php echo $total_no_recibidos; ?>
                        </span>

                    </div>

                </div>


            </section>


            <!-- =================================================
                 INFORMACIÓN DEL ESTUDIANTE
            ================================================== -->

            <section class="info-estudiante">


                <h2>
                    👨‍🎓 Información del estudiante
                </h2>


                <div class="datos">


                    <div class="dato">

                        <strong>
                            Nombre:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $estudiante["nombres"]
                            . " "
                            . $estudiante["apellidos"]
                        );

                        ?>

                    </div>


                    <div class="dato">

                        <strong>
                            Documento:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $estudiante["documento"]
                        );

                        ?>

                    </div>


                    <div class="dato">

                        <strong>
                            Curso:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $estudiante["curso"]
                            ?? "Sin curso"
                        );

                        ?>

                    </div>


                    <div class="dato">

                        <strong>
                            Jornada:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $estudiante["jornada"]
                            ?? "Sin jornada"
                        );

                        ?>

                    </div>


                </div>


            </section>


            <!-- =================================================
                 HISTORIAL
            ================================================== -->

            <h2 class="titulo-historial">

                🍽️ Registros de alimentación

            </h2>


            <?php if (count($historial) > 0): ?>


                <section class="lista-historial">


                    <?php foreach ($historial as $registro): ?>


                        <article class="registro">


                            <div class="registro-cabecera">


                                <div class="fecha">

                                    📅

                                    <?php

                                    echo date(
                                        "d/m/Y",
                                        strtotime(
                                            $registro["fecha"]
                                        )
                                    );

                                    ?>


                                    <small>

                                        Registro de alimentación PAE

                                    </small>

                                </div>


                                <?php if ($registro["recibido"] == 1): ?>


                                    <div class="estado estado-recibido">

                                        ✅ Alimentación recibida

                                    </div>


                                <?php else: ?>


                                    <div class="estado estado-no-recibido">

                                        ⚠️ No recibida

                                    </div>


                                <?php endif; ?>


                            </div>


                            <!-- =====================================
                                 DATOS DEL REGISTRO
                            ====================================== -->

                            <div class="datos-registro">


                                <div class="dato-registro">

                                    <strong>
                                        🏫 Curso
                                    </strong>

                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $registro["curso"]
                                        );

                                        ?>

                                    </span>

                                </div>


                                <div class="dato-registro">

                                    <strong>
                                        🕐 Jornada
                                    </strong>

                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $registro["jornada"]
                                        );

                                        ?>

                                    </span>

                                </div>


                                <div class="dato-registro">

                                    <strong>
                                        📌 Estado de la entrega
                                    </strong>

                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $registro["estado_entrega"]
                                        );

                                        ?>

                                    </span>

                                </div>


                                <div class="dato-registro">

                                    <strong>
                                        🍎 Alimentación
                                    </strong>

                                    <span>


                                        <?php

                                        if (
                                            $registro["recibido"] == 1
                                        ) {

                                            echo "Recibida correctamente";

                                        } else {

                                            echo "No registrada como recibida";

                                        }

                                        ?>


                                    </span>

                                </div>


                            </div>


                            <!-- =====================================
                                 OBSERVACIÓN
                            ====================================== -->

                            <?php

                            $observacion = trim(
                                $registro["observacion"]
                                ?? ""
                            );


                            if ($observacion == "") {

                                $observacion = trim(
                                    $registro[
                                        "observaciones_entrega"
                                    ] ?? ""
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


                        </article>


                    <?php endforeach; ?>


                </section>


            <?php else: ?>


                <!-- =================================================
                     SIN REGISTROS
                ================================================== -->

                <section class="sin-registros">


                    <div class="icono">
                        📭
                    </div>


                    <h2>
                        Aún no tienes registros
                    </h2>


                    <p>
                        Cuando se registre una entrega de alimentación
                        PAE asociada a tu documento, aparecerá aquí.
                    </p>


                </section>


            <?php endif; ?>


        </div>


    </main>


</body>

</html>