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

$id_curso = $_GET["id"] ?? "";

if ($id_curso === "" || !is_numeric($id_curso)) {
    header("Location: listar.php");
    exit();
}

try {

    // Verificar si el grupo existe
    $sql = "SELECT id_curso, nombre
            FROM cursos
            WHERE id_curso = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        header("Location: listar.php");
        exit();
    }

    $curso = $resultado->fetch_assoc();

    $stmt->close();


    // Verificar si el grupo tiene estudiantes registrados
    $sql = "SELECT id_estudiante
            FROM estudiantes
            WHERE id_curso = ?
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $stmt->close();

        header(
            "Location: listar.php?mensaje=" .
            urlencode(
                "No se puede eliminar " .
                $curso["nombre"] .
                " porque tiene estudiantes registrados."
            )
        );

        exit();
    }

    $stmt->close();


    // Verificar si el grupo tiene entregas PAE
    $sql = "SELECT id_entrega
            FROM entregas_pae
            WHERE id_curso = ?
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $stmt->close();

        header(
            "Location: listar.php?mensaje=" .
            urlencode(
                "No se puede eliminar " .
                $curso["nombre"] .
                " porque tiene registros de PAE asociados."
            )
        );

        exit();
    }

    $stmt->close();


    // Eliminar el grupo
    $sql = "DELETE FROM cursos
            WHERE id_curso = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_curso);

    if (!$stmt->execute()) {
        throw new Exception("No se pudo eliminar el grupo.");
    }

    $stmt->close();


    header(
        "Location: listar.php?mensaje=" .
        urlencode("Grupo eliminado correctamente")
    );

    exit();

} catch (Exception $e) {

    echo "❌ Error: " . $e->getMessage();

}

?>