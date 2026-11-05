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

require_once "../database/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listar.php");
    exit();
}

$id_curso = $_POST["id_curso"] ?? "";
$nombre = trim($_POST["nombre"] ?? "");
$id_jornada = $_POST["id_jornada"] ?? "";

if (
    $id_curso === "" ||
    $nombre === "" ||
    $id_jornada === ""
) {
    echo "❌ Todos los campos son obligatorios.";
    exit();
}

try {

    // Verificar que el grupo exista
    $sql = "SELECT id_curso
            FROM cursos
            WHERE id_curso = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo "❌ El grupo no existe.";
        exit();
    }

    $stmt->close();


    // Verificar que la jornada exista
    $sql = "SELECT id_jornada
            FROM jornadas
            WHERE id_jornada = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_jornada);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo "❌ La jornada seleccionada no existe.";
        exit();
    }

    $stmt->close();


    // Evitar grupos duplicados
    $sql = "SELECT id_curso
            FROM cursos
            WHERE nombre = ?
            AND id_jornada = ?
            AND id_curso != ?";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "sii",
        $nombre,
        $id_jornada,
        $id_curso
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo "⚠️ Ya existe otro grupo con ese nombre en esa jornada.";
        exit();
    }

    $stmt->close();


    // Actualizar grupo
    $sql = "UPDATE cursos
            SET nombre = ?,
                id_jornada = ?
            WHERE id_curso = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "sii",
        $nombre,
        $id_jornada,
        $id_curso
    );

    if (!$stmt->execute()) {
        throw new Exception("No se pudo actualizar el grupo.");
    }

    $stmt->close();


    header("Location: listar.php?mensaje=Grupo actualizado correctamente");
    exit();

} catch (Exception $e) {

    echo "❌ Error: " . $e->getMessage();

}

?>