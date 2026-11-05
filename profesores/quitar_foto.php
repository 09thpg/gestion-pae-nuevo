<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

// Solo profesores
if ($_SESSION["id_rol"] != 3) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";

$id_usuario = $_SESSION["id_usuario"];

try {

    // Buscar la foto actual del profesor
    $sql = "SELECT id_profesor, foto
            FROM profesores
            WHERE id_usuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        header("Location: perfil.php");
        exit();
    }

    $profesor = $resultado->fetch_assoc();

    $stmt->close();


    // Si tiene una foto guardada, eliminarla de la carpeta
    if (!empty($profesor["foto"])) {

        $ruta_foto = "../fotos_profesores/" . $profesor["foto"];

        if (file_exists($ruta_foto)) {
            unlink($ruta_foto);
        }
    }


    // Quitar la foto de la base de datos
    $sql = "UPDATE profesores
            SET foto = NULL
            WHERE id_profesor = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $profesor["id_profesor"]);
    $stmt->execute();

    $stmt->close();


    // Regresar al perfil
    header("Location: perfil.php");
    exit();

} catch (Exception $e) {

    echo "❌ Error al quitar la foto: " . $e->getMessage();
    exit();

}

?>