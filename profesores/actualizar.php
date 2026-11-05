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


/* ==========================================
   RECIBIR DATOS
   ========================================== */

$id_profesor = $_POST["id_profesor"] ?? "";
$documento = trim($_POST["documento"] ?? "");
$nombres = trim($_POST["nombres"] ?? "");
$apellidos = trim($_POST["apellidos"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$estado = $_POST["estado"] ?? "Activo";

if (!in_array($estado, ["Activo", "Inactivo"], true)) {
    $estado = "Activo";
}
$es_director = isset($_POST["es_director"])
    ? (int) $_POST["es_director"]
    : 0;

$id_curso_director = $_POST["id_curso_director"] ?? null;


/* ==========================================
   VALIDACIONES
   ========================================== */

if (
    $id_profesor === "" ||
    $documento === "" ||
    $nombres === "" ||
    $apellidos === ""
) {

    echo "❌ Los campos obligatorios están incompletos.";
    exit();

}


// Si no es director, quitamos el grupo
if ($es_director === 0) {

    $id_curso_director = null;

}


// Si es director, debe tener grupo
if (
    $es_director === 1 &&
    empty($id_curso_director)
) {

    echo "❌ Debes seleccionar el grupo que dirige.";
    exit();

}


try {

    /* ==========================================
       VERIFICAR PROFESOR
       ========================================== */

    $sql = "
        SELECT id_profesor
        FROM profesores
        WHERE id_profesor = ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "i",
        $id_profesor
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {

        echo "❌ El profesor no existe.";
        exit();

    }

    $stmt->close();


    /* ==========================================
       VERIFICAR DOCUMENTO DUPLICADO
       ========================================== */

    $sql = "
        SELECT id_profesor
        FROM profesores
        WHERE documento = ?
        AND id_profesor != ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "si",
        $documento,
        $id_profesor
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        echo "⚠️ Ese documento ya pertenece a otro profesor.";
        exit();

    }

    $stmt->close();


    /* ==========================================
       VERIFICAR GRUPO
       ========================================== */

    if ($es_director === 1) {

        $sql = "
            SELECT id_curso
            FROM cursos
            WHERE id_curso = ?
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "i",
            $id_curso_director
        );

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {

            echo "❌ El grupo seleccionado no existe.";
            exit();

        }

        $stmt->close();


        /* ==========================================
           VERIFICAR SI OTRO PROFESOR DIRIGE EL GRUPO
           ========================================== */

        $sql = "
            SELECT id_profesor
            FROM profesores
            WHERE es_director = 1
            AND id_curso_director = ?
            AND id_profesor != ?
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ii",
            $id_curso_director,
            $id_profesor
        );

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            echo "⚠️ Ese grupo ya tiene otro director asignado.";
            exit();

        }

        $stmt->close();

    }


    /* ==========================================
       ACTUALIZAR SIN GRUPO
       ========================================== */

    if ($id_curso_director === null) {

        $sql = "
            UPDATE profesores
            SET
                documento = ?,
                nombres = ?,
                apellidos = ?,
                correo = ?,
                telefono = ?,
                estado = ?,
                es_director = 0,
                id_curso_director = NULL
            WHERE id_profesor = ?
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
    "ssssssi",
    $documento,
    $nombres,
    $apellidos,
    $correo,
    $telefono,
    $estado,
    $id_profesor
);

    } else {

        /* ==========================================
           ACTUALIZAR CON GRUPO
           ========================================== */

        $sql = "
            UPDATE profesores
            SET
                documento = ?,
                nombres = ?,
                apellidos = ?,
                correo = ?,
                telefono = ?,
                estado = ?,
                es_director = 1,
                id_curso_director = ?
            WHERE id_profesor = ?
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
    "ssssssii",
    $documento,
    $nombres,
    $apellidos,
    $correo,
    $telefono,
    $estado,
    $id_curso_director,
    $id_profesor
);

    }


    if (!$stmt->execute()) {

        throw new Exception(
            "No se pudieron guardar los cambios."
        );

    }

    $stmt->close();

// Actualizar también el estado de la cuenta del profesor
$sql = "
    UPDATE usuarios u
    INNER JOIN profesores p ON p.id_usuario = u.id_usuario
    SET u.estado = ?
    WHERE p.id_profesor = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("si", $estado, $id_profesor);

if (!$stmt->execute()) {
    throw new Exception("No se pudo actualizar el estado de la cuenta.");
}

$stmt->close();

    /* ==========================================
       REGRESAR AL LISTADO
       ========================================== */

    header(
        "Location: listar.php?mensaje=" .
        urlencode(
            "Profesor actualizado correctamente."
        )
    );

    exit();


} catch (Exception $e) {

    echo "❌ Error: " .
        htmlspecialchars(
            $e->getMessage()
        );

}

?>