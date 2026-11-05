<?php

session_start();

include("database/conexion.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = trim($_POST["usuario"]);
    $password = $_POST["password"];

    if ($usuario == "" || $password == "") {

        $mensaje = "Por favor completa todos los campos.";

    } else {

        $sql = "
            SELECT
                u.id_usuario,
                u.usuario,
                u.password,
                u.id_rol,
                r.nombre AS nombre_rol
            FROM usuarios u
            INNER JOIN roles r
                ON u.id_rol = r.id_rol
            WHERE u.usuario = ?
            AND u.estado = 'Activo'
            LIMIT 1
        ";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {

            $mensaje = "Error en la consulta.";

        } else {

            $stmt->bind_param("s", $usuario);

            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($resultado->num_rows == 1) {

                $datos = $resultado->fetch_assoc();

                if (password_verify($password, $datos["password"])) {

                    // Guardar datos de la sesión
                    $_SESSION["id_usuario"] = $datos["id_usuario"];
                    $_SESSION["usuario"] = $datos["usuario"];
                    $_SESSION["id_rol"] = $datos["id_rol"];
                    $_SESSION["rol"] = $datos["nombre_rol"];


                    /*
                    ==========================================
                    REDIRECCIÓN SEGÚN EL TIPO DE USUARIO
                    ==========================================
                    */

                    if ($datos["id_rol"] == 2) {
    header("Location: estudiantes/perfil.php");
    exit();
}

if ($_SESSION["id_rol"] == 3) {
    header("Location: profesores/perfil.php");
    exit();
}

if ($datos["id_rol"] == 4) {
    header("Location: personal_pae/perfil.php");
    exit();
}

if ($datos["id_rol"] == 1) {
    header("Location: dashboard.php");
    exit();
}

header("Location: dashboard.php");
exit();

                } else {

                    $mensaje = "Usuario o contraseña incorrectos.";

                }

            } else {

                $mensaje = "Usuario o contraseña incorrectos.";

            }
        }
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Iniciar sesión - Gestión PAE</title>

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

            background: linear-gradient(
                135deg,
                #8c52ff,
                #00d4ff
            );

            padding: 20px;

        }


        .contenedor-login {

            width: 430px;

            max-width: 100%;

            background: white;

            padding: 40px;

            border-radius: 25px;

            box-shadow:
                0 15px 40px rgba(0,0,0,.20);

        }


        .logo {

            text-align: center;

            font-size: 55px;

            margin-bottom: 10px;

        }


        h1 {

            text-align: center;

            color: #6d3bd1;

            font-size: 30px;

            margin-bottom: 8px;

        }


        .subtitulo {

            text-align: center;

            color: #777;

            margin-bottom: 30px;

        }


        .mensaje {

            background: #ffe5e5;

            color: #b3261e;

            padding: 12px;

            border-radius: 10px;

            text-align: center;

            margin-bottom: 20px;

        }


        label {

            display: block;

            color: #444;

            font-weight: bold;

            margin-bottom: 8px;

        }


        input {

            width: 100%;

            padding: 14px;

            border: 1px solid #d7d7d7;

            border-radius: 10px;

            outline: none;

            font-size: 16px;

            margin-bottom: 20px;

        }


        input:focus {

            border-color: #7e57c2;

            box-shadow:
                0 0 0 3px rgba(126,87,194,.12);

        }


        button {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 10px;

            background: linear-gradient(
                90deg,
                #8c52ff,
                #00d4ff
            );

            color: white;

            font-size: 17px;

            font-weight: bold;

            cursor: pointer;

            transition: .3s;

        }


        button:hover {

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px rgba(0,0,0,.15);

        }


        .pie {

            text-align: center;

            margin-top: 25px;

            color: #888;

            font-size: 13px;

        }

.btn-volver {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1000;

    display: inline-block;
    padding: 10px 18px;
    border-radius: 10px;

    background: #f1ebfb;
    color: #6f42c1;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;

    transition: 0.3s;
}

.btn-volver:hover {
    background: #e2d7fa;
    transform: translateY(-2px);
}


/* ===== DISEÑO RESPONSIVE ===== */

/* Tabletas */
@media (max-width: 768px) {
    body {
        padding: 20px;
    }

    .login-container {
        width: 100%;
        max-width: 430px;
        margin: 0 auto;
    }

    .btn-volver {
        top: 15px;
        left: 15px;
    }
}

/* Celulares */
@media (max-width: 480px) {
    body {
        min-height: 100vh;
        padding: 75px 16px 20px;
    }

    .login-container {
        width: 100%;
        max-width: 100%;
        padding: 28px 22px;
        border-radius: 22px;
    }

    .btn-volver {
        top: 15px;
        left: 15px;
        padding: 9px 14px;
        font-size: 13px;
    }

    h1 {
        font-size: 27px;
    }

    input,
    button {
        width: 100%;
        max-width: 100%;
    }
}

/* ===== CORRECCIÓN RESPONSIVE PARA CELULARES ===== */

@media (max-width: 600px) {
    body {
        min-height: 100vh;
        margin: 0;
        padding: 75px 16px 24px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .login-container {
        width: 100%;
        max-width: 430px;
        margin: 0 auto;
        padding: 28px 24px;
        border-radius: 24px;
    }

    .btn-volver {
        position: fixed;
        top: 12px;
        left: 12px;
        z-index: 1000;
    }

    input,
    button {
        width: 100%;
        max-width: 100%;
    }
}

@media (max-width: 380px) {
    body {
        padding: 68px 12px 20px;
    }

    .login-container {
        padding: 24px 18px;
    }

    .login-container h1 {
        font-size: 25px;
    }

    .login-container p {
        font-size: 14px;
    }
}


    </style>

</head>


<body>


<a href="index.php" class="btn-volver">← Volver</a>

<div class="contenedor-login">


    <div class="logo">
        🍽️
    </div>


    <h1>
        Gestión del PAE
    </h1>


    <p class="subtitulo">
        Sistema de Gestión del Programa
        de Alimentación Escolar
    </p>


    <?php if ($mensaje != "") { ?>

        <div class="mensaje">

            <?php echo htmlspecialchars($mensaje); ?>

        </div>

    <?php } ?>


    <form method="POST">


        <label for="usuario">
            Usuario
        </label>

        <input
            type="text"
            id="usuario"
            name="usuario"
            placeholder="Ingresa tu usuario"
            required
        >


        <label for="password">
            Contraseña
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Ingresa tu contraseña"
            required
        >


        <button type="submit">
            Iniciar sesión
        </button>


    </form>


    <div class="pie">

        Gestión PAE © 2026

    </div>


</div>


</body>

</html>