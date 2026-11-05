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
   RECIBIR ID DE LA ENTREGA
   ========================================== */

$id_entrega = isset($_POST["id_entrega"])
    ? intval($_POST["id_entrega"])
    : 0;


if ($id_entrega <= 0) {

    header("Location: seleccionar_registro.php");
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


$id_personal =
    $personal["id_personal"];


/* ==========================================
   OBTENER DATOS DEL GRUPO
   ========================================== */

$sqlEntrega = "
    SELECT
        id_entrega,
        fecha,
        id_curso,
        id_jornada,
        id_personal,
        id_profesor,
        estado
    FROM entregas_pae
    WHERE id_entrega = ?
    AND id_personal = ?
    LIMIT 1
";

$stmtEntrega =
    $conexion->prepare($sqlEntrega);

$stmtEntrega->bind_param(
    "ii",
    $id_entrega,
    $id_personal
);

$stmtEntrega->execute();

$resultadoEntrega =
    $stmtEntrega->get_result();

$entrega =
    $resultadoEntrega->fetch_assoc();


if (!$entrega) {

    die("No se encontró el grupo.");

}


/* ==========================================
   VERIFICAR SI YA ESTÁ TERMINADO
   ========================================== */

if ($entrega["estado"] === "Terminado") {

    header(
        "Location: registro_refrigerio.php?id_curso="
        . $entrega["id_curso"]
        . "&id_profesor="
        . $entrega["id_profesor"]
    );

    exit();

}


/* ==========================================
   INICIAR TRANSACCIÓN
   ========================================== */

try {

    $conexion->begin_transaction();


    /* ======================================
       OBTENER TODOS LOS ESTUDIANTES DEL CURSO
       ====================================== */

    $sqlEstudiantes = "
        SELECT
            id_estudiante
        FROM estudiantes
        WHERE id_curso = ?
        AND estado = 'Activo'
        ORDER BY apellidos, nombres
    ";

    $stmtEstudiantes =
        $conexion->prepare($sqlEstudiantes);

    $stmtEstudiantes->bind_param(
        "i",
        $entrega["id_curso"]
    );

    $stmtEstudiantes->execute();

    $resultadoEstudiantes =
        $stmtEstudiantes->get_result();


    /* ======================================
       PREPARAR CONSULTA PARA SABER
       SI YA ESTÁ REGISTRADO
       ====================================== */

    $sqlExiste = "
        SELECT id_detalle
        FROM detalle_entrega
        WHERE id_entrega = ?
        AND id_estudiante = ?
        LIMIT 1
    ";

    $stmtExiste =
        $conexion->prepare($sqlExiste);


    /* ======================================
       PREPARAR INSERT
       ====================================== */

    $sqlInsertar = "
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

    $stmtInsertar =
        $conexion->prepare($sqlInsertar);


    /* ======================================
       RECORRER TODOS LOS ESTUDIANTES
       ====================================== */

    while (
        $estudiante =
        $resultadoEstudiantes->fetch_assoc()
    ) {

        $id_estudiante =
            $estudiante["id_estudiante"];


        /* ==============================
           COMPROBAR SI YA FUE REGISTRADO
           ============================== */

        $stmtExiste->bind_param(
            "ii",
            $id_entrega,
            $id_estudiante
        );

        $stmtExiste->execute();

        $resultadoExiste =
            $stmtExiste->get_result();


        /* ==============================
           SI NO EXISTE, MARCAR NO RECIBIÓ
           ============================== */

        if ($resultadoExiste->num_rows === 0) {

            $recibido = 0;

            $observacion =
                "No registró su documento.";


            $stmtInsertar->bind_param(
                "iiis",
                $id_entrega,
                $id_estudiante,
                $recibido,
                $observacion
            );

            $stmtInsertar->execute();

        }

    }


    /* ==========================================
       CAMBIAR ESTADO DEL GRUPO
       ========================================== */

    $nuevoEstado =
        "Terminado";

    $observacionFinal =
        "Grupo terminado. Lista completada automáticamente.";


    $sqlActualizar = "
        UPDATE entregas_pae

        SET
            estado = ?,
            observaciones = ?

        WHERE id_entrega = ?
        AND id_personal = ?
    ";

    $stmtActualizar =
        $conexion->prepare($sqlActualizar);

    $stmtActualizar->bind_param(
        "ssii",
        $nuevoEstado,
        $observacionFinal,
        $id_entrega,
        $id_personal
    );

    $stmtActualizar->execute();


    /* ==========================================
       CONFIRMAR CAMBIOS
       ========================================== */

    $conexion->commit();


    /* ==========================================
       VOLVER AL REGISTRO
       ========================================== */

    header(
        "Location: registro_refrigerio.php?id_curso="
        . $entrega["id_curso"]
        . "&id_profesor="
        . $entrega["id_profesor"]
    );

    exit();


} catch (Exception $e) {

    try {

        $conexion->rollback();

    } catch (Exception $error) {

        // Evitar errores adicionales.

    }


    die(
        "Ocurrió un error al terminar el grupo: "
        . htmlspecialchars($e->getMessage())
    );

}

?>