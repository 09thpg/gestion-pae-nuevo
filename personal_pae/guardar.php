<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");


/* ==========================================
   RECIBIR DATOS
   ========================================== */

$documento = trim($_POST["documento"] ?? "");
$nombres = trim($_POST["nombres"] ?? "");
$apellidos = trim($_POST["apellidos"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$correo = trim($_POST["correo"] ?? "");

$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";
$confirmar_password = $_POST["confirmar_password"] ?? "";


/* ==========================================
   VALIDAR DATOS
   ========================================== */

if (
    $documento == "" ||
    $nombres == "" ||
    $apellidos == "" ||
    $usuario == "" ||
    $password == "" ||
    $confirmar_password == ""
) {

    die("
        <h2>⚠️ Faltan datos obligatorios.</h2>

        <a href='registrar.php'>
            Volver al formulario
        </a>
    ");

}


/* ==========================================
   VALIDAR CONTRASEÑA
   ========================================== */

if ($password !== $confirmar_password) {

    die("
        <h2>⚠️ Las contraseñas no coinciden.</h2>

        <a href='registrar.php'>
            Volver al formulario
        </a>
    ");

}


if (strlen($password) < 6) {

    die("
        <h2>⚠️ La contraseña es demasiado corta.</h2>

        <p>
            La contraseña debe tener mínimo 6 caracteres.
        </p>

        <a href='registrar.php'>
            Volver al formulario
        </a>
    ");

}


/* ==========================================
   COMPROBAR DOCUMENTO
   ========================================== */

$sqlBuscar = "
    SELECT id_personal
    FROM personal_pae
    WHERE documento = ?
    LIMIT 1
";

$stmtBuscar = $conexion->prepare($sqlBuscar);

if (!$stmtBuscar) {

    die(
        "Error al preparar la consulta: "
        . $conexion->error
    );

}

$stmtBuscar->bind_param(
    "s",
    $documento
);

$stmtBuscar->execute();

$resultado = $stmtBuscar->get_result();


if ($resultado->num_rows > 0) {

    die("
        <h2>⚠️ El personal PAE ya existe.</h2>

        <p>
            Ya existe una persona registrada
            con ese documento.
        </p>

        <a href='registrar.php'>
            Volver
        </a>
    ");

}


/* ==========================================
   COMPROBAR USUARIO
   ========================================== */

$sqlUsuario = "
    SELECT id_usuario
    FROM usuarios
    WHERE usuario = ?
    LIMIT 1
";

$stmtUsuario =
    $conexion->prepare($sqlUsuario);

if (!$stmtUsuario) {

    die(
        "Error al comprobar el usuario: "
        . $conexion->error
    );

}

$stmtUsuario->bind_param(
    "s",
    $usuario
);

$stmtUsuario->execute();

$resultadoUsuario =
    $stmtUsuario->get_result();


if ($resultadoUsuario->num_rows > 0) {

    die("
        <h2>⚠️ El usuario ya existe.</h2>

        <p>
            Debes elegir otro nombre de usuario.
        </p>

        <a href='registrar.php'>
            Volver
        </a>
    ");

}


/* ==========================================
   INICIAR TRANSACCIÓN
   ========================================== */

$conexion->begin_transaction();


try {


    /* ======================================
       CREAR USUARIO
       ====================================== */

    $passwordHash =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );


    $sqlUsuarioInsertar = "
        INSERT INTO usuarios
        (
            usuario,
            password,
            id_rol,
            estado
        )
        VALUES
        (
            ?,
            ?,
            4,
            'Activo'
        )
    ";


    $stmtUsuarioInsertar =
        $conexion->prepare(
            $sqlUsuarioInsertar
        );


    if (!$stmtUsuarioInsertar) {

        throw new Exception(
            "Error al preparar el usuario: "
            . $conexion->error
        );

    }


    $stmtUsuarioInsertar->bind_param(
        "ss",
        $usuario,
        $passwordHash
    );


    if (!$stmtUsuarioInsertar->execute()) {

        throw new Exception(
            "Error al crear el usuario: "
            . $stmtUsuarioInsertar->error
        );

    }


    /* ======================================
       OBTENER ID DEL USUARIO
       ====================================== */

    $id_usuario =
        $conexion->insert_id;


    /* ======================================
       INSERTAR PERSONAL PAE
       ====================================== */

    $sql = "
        INSERT INTO personal_pae
        (
            documento,
            nombres,
            apellidos,
            telefono,
            correo,
            id_usuario,
            estado
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'Activo'
        )
    ";


    $stmt =
        $conexion->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            "Error al preparar el registro: "
            . $conexion->error
        );

    }


    $stmt->bind_param(
        "sssssi",
        $documento,
        $nombres,
        $apellidos,
        $telefono,
        $correo,
        $id_usuario
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Error al guardar el personal PAE: "
            . $stmt->error
        );

    }


    /* ======================================
       CONFIRMAR
       ====================================== */

    $conexion->commit();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Personal PAE registrado
    </title>


    <style>

        * {

            margin: 0;
            padding: 0;

            box-sizing: border-box;

            font-family: Arial, sans-serif;

        }


        body {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            background:
                linear-gradient(
                    135deg,
                    #eef3ff,
                    #fdfcff
                );

            padding: 20px;

        }


        .mensaje {

            background: white;

            width: 500px;

            max-width: 100%;

            padding: 40px;

            border-radius: 22px;

            text-align: center;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.10);

        }


        .icono {

            font-size: 65px;

            margin-bottom: 15px;

        }


        h1 {

            color: #6d3bd1;

            margin-bottom: 15px;

        }


        p {

            color: #666;

            line-height: 1.6;

            margin-bottom: 10px;

        }


        .usuario {

            background: #f5f1ff;

            color: #6d3bd1;

            padding: 12px;

            border-radius: 10px;

            margin: 18px 0;

            font-weight: bold;

        }


        .botones {

            display: flex;

            gap: 12px;

            margin-top: 25px;

        }


        .boton {

            flex: 1;

            padding: 13px;

            border-radius: 10px;

            text-decoration: none;

            font-weight: bold;

        }


        .principal {

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #00d4ff
                );

            color: white;

        }


        .secundario {

            background: #eeeeee;

            color: #555;

        }


        @media (max-width: 550px) {

            .botones {

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<div class="mensaje">


    <div class="icono">
        👩‍🍳
    </div>


    <h1>
        ¡Personal PAE registrado!
    </h1>


    <p>

        La persona

        <strong>

            <?php
            echo htmlspecialchars(
                $nombres . " " . $apellidos
            );
            ?>

        </strong>

        fue registrada correctamente.

    </p>


    <div class="usuario">

        🔐 Usuario de acceso:

        <br>

        <?php
        echo htmlspecialchars($usuario);
        ?>

    </div>


    <p>
        Ya puede utilizar estos datos para
        iniciar sesión en el sistema.
    </p>


    <div class="botones">


        <a
            href="registrar.php"
            class="boton principal"
        >

            + Registrar otro

        </a>


        <a
            href="listar.php"
            class="boton secundario"
        >

            📋 Ver personal PAE

        </a>


    </div>


</div>


</body>

</html>


<?php


} catch (Exception $e) {


    /* ======================================
       DESHACER CAMBIOS
       ====================================== */

    $conexion->rollback();


    die("

        <h2>❌ No se pudo registrar el personal PAE.</h2>

        <p>
            "
            . htmlspecialchars(
                $e->getMessage()
            )
            . "
        </p>

        <a href='registrar.php'>
            Volver al formulario
        </a>

    ");

}

?>