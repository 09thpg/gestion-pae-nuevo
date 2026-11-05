<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../login.php");
    exit();

}

include("../database/conexion.php");


/* ==========================================
   RECIBIR DATOS DEL FORMULARIO
   ========================================== */

$tipo_documento = $_POST["tipo_documento"] ?? "";

$documento = trim(
    $_POST["documento"] ?? ""
);

$nombres = trim(
    $_POST["nombres"] ?? ""
);

$apellidos = trim(
    $_POST["apellidos"] ?? ""
);

$fecha_nacimiento =
    $_POST["fecha_nacimiento"] ?? null;

$sexo =
    $_POST["sexo"] ?? null;

$correo = trim(
    $_POST["correo"] ?? ""
);

$id_curso =
    $_POST["id_curso"] ?? "";

$beneficiario =
    $_POST["beneficiario"] ?? "";


/* ==========================================
   DATOS DE ACCESO
   ========================================== */

$usuario = trim(
    $_POST["usuario"] ?? ""
);

$password =
    $_POST["password"] ?? "";

$confirmar_password =
    $_POST["confirmar_password"] ?? "";


/* ==========================================
   COMPROBAR DATOS OBLIGATORIOS
   ========================================== */

if (

    $tipo_documento == "" ||

    $documento == "" ||

    $nombres == "" ||

    $apellidos == "" ||

    $id_curso == "" ||

    $beneficiario == "" ||

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
   COMPROBAR CONTRASEÑAS
   ========================================== */

if ($password !== $confirmar_password) {

    die("

        <h2>⚠️ Las contraseñas no coinciden.</h2>

        <p>
            Verifica que ambas contraseñas sean iguales.
        </p>

        <a href='registrar.php'>
            Volver al formulario
        </a>

    ");

}


/* ==========================================
   COMPROBAR LONGITUD DE CONTRASEÑA
   ========================================== */

if (strlen($password) < 6) {

    die("

        <h2>⚠️ Contraseña demasiado corta.</h2>

        <p>
            La contraseña debe tener mínimo 6 caracteres.
        </p>

        <a href='registrar.php'>
            Volver al formulario
        </a>

    ");

}


/* ==========================================
   COMPROBAR SI EXISTE EL DOCUMENTO
   ========================================== */

$sqlDocumento = "

    SELECT id_estudiante

    FROM estudiantes

    WHERE documento = ?

    LIMIT 1

";

$stmtDocumento =
    $conexion->prepare($sqlDocumento);


if (!$stmtDocumento) {

    die(
        "Error al preparar la consulta: "
        . $conexion->error
    );

}


$stmtDocumento->bind_param(
    "s",
    $documento
);

$stmtDocumento->execute();

$resultadoDocumento =
    $stmtDocumento->get_result();


if ($resultadoDocumento->num_rows > 0) {

    die("

        <h2>⚠️ Este estudiante ya está registrado.</h2>

        <p>
            Ya existe un estudiante con el documento
            <strong>"
            . htmlspecialchars($documento)
            . "</strong>.
        </p>

        <a href='registrar.php'>
            Volver al formulario
        </a>

    ");

}


/* ==========================================
   COMPROBAR SI EXISTE EL USUARIO
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
        "Error al preparar la consulta de usuario: "
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
            El nombre de usuario
            <strong>"
            . htmlspecialchars($usuario)
            . "</strong>
            ya está siendo utilizado.
        </p>

        <a href='registrar.php'>
            Volver al formulario
        </a>

    ");

}


/* ==========================================
   GUARDAR FOTO
   ========================================== */

$foto = null;

$rutaFoto = null;


if (

    isset($_FILES["foto"]) &&

    $_FILES["foto"]["error"] == UPLOAD_ERR_OK

) {

    $nombreOriginal =
        $_FILES["foto"]["name"];


    $extension = strtolower(

        pathinfo(

            $nombreOriginal,

            PATHINFO_EXTENSION

        )

    );


    $extensionesPermitidas = [

        "jpg",
        "jpeg",
        "png",
        "webp"

    ];


    if (
        !in_array(
            $extension,
            $extensionesPermitidas
        )
    ) {

        die("

            <h2>⚠️ Formato de imagen no permitido.</h2>

            <p>
                Usa JPG, JPEG, PNG o WEBP.
            </p>

            <a href='registrar.php'>
                Volver
            </a>

        ");

    }


    $foto =

        $documento
        . "_"
        . time()
        . "."
        . $extension;


    $rutaFoto =

        "../uploads/"
        . $foto;


    if (

        !move_uploaded_file(

            $_FILES["foto"]["tmp_name"],

            $rutaFoto

        )

    ) {

        die("

            <h2>⚠️ No se pudo guardar la foto.</h2>

            <a href='registrar.php'>
                Volver
            </a>

        ");

    }

}


/* ==========================================
   CONTRASEÑA SEGURA
   ========================================== */

$passwordHash =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );


/* ==========================================
   TRANSACCIÓN
   ========================================== */

$conexion->begin_transaction();


try {


    /* ======================================
       CREAR USUARIO
       ====================================== */

    $sqlCrearUsuario = "

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
            2,
            'Activo'
        )

    ";


    $stmtCrearUsuario =
        $conexion->prepare(
            $sqlCrearUsuario
        );


    if (!$stmtCrearUsuario) {

        throw new Exception(
            "Error al preparar el usuario: "
            . $conexion->error
        );

    }


    $stmtCrearUsuario->bind_param(

        "ss",

        $usuario,

        $passwordHash

    );


    if (!$stmtCrearUsuario->execute()) {

        throw new Exception(
            "Error al crear el usuario: "
            . $stmtCrearUsuario->error
        );

    }


    /* ======================================
       OBTENER ID DEL USUARIO
       ====================================== */

    $id_usuario =
        $conexion->insert_id;


    /* ======================================
       GUARDAR ESTUDIANTE
       ====================================== */

    $sqlEstudiante = "

        INSERT INTO estudiantes

        (

            documento,
            tipo_documento,
            nombres,
            apellidos,
            fecha_nacimiento,
            sexo,
            correo,
            foto,
            beneficiario,
            estado,
            id_curso,
            id_usuario

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
            ?,
            ?,
            'Activo',
            ?,
            ?

        )

    ";


    $stmtEstudiante =
        $conexion->prepare(
            $sqlEstudiante
        );


    if (!$stmtEstudiante) {

        throw new Exception(
            "Error al preparar el estudiante: "
            . $conexion->error
        );

    }


    $beneficiario =
        (int) $beneficiario;

    $id_curso =
        (int) $id_curso;


    $stmtEstudiante->bind_param(

        "ssssssssiii",

        $documento,

        $tipo_documento,

        $nombres,

        $apellidos,

        $fecha_nacimiento,

        $sexo,

        $correo,

        $foto,

        $beneficiario,

        $id_curso,

        $id_usuario

    );


    if (!$stmtEstudiante->execute()) {

        throw new Exception(
            "Error al guardar el estudiante: "
            . $stmtEstudiante->error
        );

    }


    /* ======================================
       CONFIRMAR TODO
       ====================================== */

    $conexion->commit();


} catch (Exception $e) {


    /* ======================================
       DESHACER CAMBIOS
       ====================================== */

    $conexion->rollback();


    /* Si se había guardado una foto,
       eliminarla */

    if (
        $rutaFoto !== null &&
        file_exists($rutaFoto)
    ) {

        unlink($rutaFoto);

    }


    die("

        <h2>⚠️ No se pudo registrar el estudiante.</h2>

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


/* ==========================================
   MENSAJE DE ÉXITO
   ========================================== */

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
        Estudiante registrado
    </title>


    <style>

        * {

            box-sizing: border-box;

            font-family: Arial, sans-serif;

        }


        body {

            margin: 0;

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

            width: 500px;

            max-width: 100%;

            background: white;

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

            margin-bottom: 10px;

        }


        p {

            color: #666;

            line-height: 1.6;

        }


        .datos-acceso {

            background: #f4f0ff;

            padding: 18px;

            border-radius: 12px;

            margin-top: 20px;

            text-align: left;

        }


        .datos-acceso strong {

            color: #6d3bd1;

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


        @media (max-width: 500px) {

            .botones {

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<div class="mensaje">


    <div class="icono">
        🎉
    </div>


    <h1>
        ¡Estudiante registrado!
    </h1>


    <p>

        <strong>

            <?php

            echo htmlspecialchars(
                $nombres
                . " "
                . $apellidos
            );

            ?>

        </strong>

        fue registrado correctamente
        en el sistema.

    </p>


    <p>

        Documento:

        <strong>

            <?php

            echo htmlspecialchars(
                $documento
            );

            ?>

        </strong>

    </p>


    <div class="datos-acceso">

        <p>

            👤 <strong>Usuario:</strong>

            <?php

            echo htmlspecialchars(
                $usuario
            );

            ?>

        </p>


        <p>

            🔐 La contraseña fue guardada
            de forma segura.

        </p>

    </div>


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

            📋 Ver estudiantes

        </a>


    </div>


</div>


</body>

</html>