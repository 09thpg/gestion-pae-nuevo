<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");

$mensaje = "";


/* ==========================================
   GUARDAR CURSO
   ========================================== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"]);
    $id_jornada = $_POST["id_jornada"];

    if ($nombre == "" || $id_jornada == "") {

        $mensaje = "Completa todos los campos.";

    } else {

        $sql = "
            INSERT INTO cursos
            (nombre, id_jornada)
            VALUES (?, ?)
        ";

        $stmt = $conexion->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "si",
                $nombre,
                $id_jornada
            );

            if ($stmt->execute()) {

                $mensaje = "Curso creado correctamente.";

            } else {

                $mensaje = "No se pudo crear el curso.";

            }

        } else {

            $mensaje = "Error en la consulta.";

        }
    }
}


/* ==========================================
   OBTENER JORNADAS
   ========================================== */

$sqlJornadas = "
    SELECT id_jornada, nombre
    FROM jornadas
    ORDER BY id_jornada
";

$jornadas = $conexion->query($sqlJornadas);


/* ==========================================
   OBTENER CURSOS
   ========================================== */

$sqlCursos = "
    SELECT
        c.nombre AS curso,
        j.nombre AS jornada
    FROM cursos c
    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada
    ORDER BY c.nombre
";

$cursos = $conexion->query($sqlCursos);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Cursos - Gestión PAE</title>


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

            padding: 90px 30px 50px;

        }


        .contenedor {

            max-width: 800px;

            margin: auto;

        }


        h1 {

            color: #6d3bd1;

            margin-bottom: 10px;

        }


        .descripcion {

            color: #666;

            margin-bottom: 25px;

        }


        .tarjeta {

            background: white;

            padding: 30px;

            border-radius: 20px;

            box-shadow:
                0 10px 25px rgba(0,0,0,.08);

            margin-bottom: 25px;

        }


        .tarjeta h2 {

            color: #6d3bd1;

            margin-bottom: 20px;

        }


        label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            color: #444;

        }


        input,
        select {

            width: 100%;

            padding: 13px;

            border: 1px solid #ddd;

            border-radius: 10px;

            margin-bottom: 18px;

            font-size: 16px;

        }


        button {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 10px;

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

        }


        .mensaje {

            padding: 12px;

            margin-bottom: 20px;

            border-radius: 10px;

            background: #eee;

            text-align: center;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th {

            background: #7e57c2;

            color: white;

            padding: 12px;

        }


        td {

            padding: 12px;

            text-align: center;

            border-bottom: 1px solid #eee;

        }


    </style>

</head>


<body>


<?php include("../componentes/sidebar.php"); ?>


<main class="contenido">


    <div class="contenedor">


        <h1>
            🏫 Cursos y grupos
        </h1>


        <p class="descripcion">

            Crea los grupos de la institución
            y asigna su jornada.

        </p>


        <div class="tarjeta">


            <h2>
                ➕ Crear nuevo grupo
            </h2>


            <?php if ($mensaje != "") { ?>

                <div class="mensaje">

                    <?php
                    echo htmlspecialchars($mensaje);
                    ?>

                </div>

            <?php } ?>


            <form method="POST">


                <label for="nombre">
                    Nombre del curso / grupo
                </label>


                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Ejemplo: 6°A"
                    required
                >


                <label for="id_jornada">
                    Jornada
                </label>


                <select
                    id="id_jornada"
                    name="id_jornada"
                    required
                >

                    <option value="">
                        Selecciona una jornada
                    </option>


                    <?php while (
                        $jornada =
                        $jornadas->fetch_assoc()
                    ) { ?>

                        <option
                            value="<?php
                                echo $jornada["id_jornada"];
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $jornada["nombre"]
                            );
                            ?>

                        </option>

                    <?php } ?>

                </select>


                <button type="submit">

                    Crear grupo

                </button>


            </form>


        </div>


        <div class="tarjeta">


            <h2>
                📋 Grupos registrados
            </h2>


            <?php if (
                $cursos &&
                $cursos->num_rows > 0
            ) { ?>


                <table>

                    <thead>

                        <tr>

                            <th>
                                Grupo
                            </th>

                            <th>
                                Jornada
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while (
                        $curso =
                        $cursos->fetch_assoc()
                    ) { ?>

                        <tr>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $curso["curso"]
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $curso["jornada"]
                                );
                                ?>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>


            <?php } else { ?>

                <p>
                    Todavía no hay grupos registrados.
                </p>

            <?php } ?>


        </div>


    </div>


</main>


</body>

</html>