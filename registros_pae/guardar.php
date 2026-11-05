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

include("../database/conexion.php");


/* ==========================================
   RECIBIR DATOS
   ========================================== */

$fecha = $_POST["fecha"] ?? "";
$id_jornada = $_POST["id_jornada"] ?? "";
$id_curso = $_POST["id_curso"] ?? "";
$id_personal = $_POST["id_personal"] ?? "";
$observaciones = trim(
    $_POST["observaciones"] ?? ""
);


/* ==========================================
   VALIDAR DATOS
   ========================================== */

if (
    $fecha == "" ||
    $id_jornada == "" ||
    $id_curso == "" ||
    $id_personal == ""
) {

    die("
        <h2>⚠️ Faltan datos de la entrega.</h2>

        <a href='index.php'>
            Volver al registro
        </a>
    ");

}


/* ==========================================
   BUSCAR INFORMACIÓN DEL CURSO
   ========================================== */

$sqlCurso = "
    SELECT
        c.nombre AS curso,
        j.nombre AS jornada
    FROM cursos c
    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada
    WHERE c.id_curso = ?
    LIMIT 1
";

$stmtCurso = $conexion->prepare($sqlCurso);

if (!$stmtCurso) {

    die(
        "Error al consultar el curso: "
        . $conexion->error
    );

}

$stmtCurso->bind_param(
    "i",
    $id_curso
);

$stmtCurso->execute();

$resultadoCurso =
    $stmtCurso->get_result();


if ($resultadoCurso->num_rows == 0) {

    die("
        <h2>⚠️ El curso no existe.</h2>

        <a href='index.php'>
            Volver
        </a>
    ");

}

$curso = $resultadoCurso->fetch_assoc();


/* ==========================================
   BUSCAR ESTUDIANTES DEL CURSO
   ========================================== */

$sqlEstudiantes = "
    SELECT
        id_estudiante,
        documento,
        tipo_documento,
        nombres,
        apellidos,
        foto,
        beneficiario,
        estado
    FROM estudiantes
    WHERE id_curso = ?
      AND estado = 'Activo'
    ORDER BY apellidos ASC, nombres ASC
";

$stmtEstudiantes =
    $conexion->prepare($sqlEstudiantes);

if (!$stmtEstudiantes) {

    die(
        "Error al consultar estudiantes: "
        . $conexion->error
    );

}

$stmtEstudiantes->bind_param(
    "i",
    $id_curso
);

$stmtEstudiantes->execute();

$estudiantes =
    $stmtEstudiantes->get_result();

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
        Estudiantes - Registro PAE
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

            background:
                linear-gradient(
                    135deg,
                    #eef3ff,
                    #fdfcff
                );

        }


        .contenido {

            min-height: 100vh;

            padding:
                90px 25px 50px;

        }


        .contenedor {

            max-width: 1000px;

            margin: auto;

        }


        /* ==================================
           ENCABEZADO
           ================================== */

        .encabezado {

            margin-bottom: 25px;

        }


        .encabezado h1 {

            color: #6d3bd1;

            font-size: 32px;

            margin-bottom: 8px;

        }


        .encabezado p {

            color: #666;

        }


        /* ==================================
           INFORMACIÓN
           ================================== */

        .informacion {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 25px;

        }


        .dato {

            background: white;

            padding: 18px;

            border-radius: 15px;

            box-shadow:
                0 8px 20px
                rgba(0,0,0,.07);

        }


        .dato span {

            display: block;

            color: #777;

            font-size: 14px;

            margin-bottom: 7px;

        }


        .dato strong {

            color: #6d3bd1;

            font-size: 17px;

        }


        /* ==================================
           LISTA
           ================================== */

        .tarjeta {

            background: white;

            padding: 25px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

        }


        .titulo-lista {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            margin-bottom: 20px;

        }


        .titulo-lista h2 {

            color: #6d3bd1;

        }


        .cantidad {

            background: #f0eaff;

            color: #6d3bd1;

            padding: 8px 14px;

            border-radius: 20px;

            font-weight: bold;

        }


        /* ==================================
           ESTUDIANTE
           ================================== */

        .estudiante {

            display: flex;

            align-items: center;

            gap: 15px;

            padding: 15px;

            border:
                1px solid #eeeeee;

            border-radius: 14px;

            margin-bottom: 12px;

            transition: .2s;

        }


        .estudiante:hover {

            background: #faf8ff;

        }


        .foto {

            width: 60px;

            height: 60px;

            border-radius: 50%;

            object-fit: cover;

        }


        .sin-foto {

            width: 60px;

            height: 60px;

            border-radius: 50%;

            background: #eeeeee;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 28px;

        }


        .datos-estudiante {

            flex: 1;

        }


        .datos-estudiante h3 {

            color: #444;

            margin-bottom: 5px;

        }


        .datos-estudiante p {

            color: #777;

            font-size: 14px;

        }


        /* ==================================
           CHECKBOX
           ================================== */

        .check {

            display: flex;

            align-items: center;

            gap: 10px;

            color: #6d3bd1;

            font-weight: bold;

        }


        .check input {

            width: 24px;

            height: 24px;

            cursor: pointer;

            accent-color: #7e57c2;

        }


        /* ==================================
           OBSERVACIÓN
           ================================== */

        textarea {

            width: 100%;

            min-height: 100px;

            padding: 13px;

            margin-top: 20px;

            border:
                1px solid #ddd;

            border-radius: 10px;

            resize: vertical;

            font-size: 15px;

        }


        /* ==================================
           BOTONES
           ================================== */

        .botones {

            display: flex;

            gap: 12px;

            margin-top: 20px;

        }


        .boton {

            flex: 1;

            padding: 14px;

            border: none;

            border-radius: 10px;

            text-align: center;

            text-decoration: none;

            font-weight: bold;

            cursor: pointer;

        }


        .guardar {

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #00d4ff
                );

            color: white;

        }


        .volver {

            background: #eeeeee;

            color: #555;

        }


        .vacio {

            text-align: center;

            padding: 40px;

            color: #777;

        }


        @media (max-width: 700px) {

            .informacion {

                grid-template-columns: 1fr;

            }

            .estudiante {

                align-items: flex-start;

            }

            .botones {

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<?php include("../componentes/sidebar.php"); ?>


<main class="contenido">


<div class="contenedor">


    <!-- ==================================
         ENCABEZADO
         ================================== -->

    <div class="encabezado">

        <h1>
            👥 Estudiantes del grupo
        </h1>

        <p>
            Marca los estudiantes que recibieron
            la alimentación.
        </p>

    </div>


    <!-- ==================================
         INFORMACIÓN DE LA ENTREGA
         ================================== -->

    <div class="informacion">


        <div class="dato">

            <span>
                📅 Fecha
            </span>

            <strong>
                <?php
                echo htmlspecialchars($fecha);
                ?>
            </strong>

        </div>


        <div class="dato">

            <span>
                🏫 Curso
            </span>

            <strong>
                <?php
                echo htmlspecialchars(
                    $curso["curso"]
                );
                ?>
            </strong>

        </div>


        <div class="dato">

            <span>
                🌅 Jornada
            </span>

            <strong>
                <?php
                echo htmlspecialchars(
                    $curso["jornada"]
                );
                ?>
            </strong>

        </div>


    </div>


    <!-- ==================================
         LISTA DE ESTUDIANTES
         ================================== -->

    <div class="tarjeta">


        <div class="titulo-lista">

            <h2>
                🍎 Entrega de alimentación
            </h2>

            <div class="cantidad">

                <?php
                echo $estudiantes->num_rows;
                ?>

                estudiantes

            </div>

        </div>


        <?php if (
            $estudiantes->num_rows > 0
        ) { ?>


            <form
                action="guardar_detalle.php"
                method="POST"
            >


                <!-- DATOS OCULTOS -->

                <input
                    type="hidden"
                    name="fecha"
                    value="<?php
                        echo htmlspecialchars($fecha);
                    ?>"
                >

                <input
                    type="hidden"
                    name="id_jornada"
                    value="<?php
                        echo htmlspecialchars(
                            $id_jornada
                        );
                    ?>"
                >

                <input
                    type="hidden"
                    name="id_curso"
                    value="<?php
                        echo htmlspecialchars(
                            $id_curso
                        );
                    ?>"
                >

                <input
                    type="hidden"
                    name="id_personal"
                    value="<?php
                        echo htmlspecialchars(
                            $id_personal
                        );
                    ?>"
                >

                <input
                    type="hidden"
                    name="observaciones"
                    value="<?php
                        echo htmlspecialchars(
                            $observaciones
                        );
                    ?>"
                >


                <!-- ESTUDIANTES -->

                <?php while (
                    $estudiante =
                    $estudiantes->fetch_assoc()
                ) { ?>


                    <div class="estudiante">


                        <?php if (
                            !empty(
                                $estudiante["foto"]
                            )
                        ) { ?>

                            <img
                                src="../uploads/<?php
                                    echo htmlspecialchars(
                                        $estudiante["foto"]
                                    );
                                ?>"
                                class="foto"
                                alt="Foto"
                            >

                        <?php } else { ?>

                            <div class="sin-foto">
                                👤
                            </div>

                        <?php } ?>


                        <div class="datos-estudiante">

                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $estudiante[
                                        "nombres"
                                    ]
                                    . " "
                                    .
                                    $estudiante[
                                        "apellidos"
                                    ]
                                );
                                ?>

                            </h3>

                            <p>

                                <?php
                                echo htmlspecialchars(
                                    $estudiante[
                                        "tipo_documento"
                                    ]
                                    . " "
                                    .
                                    $estudiante[
                                        "documento"
                                    ]
                                );
                                ?>

                            </p>

                        </div>


                        <label class="check">

                            <input
                                type="checkbox"
                                name="recibidos[]"
                                value="<?php
                                    echo $estudiante[
                                        "id_estudiante"
                                    ];
                                ?>"
                            >

                            Recibió

                        </label>


                    </div>


                <?php } ?>


                <!-- OBSERVACIONES -->

                <textarea
                    name="observacion_detalle"
                    placeholder="Observación general de la entrega..."
                ></textarea>


                <div class="botones">


                    <a
                        href="index.php"
                        class="boton volver"
                    >
                        ← Volver
                    </a>


                    <button
                        type="submit"
                        class="boton guardar"
                    >
                        ✅ Guardar entrega
                    </button>


                </div>


            </form>


        <?php } else { ?>


            <div class="vacio">

                <h2>
                    🎓 No hay estudiantes
                </h2>

                <p>
                    Este grupo todavía no tiene
                    estudiantes activos registrados.
                </p>

            </div>


        <?php } ?>


    </div>


</div>


</main>


</body>

</html>