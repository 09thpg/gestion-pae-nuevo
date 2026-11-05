<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");


/* ==============================
   CONSULTAR CURSOS
   ============================== */

$sqlCursos = "
    SELECT
        c.id_curso,
        c.nombre AS curso,
        j.nombre AS jornada
    FROM cursos c
    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada
    ORDER BY c.nombre ASC
";

$resultadoCursos = $conexion->query($sqlCursos);


/* ==============================
   CONSULTAR PROFESORES
   ============================== */

$sqlProfesores = "
    SELECT
        id_profesor,
        nombres,
        apellidos
    FROM profesores
    WHERE estado = 'Activo'
    ORDER BY apellidos ASC, nombres ASC
";

$resultadoProfesores = $conexion->query($sqlProfesores);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Registrar refrigerio</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(
                135deg,
                #eef3ff,
                #fdfcff
            );
        }

        .contenido {
            min-height: 100vh;
            padding: 70px 25px 50px;
            display: flex;
            justify-content: center;
        }

        .contenedor {
            width: 100%;
            max-width: 700px;
        }

        .encabezado {
            text-align: center;
            margin-bottom: 25px;
        }

        .icono {
            font-size: 60px;
            margin-bottom: 8px;
        }

        .encabezado h1 {
            color: #6d3bd1;
            font-size: 32px;
            margin-bottom: 8px;
        }

        .encabezado p {
            color: #666;
            font-size: 16px;
        }

        .tarjeta {
            background: white;
            padding: 35px;
            border-radius: 25px;
            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);
        }

        .titulo {
            color: #6d3bd1;
            font-size: 22px;
            margin-bottom: 25px;
        }

        .campo {
            margin-bottom: 22px;
        }

        .campo label {
            display: block;
            color: #555;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .campo select {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 12px;
            background: white;
            color: #555;
            font-size: 15px;
            outline: none;
        }

        .campo select:focus {
            border-color: #8c52ff;
            box-shadow:
                0 0 0 3px
                rgba(140,82,255,.10);
        }

        .boton {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #00d4ff
                );
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: .2s;
        }

        .boton:hover {
            transform: translateY(-2px);
        }

        .volver {
            display: block;
            margin-top: 18px;
            text-align: center;
            color: #6d3bd1;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 700px) {

            .contenido {
                padding: 55px 15px 40px;
            }

            .tarjeta {
                padding: 25px 20px;
            }

            .encabezado h1 {
                font-size: 27px;
            }

        }

    </style>

</head>


<body>


<?php include("../componentes/sidebar_personal.php"); ?>


<main class="contenido">

    <div class="contenedor">


        <div class="encabezado">

            <div class="icono">
                🍎
            </div>

            <h1>
                Registrar refrigerio
            </h1>

            <p>
                Selecciona el curso y el profesor
                antes de comenzar.
            </p>

        </div>


        <div class="tarjeta">

            <h2 class="titulo">
                📋 Información de la entrega
            </h2>


            <form
                action="registro_refrigerio.php"
                method="GET"
            >


                <div class="campo">

                    <label for="id_curso">
                        🏫 Curso
                    </label>

                    <select
                        name="id_curso"
                        id="id_curso"
                        required
                    >

                        <option value="">
                            -- Selecciona un curso --
                        </option>


                        <?php while (
                            $curso =
                            $resultadoCursos->fetch_assoc()
                        ) { ?>

                            <option
                                value="<?php
                                    echo $curso["id_curso"];
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $curso["curso"]
                                    . " - "
                                    . $curso["jornada"]
                                );
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <div class="campo">

                    <label for="id_profesor">
                        👨‍🏫 Profesor
                    </label>

                    <select
                        name="id_profesor"
                        id="id_profesor"
                        required
                    >

                        <option value="">
                            -- Selecciona un profesor --
                        </option>


                        <?php while (
                            $profesor =
                            $resultadoProfesores->fetch_assoc()
                        ) { ?>

                            <option
                                value="<?php
                                    echo $profesor["id_profesor"];
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $profesor["nombres"]
                                    . " "
                                    . $profesor["apellidos"]
                                );
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <button
                    type="submit"
                    class="boton"
                >

                    🍎 Comenzar registro

                </button>


            </form>


            <a
                href="perfil.php"
                class="volver"
            >

                ← Volver al inicio

            </a>

        </div>

    </div>

</main>


</body>

</html>