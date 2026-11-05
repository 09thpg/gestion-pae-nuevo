
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION["id_rol"]) || $_SESSION["id_rol"] != 1) {
    header("Location: ../dashboard.php");
    exit();
}

require_once __DIR__ . "/../database/conexion.php";

if (
    !isset($_GET["id"]) ||
    filter_var($_GET["id"], FILTER_VALIDATE_INT) === false ||
    (int) $_GET["id"] <= 0
) {
    header("Location: listar.php?error=id");
    exit();
}

$id_profesor = (int) $_GET["id"];

try {
    $conexion->begin_transaction();

    // Buscar al profesor y su usuario asociado.
    $sql = "SELECT id_usuario
            FROM profesores
            WHERE id_profesor = ?
            FOR UPDATE";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_profesor);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $profesor = $resultado->fetch_assoc();
    $stmt->close();

    if (!$profesor) {
        throw new Exception("El profesor no existe.");
    }

    // Desvincular las entregas, conservando su historial.
    $sql = "UPDATE entregas_pae
            SET id_profesor = NULL
            WHERE id_profesor = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_profesor);
    $stmt->execute();
    $stmt->close();

    // Eliminar al profesor.
    $sql = "DELETE FROM profesores
            WHERE id_profesor = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_profesor);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new Exception("No se pudo eliminar al profesor.");
    }

    $stmt->close();

    // Eliminar su usuario asociado, si existe.
    if (
        isset($profesor["id_usuario"]) &&
        $profesor["id_usuario"] !== null
    ) {
        $id_usuario = (int) $profesor["id_usuario"];

        $sql = "DELETE FROM usuarios
                WHERE id_usuario = ?
                AND id_rol = 3";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();
    }

    $conexion->commit();

    header("Location: listar.php?eliminado=1");
    exit();

} catch (Throwable $e) {
    $conexion->rollback();

    error_log(
        "Error al eliminar profesor: " . $e->getMessage()
    );

    echo "<h2>No se pudo eliminar al profesor</h2>";
    echo "<p>Error: " .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8") .
        "</p>";
    echo "<a href='listar.php'>Volver al listado</a>";
    exit();
}
?>
