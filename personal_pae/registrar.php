<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
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

    <title>Registrar Personal PAE</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }


        body {

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #eef3ff,
                    #fdfcff
                );

        }


        .contenido {

            min-height: 100vh;

            padding: 90px 25px 50px;

        }


        .contenedor {

            max-width: 650px;

            margin: auto;

        }


        .tarjeta {

            background: white;

            padding: 35px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

        }


        h1 {

            color: #6d3bd1;

            margin-bottom: 10px;

        }


        .descripcion {

            color: #666;

            line-height: 1.5;

            margin-bottom: 25px;

        }


        .seccion {

            color: #6d3bd1;

            font-size: 19px;

            margin-top: 28px;

            margin-bottom: 18px;

            padding-bottom: 8px;

            border-bottom:
                1px solid #eee;

        }


        .campo {

            margin-bottom: 18px;

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

            border: 1px solid #ddd;

            border-radius: 10px;

            font-size: 16px;

            outline: none;

        }


        input:focus {

            border-color: #7e57c2;

            box-shadow:
                0 0 0 3px
                rgba(126,87,194,.10);

        }


        .ayuda {

            color: #888;

            font-size: 13px;

            margin-top: 6px;

        }


        .boton {

            width: 100%;

            padding: 15px;

            border: none;

            border-radius: 10px;

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #00d4ff
                );

            color: white;

            font-size: 17px;

            font-weight: bold;

            cursor: pointer;

            margin-top: 10px;

        }


        .boton:hover {

            transform: translateY(-2px);

        }


        .volver {

            display: block;

            text-align: center;

            margin-top: 18px;

            color: #6d3bd1;

            text-decoration: none;

            font-weight: bold;

        }


        @media (max-width: 600px) {

            .tarjeta {

                padding: 25px 20px;

            }

        }

    </style>

</head>


<body>


<?php include("../componentes/sidebar.php"); ?>


<main class="contenido">


    <div class="contenedor">


        <div class="tarjeta">


            <h1>
                👩‍🍳 Registrar Personal PAE
            </h1>


            <p class="descripcion">

                Registra los datos del personal
                encargado de apoyar la gestión
                del Programa de Alimentación Escolar.

            </p>


            <form
                action="guardar.php"
                method="POST"
            >


                <!-- ==================================
                     DATOS PERSONALES
                     ================================== -->

                <h2 class="seccion">
                    👤 Datos personales
                </h2>


                <div class="campo">

                    <label for="documento">
                        Documento
                    </label>

                    <input
                        type="text"
                        id="documento"
                        name="documento"
                        placeholder="Número de documento"
                        required
                    >

                </div>


                <div class="campo">

                    <label for="nombres">
                        Nombres
                    </label>

                    <input
                        type="text"
                        id="nombres"
                        name="nombres"
                        placeholder="Nombres"
                        required
                    >

                </div>


                <div class="campo">

                    <label for="apellidos">
                        Apellidos
                    </label>

                    <input
                        type="text"
                        id="apellidos"
                        name="apellidos"
                        placeholder="Apellidos"
                        required
                    >

                </div>


                <div class="campo">

                    <label for="telefono">
                        Teléfono
                    </label>

                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        placeholder="Número de teléfono"
                    >

                </div>


                <div class="campo">

                    <label for="correo">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="correo@ejemplo.com"
                    >

                </div>


                <!-- ==================================
                     DATOS DE ACCESO
                     ================================== -->

                <h2 class="seccion">
                    🔐 Datos de acceso
                </h2>


                <div class="campo">

                    <label for="usuario">
                        Usuario
                    </label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        placeholder="Nombre de usuario"
                        required
                    >

                    <p class="ayuda">
                        Este usuario será utilizado para iniciar sesión.
                    </p>

                </div>


                <div class="campo">

                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Contraseña"
                        minlength="6"
                        required
                    >

                    <p class="ayuda">
                        La contraseña debe tener mínimo 6 caracteres.
                    </p>

                </div>


                <div class="campo">

                    <label for="confirmar_password">
                        Confirmar contraseña
                    </label>

                    <input
                        type="password"
                        id="confirmar_password"
                        name="confirmar_password"
                        placeholder="Repita la contraseña"
                        minlength="6"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="boton"
                >

                    💾 Guardar Personal PAE

                </button>


            </form>


            <a
                href="listar.php"
                class="volver"
            >

                📋 Ver Personal PAE

            </a>


        </div>


    </div>


</main>


</body>

</html>