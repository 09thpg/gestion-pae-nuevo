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

// Verificar que se haya enviado una foto
if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
    header("Location: perfil.php");
    exit();
}

$foto = $_FILES["foto"];

// Extensiones permitidas
$extensiones_permitidas = ["jpg", "jpeg", "png", "gif", "webp"];

$extension = strtolower(
    pathinfo($foto["name"], PATHINFO_EXTENSION)
);

if (!in_array($extension, $extensiones_permitidas)) {
    echo "❌ El formato de imagen no está permitido.";
    exit();
}

// Verificar que realmente sea una imagen
$informacion_imagen = getimagesize($foto["tmp_name"]);

if ($informacion_imagen === false) {
    echo "❌ El archivo seleccionado no es una imagen válida.";
    exit();
}

// Tamaño máximo: 5 MB
$maximo = 5 * 1024 * 1024;

if ($foto["size"] > $maximo) {
    echo "❌ La imagen no puede superar los 5 MB.";
    exit();
}

try {

    // Buscar al profesor
    $sql = "SELECT id_profesor, foto
            FROM profesores
            WHERE id_usuario = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo "❌ No se encontró el profesor.";
        exit();
    }

    $profesor = $resultado->fetch_assoc();

    $stmt->close();


    // Carpeta donde se guardarán las fotos
    $carpeta = "../fotos_profesores/";

    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }


    // Crear un nombre único para la foto
    $nombre_foto = "profesor_" .
                   $profesor["id_profesor"] .
                   "_" .
                   time() .
                   "." .
                   $extension;


    $ruta_destino = $carpeta . $nombre_foto;


    // Guardar la nueva imagen
    if (!move_uploaded_file($foto["tmp_name"], $ruta_destino)) {
        echo "❌ No se pudo guardar la imagen.";
        exit();
    }


    // Si ya tenía una foto anterior, eliminarla
    if (!empty($profesor["foto"])) {

        $foto_anterior = $carpeta . $profesor["foto"];

        if (file_exists($foto_anterior)) {
            unlink($foto_anterior);
        }

    }


    // Guardar el nombre de la foto en la base de datos
    $sql = "UPDATE profesores
            SET foto = ?
            WHERE id_profesor = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "si",
        $nombre_foto,
        $profesor["id_profesor"]
    );

    $stmt->execute();

    $stmt->close();


    // Regresar al perfil
    header("Location: perfil.php");
    exit();

} catch (Exception $e) {

    echo "❌ Error al guardar la foto: " . $e->getMessage();
    exit();

}
?>