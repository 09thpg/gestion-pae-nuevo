<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");


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
$stmtPersonal->bind_param("i", $id_usuario);
$stmtPersonal->execute();

$resultadoPersonal = $stmtPersonal->get_result();

$personal = $resultadoPersonal->fetch_assoc();

if (!$personal) {
    die("No se encontró el Personal PAE.");
}

$id_personal = $personal["id_personal"];


/* ==========================================
   RECIBIR ID DEL DETALLE
   ========================================== */

$id_detalle = isset($_GET["id_detalle"])
    ? intval($_GET["id_detalle"])
    : 0;


if ($id_detalle <= 0) {

    header("Location: mis_registros.php");
    exit();

}


/* ==========================================
   VERIFICAR QUE EL REGISTRO SEA DEL PERSONAL
   ========================================== */

$sqlBuscar = "
    SELECT
        d.id_detalle,
        e.id_entrega
    FROM detalle_entrega d
    INNER JOIN entregas_pae e
        ON d.id_entrega = e.id_entrega
    WHERE d.id_detalle = ?
    AND e.id_personal = ?
    LIMIT 1
";

$stmtBuscar = $conexion->prepare($sqlBuscar);

$stmtBuscar->bind_param(
    "ii",
    $id_detalle,
    $id_personal
);

$stmtBuscar->execute();

$resultadoBuscar = $stmtBuscar->get_result();

$registro = $resultadoBuscar->fetch_assoc();


if (!$registro) {

    die("El registro no existe o no pertenece a tu cuenta.");

}


$id_entrega = $registro["id_entrega"];


/* ==========================================
   ELIMINAR
   ========================================== */

try {

    $conexion->begin_transaction();


    /* ELIMINAR DETALLE */

    $sqlDetalle = "
        DELETE FROM detalle_entrega
        WHERE id_detalle = ?
    ";

    $stmtDetalle = $conexion->prepare($sqlDetalle);

    $stmtDetalle->bind_param(
        "i",
        $id_detalle
    );

    $stmtDetalle->execute();


    /* ELIMINAR ENTREGA */

    $sqlEntrega = "
        DELETE FROM entregas_pae
        WHERE id_entrega = ?
        AND id_personal = ?
    ";

    $stmtEntrega = $conexion->prepare($sqlEntrega);

    $stmtEntrega->bind_param(
        "ii",
        $id_entrega,
        $id_personal
    );

    $stmtEntrega->execute();


    $conexion->commit();


    header("Location: mis_registros.php");
    exit();


} catch (Exception $e) {

    $conexion->rollback();

    die(
        "No se pudo eliminar el registro."
    );

}

?>