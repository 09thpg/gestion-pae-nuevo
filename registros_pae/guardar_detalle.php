<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["id_rol"] != 1) {
    header("Location: ../estudiantes/perfil.php");
    exit();
}

include("../database/conexion.php");


/* ==========================================
   RECIBIR DATOS
   ========================================== */

$fecha = $_POST["fecha"] ?? "";

$id_jornada = $_POST["id_jornada"] ?? "";

$id_curso = $_POST["id_curso"] ?? "";

$id_personal = $_POST["id_personal"] ?? "";

$observaciones = trim(
    $_POST["observaciones"] ?? ""
);

$observacion_detalle = trim(
    $_POST["observacion_detalle"] ?? ""
);

$recibidos = $_POST["recibidos"] ?? [];


/* ==========================================
   VALIDAR DATOS
   ========================================== */

if (
    $fecha == "" ||
    $id_jornada == "" ||
    $id_curso == "" ||
    $id_personal == ""
) {

    die("
        <h2>⚠️ Faltan datos de la entrega.</h2>

        <a href='index.php'>
            Volver
        </a>
    ");

}


/* ==========================================
   INICIAR TRANSACCIÓN
   ========================================== */

$conexion->begin_transaction();


try {


    /* ======================================
       CREAR ENTREGA
       ====================================== */

    $sqlEntrega = "
        INSERT INTO entregas_pae
        (
            fecha,
            id_jornada,
            id_curso,
            id_personal,
            observaciones
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";


    $stmtEntrega =
        $conexion->prepare($sqlEntrega);


    if (!$stmtEntrega) {

        throw new Exception(
            "Error al preparar la entrega: "
            . $conexion->error
        );

    }


    $stmtEntrega->bind_param(
        "siiis",
        $fecha,
        $id_jornada,
        $id_curso,
        $id_personal,
        $observaciones
    );


    if (!$stmtEntrega->execute()) {

        throw new Exception(
            "Error al guardar la entrega: "
            . $stmtEntrega->error
        );

    }


    /* ======================================
       OBTENER ID DE LA ENTREGA
       ====================================== */

    $id_entrega =
        $conexion->insert_id;


    /* ======================================
       CONSULTAR ESTUDIANTES DEL CURSO
       ====================================== */

    $sqlEstudiantes = "
        SELECT
            id_estudiante
        FROM estudiantes
        WHERE id_curso = ?
          AND estado = 'Activo'
    ";


    $stmtEstudiantes =
        $conexion->prepare(
            $sqlEstudiantes
        );


    if (!$stmtEstudiantes) {

        throw new Exception(
            "Error al consultar estudiantes: "
            . $conexion->error
        );

    }


    $stmtEstudiantes->bind_param(
        "i",
        $id_curso
    );


    $stmtEstudiantes->execute();


    $resultado =
        $stmtEstudiantes->get_result();


    /* ======================================
       PREPARAR DETALLE
       ====================================== */

    $sqlDetalle = "
        INSERT INTO detalle_entrega
        (
            id_entrega,
            id_estudiante,
            recibido,
            observacion
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ";


    $stmtDetalle =
        $conexion->prepare($sqlDetalle);


    if (!$stmtDetalle) {

        throw new Exception(
            "Error al preparar los detalles: "
            . $conexion->error
        );

    }


    /* ======================================
       GUARDAR CADA ESTUDIANTE
       ====================================== */

    while (
        $estudiante =
        $resultado->fetch_assoc()
    ) {

        $id_estudiante =
            $estudiante["id_estudiante"];


        /*
         * Comprobamos si el estudiante
         * fue marcado como recibido.
         */

        $recibido =
            in_array(
                $id_estudiante,
                $recibidos
            )
            ? 1
            : 0;


        $stmtDetalle->bind_param(
            "iiis",
            $id_entrega,
            $id_estudiante,
            $recibido,
            $observacion_detalle
        );


        if (!$stmtDetalle->execute()) {

            throw new Exception(
                "Error al guardar estudiante: "
                . $stmtDetalle->error
            );

        }

    }


    /* ======================================
       CONFIRMAR
       ====================================== */

    $conexion->commit();


    $cantidadRecibidos =
        count($recibidos);

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
        Entrega registrada
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

            display: flex;

            justify-content: center;

            align-items: center;

            background:
                linear-gradient(
                    135deg,
                    #eef3ff,
                    #fdfcff
                );

            padding: 20px;

        }


        .tarjeta {

            width: 500px;

            max-width: 100%;

            background: white;

            padding: 45px;

            border-radius: 25px;

            text-align: center;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.10);

        }


        .icono {

            font-size: 70px;

            margin-bottom: 15px;

        }


        h1 {

            color: #6d3bd1;

            margin-bottom: 15px;

        }


        p {

            color: #666;

            line-height: 1.6;

            margin-bottom: 10px;

        }


        .numero {

            font-size: 40px;

            color: #6d3bd1;

            font-weight: bold;

            margin: 15px 0;

        }


        .botones {

            display: flex;

            gap: 12px;

            margin-top: 25px;

        }


        .boton {

            flex: 1;

            padding: 14px;

            border-radius: 10px;

            text-decoration: none;

            font-weight: bold;

        }


        .principal {

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #00d4ff
                );

            color: white;

        }


        .secundario {

            background: #eeeeee;

            color: #555;

        }


        @media (max-width: 550px) {

            .botones {

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<div class="tarjeta">


    <div class="icono">
        🎉
    </div>


    <h1>
        ¡Entrega registrada!
    </h1>


    <p>
        La entrega de alimentación
        fue registrada correctamente.
    </p>


    <p>
        Estudiantes que recibieron:
    </p>


    <div class="numero">

        <?php
        echo $cantidadRecibidos;
        ?>

    </div>


    <p>
        estudiantes marcados como
        beneficiarios de esta entrega.
    </p>


    <div class="botones">


        <a
            href="index.php"
            class="boton principal"
        >
            🍽️ Nueva entrega
        </a>


        <a
            href="../dashboard.php"
            class="boton secundario"
        >
            🏠 Inicio
        </a>


    </div>


</div>


</body>

</html>


<?php


} catch (Exception $e) {


    /* ======================================
       DESHACER SI HAY ERROR
       ====================================== */

    $conexion->rollback();


    die("

        <h2>❌ No se pudo guardar la entrega.</h2>

        <p>
            "
            . htmlspecialchars(
                $e->getMessage()
            )
            . "
        </p>

        <a href='index.php'>
            Volver al registro
        </a>

    ");

}

?>