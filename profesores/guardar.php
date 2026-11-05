<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["id_rol"] != 1) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";


/* =====================================================
   RECIBIR DATOS
===================================================== */

$tipo_documento = trim($_POST["tipo_documento"] ?? "");
$documento = trim($_POST["documento"] ?? "");
$nombres = trim($_POST["nombres"] ?? "");
$apellidos = trim($_POST["apellidos"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");

$estado = $_POST["estado"] ?? "Activo";

if (!in_array($estado, ["Activo", "Inactivo"], true)) {
    $estado = "Activo";
}

$usuario = trim($_POST["usuario"] ?? "");
$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";
$confirmar_password = $_POST["confirmar_password"] ?? "";

$es_director = isset($_POST["es_director"])
    ? intval($_POST["es_director"])
    : 0;

$id_curso_director = !empty($_POST["id_curso_director"])
    ? intval($_POST["id_curso_director"])
    : null;


/* =====================================================
   VALIDAR CAMPOS
===================================================== */

if (
    empty($tipo_documento) ||
    empty($documento) ||
    empty($nombres) ||
    empty($apellidos) ||
    empty($correo) ||
    empty($telefono) ||
    empty($usuario) ||
    empty($password) ||
    empty($confirmar_password)
) {

    header("Location: registrar.php?error=Todos los campos son obligatorios");
    exit();
}


/* =====================================================
   VALIDAR CONTRASEÑAS
===================================================== */

if ($password !== $confirmar_password) {

    header("Location: registrar.php?error=Las contraseñas no coinciden");
    exit();
}


/* =====================================================
   VALIDAR DIRECTOR
===================================================== */

if ($es_director == 1 && empty($id_curso_director)) {

    header("Location: registrar.php?error=Debe seleccionar el grupo que dirige");
    exit();
}

if ($es_director != 1) {

    $es_director = 0;
    $id_curso_director = null;
}


/* =====================================================
   INICIAR TRANSACCIÓN
===================================================== */

$conexion->begin_transaction();


try {


    /* =================================================
       VERIFICAR DOCUMENTO DEL PROFESOR
    ================================================= */

    $sql = "
        SELECT id_profesor
        FROM profesores
        WHERE documento = ?
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $documento);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        throw new Exception(
            "Ya existe un profesor con ese documento"
        );
    }


    /* =================================================
       VERIFICAR USUARIO
    ================================================= */

    $sql = "
        SELECT id_usuario
        FROM usuarios
        WHERE usuario = ?
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        throw new Exception(
            "El nombre de usuario ya está registrado"
        );
    }


    /* =================================================
       SI ES DIRECTOR, VERIFICAR GRUPO
    ================================================= */

    if ($es_director == 1) {


        $sql = "
            SELECT id_curso
            FROM cursos
            WHERE id_curso = ?
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_curso_director);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 0) {

            throw new Exception(
                "El grupo seleccionado no existe"
            );
        }


        /* =============================================
           VERIFICAR SI YA TIENE DIRECTOR
        ============================================= */

        $sql = "
            SELECT id_profesor
            FROM profesores
            WHERE id_curso_director = ?
              AND es_director = 1
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_curso_director);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            throw new Exception(
                "Ese grupo ya tiene un director asignado"
            );
        }

    }


    /* =================================================
       CREAR CUENTA DE USUARIO
    ================================================= */

    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $id_rol = 3;
    $estado_usuario = $estado;


    $sql = "
        INSERT INTO usuarios
        (
            usuario,
            password,
            id_rol,
            estado,
            fecha_creacion
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            NOW()
        )
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "ssis",
        $usuario,
        $password_hash,
        $id_rol,
        $estado_usuario
    );

    if (!$stmt->execute()) {

        throw new Exception(
            "No se pudo crear la cuenta del profesor"
        );
    }


    /* =================================================
       OBTENER ID DEL USUARIO
    ================================================= */

    $id_usuario = $conexion->insert_id;


    /* =================================================
       CREAR PROFESOR
    ================================================= */

    if ($id_curso_director === null) {

        $sql = "
            INSERT INTO profesores
            (
                documento,
                nombres,
                apellidos,
                correo,
                telefono,
                id_usuario,
                estado,
                es_director,
                id_curso_director
            )
            VALUES
            (
               ?,
?,
?,
?,
?,
?,
?,
0,
NULL
            )
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "sssssis",
$documento,
$nombres,
$apellidos,
$correo,
$telefono,
$id_usuario,
$estado
        );

    } else {

        $sql = "
            INSERT INTO profesores
            (
                documento,
                nombres,
                apellidos,
                correo,
                telefono,
                id_usuario,
                estado,
                es_director,
                id_curso_director
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                1,
                ?
            )
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
    "sssssisi",
    $documento,
    $nombres,
    $apellidos,
    $correo,
    $telefono,
    $id_usuario,
    $estado,
    $id_curso_director
);
    }


    if (!$stmt->execute()) {

        throw new Exception(
            "No se pudo registrar el profesor"
        );
    }


    /* =================================================
       CONFIRMAR TODO
    ================================================= */

    $conexion->commit();


    header(
        "Location: listar.php?mensaje=Profesor registrado correctamente"
    );

    exit();


} catch (Exception $e) {


    /* =================================================
       DESHACER CAMBIOS SI ALGO FALLA
    ================================================= */

    $conexion->rollback();


    $mensaje = urlencode($e->getMessage());

    header(
        "Location: registrar.php?error=" . $mensaje
    );

    exit();
}

?>