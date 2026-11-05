
<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ((int)($_SESSION["id_rol"] ?? 0) !== 1) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";

function volverConError($mensaje) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error al actualizar</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f4f1fc;
                padding: 25px;
                color: #333;
            }
            .caja {
                max-width: 600px;
                margin: 50px auto;
                background: white;
                padding: 30px;
                border-radius: 18px;
                box-shadow: 0 5px 20px #0001;
            }
            a {
                color: #7650d6;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class='caja'>
            <h2>⚠️ No se pudieron guardar los cambios</h2>
            <p>" . htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") . "</p>
            <a href='javascript:history.back()'>← Volver al formulario</a>
        </div>
    </body>
    </html>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listar.php");
    exit();
}

/* =========================================
   RECIBIR DATOS
========================================= */

$id_estudiante = filter_input(
    INPUT_POST,
    "id_estudiante",
    FILTER_VALIDATE_INT
);

$tipo_documento = trim($_POST["tipo_documento"] ?? "");
$documento = trim($_POST["documento"] ?? "");
$nombres = trim($_POST["nombres"] ?? "");
$apellidos = trim($_POST["apellidos"] ?? "");
$fecha_nacimiento = trim($_POST["fecha_nacimiento"] ?? "");
$sexo = trim($_POST["sexo"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$id_curso = filter_input(INPUT_POST, "id_curso", FILTER_VALIDATE_INT);
$beneficiario = $_POST["beneficiario"] ?? "";
$estado = trim($_POST["estado"] ?? "");

/* =========================================
   VALIDAR DATOS
========================================= */

if (
    !$id_estudiante ||
    $id_estudiante < 1 ||
    $documento === "" ||
    $nombres === "" ||
    $apellidos === "" ||
    !in_array($tipo_documento, ["TI", "CC", "CE"], true) ||
    !$id_curso ||
    $id_curso < 1 ||
    !in_array((string)$beneficiario, ["0", "1"], true) ||
    !in_array($estado, ["Activo", "Inactivo"], true)
) {
    volverConError("Verifica los campos obligatorios y el estado del estudiante.");
}

if ($correo !== "" && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    volverConError("El correo electrónico no tiene un formato válido.");
}

if ($fecha_nacimiento !== "") {
    $fecha = DateTime::createFromFormat("Y-m-d", $fecha_nacimiento);

    if (!$fecha || $fecha->format("Y-m-d") !== $fecha_nacimiento) {
        volverConError("La fecha de nacimiento no es válida.");
    }
} else {
    $fecha_nacimiento = null;
}

if (!in_array($sexo, ["", "F", "M"], true)) {
    volverConError("Selecciona un valor válido para el sexo.");
}

$beneficiario = (int)$beneficiario;
$id_curso = (int)$id_curso;

/* =========================================
   COMPROBAR ESTUDIANTE
========================================= */

$sql_actual = "
    SELECT id_usuario, foto
    FROM estudiantes
    WHERE id_estudiante = ?
    LIMIT 1
";

$stmt_actual = $conexion->prepare($sql_actual);

if (!$stmt_actual) {
    volverConError("No se pudo consultar el estudiante.");
}

$stmt_actual->bind_param("i", $id_estudiante);
$stmt_actual->execute();

$resultado_actual = $stmt_actual->get_result();

if ($resultado_actual->num_rows !== 1) {
    volverConError("El estudiante no existe.");
}

$actual = $resultado_actual->fetch_assoc();

$foto_actual = $actual["foto"] ?? "";
$id_usuario_estudiante = (int)($actual["id_usuario"] ?? 0);

/* =========================================
   COMPROBAR DOCUMENTO DUPLICADO
========================================= */

$sql_documento = "
    SELECT id_estudiante
    FROM estudiantes
    WHERE documento = ?
      AND id_estudiante <> ?
    LIMIT 1
";

$stmt_documento = $conexion->prepare($sql_documento);

if (!$stmt_documento) {
    volverConError("No se pudo comprobar el documento.");
}

$stmt_documento->bind_param("si", $documento, $id_estudiante);
$stmt_documento->execute();

if ($stmt_documento->get_result()->num_rows > 0) {
    volverConError("Ya existe otro estudiante con ese número de documento.");
}

/* =========================================
   COMPROBAR CURSO
========================================= */

$sql_curso = "
    SELECT id_curso
    FROM cursos
    WHERE id_curso = ?
    LIMIT 1
";

$stmt_curso = $conexion->prepare($sql_curso);

if (!$stmt_curso) {
    volverConError("No se pudo comprobar el curso.");
}

$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();

if ($stmt_curso->get_result()->num_rows !== 1) {
    volverConError("El curso seleccionado no existe.");
}

/* =========================================
   PROCESAR FOTO
========================================= */

$nueva_foto = $foto_actual;
$ruta_nueva_foto = null;

if (
    isset($_FILES["foto"]) &&
    $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE
) {
    if ($_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        volverConError("Ocurrió un error al subir la fotografía.");
    }

    if ($_FILES["foto"]["size"] > 5 * 1024 * 1024) {
        volverConError("La fotografía no puede superar los 5 MB.");
    }

    $extension = strtolower(
        pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION)
    );

    $extensiones_permitidas = ["jpg", "jpeg", "png", "webp"];

    if (!in_array($extension, $extensiones_permitidas, true)) {
        volverConError("La foto debe ser JPG, JPEG, PNG o WEBP.");
    }

    $informacion_imagen = @getimagesize($_FILES["foto"]["tmp_name"]);

    if ($informacion_imagen === false) {
        volverConError("El archivo seleccionado no es una imagen válida.");
    }

    $carpeta_uploads = realpath("../uploads");

    if ($carpeta_uploads === false || !is_dir($carpeta_uploads)) {
        volverConError("No se encontró la carpeta uploads.");
    }

    $nombre_foto = $documento . "_" . bin2hex(random_bytes(8)) . "." . $extension;

    $destino = $carpeta_uploads . DIRECTORY_SEPARATOR . $nombre_foto;

    if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $destino)) {
        volverConError("No se pudo guardar la nueva fotografía.");
    }

    $ruta_nueva_foto = $destino;
    $nueva_foto = $nombre_foto;
}

/* =========================================
   ACTUALIZAR INFORMACIÓN Y ESTADO
========================================= */

$conexion->begin_transaction();

try {
    $sql_actualizar = "
        UPDATE estudiantes
        SET
            documento = ?,
            tipo_documento = ?,
            nombres = ?,
            apellidos = ?,
            fecha_nacimiento = ?,
            sexo = ?,
            correo = ?,
            foto = ?,
            beneficiario = ?,
            id_curso = ?,
            estado = ?
        WHERE id_estudiante = ?
    ";

    $stmt_actualizar = $conexion->prepare($sql_actualizar);

    if (!$stmt_actualizar) {
        throw new Exception("No se pudo preparar la actualización del estudiante.");
    }

    $stmt_actualizar->bind_param(
    "ssssssssiisi",
        $documento,
        $tipo_documento,
        $nombres,
        $apellidos,
        $fecha_nacimiento,
        $sexo,
        $correo,
        $nueva_foto,
        $beneficiario,
        $id_curso,
        $estado,
        $id_estudiante
    );

    if (!$stmt_actualizar->execute()) {
        throw new Exception("No se pudieron actualizar los datos del estudiante.");
    }

    /* Actualizar la cuenta vinculada, si existe. */
    if ($id_usuario_estudiante > 0) {
        $sql_usuario = "
            UPDATE usuarios
            SET estado = ?
            WHERE id_usuario = ?
              AND id_rol = 2
        ";

        $stmt_usuario = $conexion->prepare($sql_usuario);

        if (!$stmt_usuario) {
            throw new Exception("No se pudo preparar la actualización de la cuenta.");
        }

        $stmt_usuario->bind_param(
            "si",
            $estado,
            $id_usuario_estudiante
        );

        if (!$stmt_usuario->execute()) {
            throw new Exception("No se pudo actualizar el estado de la cuenta.");
        }
    }

    $conexion->commit();

    /* Eliminar la foto anterior solo si todo se guardó correctamente. */
    if (
        $ruta_nueva_foto !== null &&
        $foto_actual !== "" &&
        basename($foto_actual) !== basename($nueva_foto)
    ) {
        $carpeta_real = realpath("../uploads");
        $ruta_anterior = realpath("../uploads/" . basename($foto_actual));

        if (
            $carpeta_real !== false &&
            $ruta_anterior !== false &&
            is_file($ruta_anterior) &&
            dirname($ruta_anterior) === $carpeta_real
        ) {
            @unlink($ruta_anterior);
        }
    }

    header("Location: listar.php?actualizado=1");
    exit();

} catch (Throwable $e) {
    $conexion->rollback();

    if (
        $ruta_nueva_foto !== null &&
        is_file($ruta_nueva_foto)
    ) {
        @unlink($ruta_nueva_foto);
    }

    volverConError(
        "Ocurrió un problema al guardar los cambios. Revisa los datos e inténtalo de nuevo."
    );
}
