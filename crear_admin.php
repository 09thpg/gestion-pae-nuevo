<?php

include("database/conexion.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = trim($_POST["usuario"]);
    $password = $_POST["password"];

    if ($usuario == "" || $password == "") {

        $mensaje = "Completa todos los campos.";

    } else {

        // Verificar si el usuario ya existe
        $consulta = $conexion->prepare(
            "SELECT id_usuario FROM usuarios WHERE usuario = ?"
        );

        $consulta->bind_param("s", $usuario);
        $consulta->execute();

        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {

            $mensaje = "Ese usuario ya existe.";

        } else {

            // Encriptar la contraseña
            $password_segura = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // El rol 1 corresponde a Administrador
            $id_rol = 1;

            $insertar = $conexion->prepare(
                "
                INSERT INTO usuarios
                (usuario, password, id_rol, estado)
                VALUES (?, ?, ?, 'Activo')
                "
            );

            $insertar->bind_param(
                "ssi",
                $usuario,
                $password_segura,
                $id_rol
            );

            if ($insertar->execute()) {

                $mensaje = "ADMINISTRADOR CREADO CORRECTAMENTE.";

            } else {

                $mensaje = "Error al crear administrador: "
                    . $conexion->error;
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

    <title>Crear administrador</title>

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

            background: linear-gradient(
                135deg,
                #8c52ff,
                #00d4ff
            );

        }

        .contenedor {

            width: 400px;

            max-width: 90%;

            background: white;

            padding: 35px;

            border-radius: 20px;

            box-shadow:
                0 15px 35px rgba(0,0,0,.2);

        }

        h1 {

            text-align: center;

            color: #6d3bd1;

            margin-bottom: 25px;

        }

        label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

        }

        input {

            width: 100%;

            padding: 13px;

            margin-bottom: 18px;

            border: 1px solid #ddd;

            border-radius: 10px;

            font-size: 16px;

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

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

        }

        .mensaje {

            margin-bottom: 20px;

            padding: 12px;

            border-radius: 10px;

            text-align: center;

            background: #eee;

        }

    </style>

</head>

<body>

<div class="contenedor">

    <h1>👤 Crear Administrador</h1>

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
            placeholder="Ejemplo: admin"
            required
        >


        <label for="password">
            Contraseña
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Crea una contraseña"
            required
        >


        <button type="submit">
            Crear administrador
        </button>

    </form>

</div>

</body>

</html>