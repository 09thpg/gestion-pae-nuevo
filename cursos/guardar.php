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
    header("Location: registrar.php");
    exit();
}

$nombre = trim($_POST["nombre"] ?? "");
$id_jornada = $_POST["id_jornada"] ?? "";

if ($nombre === "" || $id_jornada === "") {
    echo "Todos los campos son obligatorios.";
    exit();
}

try {

    // Verificar que la jornada exista
    $sql = "SELECT id_jornada 
            FROM jornadas 
            WHERE id_jornada = ?";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta.");
    }

    $stmt->bind_param("i", $id_jornada);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo "La jornada seleccionada no existe.";
        exit();
    }

    $stmt->close();


    // Verificar si el grupo ya existe en esa jornada
    $sql = "SELECT id_curso 
            FROM cursos 
            WHERE nombre = ?
            AND id_jornada = ?";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta.");
    }

    $stmt->bind_param("si", $nombre, $id_jornada);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo "⚠️ Ese grupo ya existe en esa jornada.";
        exit();
    }

    $stmt->close();


    // Insertar el nuevo grupo
    $sql = "INSERT INTO cursos (nombre, id_jornada)
            VALUES (?, ?)";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar el registro.");
    }

    $stmt->bind_param("si", $nombre, $id_jornada);

    if (!$stmt->execute()) {
        throw new Exception("No se pudo guardar el grupo.");
    }

    $stmt->close();

    // Volver al listado
    header("Location: listar.php?mensaje=Grupo agregado correctamente");
    exit();

} catch (Exception $e) {

    echo "❌ Error: " . $e->getMessage();

}

?>