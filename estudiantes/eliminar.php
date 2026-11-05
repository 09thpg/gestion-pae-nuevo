
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

require_once __DIR__ . "/../database/conexion.php";

// Solo permitir solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listar.php");
    exit();
}

$id_estudiante = filter_input(
    INPUT_POST,
    "id_estudiante",
    FILTER_VALIDATE_INT
);

if (!$id_estudiante || $id_estudiante < 1) {
    header("Location: listar.php?error=id");
    exit();
}

$conexion->begin_transaction();

try {
    // Buscar el usuario asociado al estudiante
    $sql = "SELECT id_usuario
            FROM estudiantes
            WHERE id_estudiante = ?
            FOR UPDATE";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_estudiante);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $estudiante = $resultado->fetch_assoc();
    $stmt->close();

    if (!$estudiante) {
        throw new Exception("El estudiante no existe.");
    }

    $id_usuario = (int) $estudiante["id_usuario"];

    // Eliminar sus detalles de entregas, sin borrar
    // las entregas generales ni las de otros estudiantes.
    $sql = "DELETE FROM detalle_entrega
            WHERE id_estudiante = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_estudiante);
    $stmt->execute();
    $stmt->close();

    // Eliminar el estudiante
    $sql = "DELETE FROM estudiantes
            WHERE id_estudiante = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_estudiante);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new Exception("No se pudo eliminar el estudiante.");
    }

    $stmt->close();

    // Eliminar su usuario de acceso, solo si corresponde
    // a una cuenta de estudiante (rol 2).
    $sql = "DELETE FROM usuarios
            WHERE id_usuario = ?
              AND id_rol = 2";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->close();

    $conexion->commit();

    header("Location: listar.php?eliminado=1");
    exit();

} catch (Throwable $e) {
    $conexion->rollback();

    error_log("Error al eliminar estudiante: " . $e->getMessage());

    header("Location: listar.php?error=eliminar");
    exit();
}
?>
