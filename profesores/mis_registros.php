<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["id_rol"] != 3) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";

$id_usuario = $_SESSION["id_usuario"];


/* =====================================================
   BUSCAR AL PROFESOR
===================================================== */

$sql_profesor = "
    SELECT
        p.id_profesor,
        p.nombres,
        p.apellidos,
        p.es_director,
        p.id_curso_director
    FROM profesores p
    WHERE p.id_usuario = ?
";

$stmt_profesor = $conexion->prepare($sql_profesor);
$stmt_profesor->bind_param("i", $id_usuario);
$stmt_profesor->execute();

$resultado_profesor = $stmt_profesor->get_result();

if ($resultado_profesor->num_rows == 0) {
    die("No se encontró el profesor.");
}

$profesor = $resultado_profesor->fetch_assoc();


/* =====================================================
   OBTENER TODOS LOS CURSOS
===================================================== */

$sql_cursos = "
    SELECT
        c.id_curso,
        c.nombre AS nombre_curso,
        j.nombre AS nombre_jornada
    FROM cursos c
    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada
    ORDER BY c.nombre ASC
";

$resultado_cursos = $conexion->query($sql_cursos);


/* =====================================================
   CURSO SELECCIONADO
===================================================== */

$id_curso = isset($_GET["id_curso"])
    ? intval($_GET["id_curso"])
    : 0;


/* =====================================================
   REGISTROS PAE
===================================================== */

$registros = null;

if ($id_curso > 0) {

    $sql_registros = "
        SELECT
            e.id_entrega,
            e.fecha,
            e.estado,
            e.observaciones,

            c.nombre AS nombre_curso,
            j.nombre AS nombre_jornada,

            CONCAT(
                p.nombres,
                ' ',
                p.apellidos
            ) AS profesor,

            CONCAT(
                pp.nombres,
                ' ',
                pp.apellidos
            ) AS personal_pae

        FROM entregas_pae e

        INNER JOIN cursos c
            ON e.id_curso = c.id_curso

        INNER JOIN jornadas j
            ON e.id_jornada = j.id_jornada

        LEFT JOIN profesores p
            ON e.id_profesor = p.id_profesor

        LEFT JOIN personal_pae pp
            ON e.id_personal = pp.id_personal

        WHERE e.id_curso = ?

        ORDER BY e.fecha DESC, e.id_entrega DESC
    ";

    $stmt_registros = $conexion->prepare($sql_registros);
    $stmt_registros->bind_param("i", $id_curso);
    $stmt_registros->execute();

    $registros = $stmt_registros->get_result();
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

    <title>Registros PAE</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f4f1fc;
        }


        .contenido {

            margin-left: 250px;

            padding: 40px;
        }


        /* ==========================================
           ENCABEZADO
        ========================================== */

        .encabezado {

            margin-bottom: 25px;
        }


        .encabezado h1 {

            margin: 0;

            color: #5b2c83;
        }


        .encabezado p {

            color: #666;
        }


        /* ==========================================
           GRUPO DEL DIRECTOR
        ========================================== */

        .grupo-director {

            display: block;

            background: linear-gradient(
                135deg,
                #7650d6,
                #19add1
            );

            color: white;

            padding: 25px;

            border-radius: 18px;

            margin-bottom: 25px;

            text-decoration: none;

            transition: 0.2s;

            cursor: pointer;
        }


        .grupo-director:hover {

            transform: translateY(-3px);

            box-shadow:
                0 8px 20px rgba(0,0,0,0.15);
        }


        .grupo-director h2 {

            margin-top: 0;
        }


        .grupo-director .ver {

            display: inline-block;

            margin-top: 15px;

            background: white;

            color: #5b2c83;

            padding: 10px 16px;

            border-radius: 10px;

            font-weight: bold;
        }


        /* ==========================================
           BUSCADOR
        ========================================== */

        .busqueda {

            background: white;

            padding: 25px;

            border-radius: 18px;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.07);

            margin-bottom: 25px;
        }


        .busqueda label {

            display: block;

            font-weight: bold;

            color: #5b2c83;

            margin-bottom: 10px;
        }


        .fila-busqueda {

            display: flex;

            gap: 12px;
        }


        .fila-busqueda select {

            flex: 1;

            padding: 13px;

            border: 1px solid #ddd;

            border-radius: 10px;

            font-size: 15px;
        }


        .btn {

            border: none;

            padding: 13px 20px;

            border-radius: 10px;

            cursor: pointer;

            text-decoration: none;

            font-size: 15px;
        }


        .btn-buscar {

            background: #6f42c1;

            color: white;
        }


        .btn-buscar:hover {

            background: #5b34a3;
        }


        .btn-limpiar {

            background: #ddd;

            color: #333;
        }


        .btn-limpiar:hover {

            background: #ccc;
        }


        /* ==========================================
           TARJETAS DE REGISTROS
        ========================================== */

        .tarjeta-registro {

            display: block;

            width: 100%;

            background: white;

            border-radius: 18px;

            padding: 22px;

            margin-bottom: 15px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.06);

            transition: 0.2s;

            cursor: pointer;

            text-decoration: none;

            color: inherit;
        }


        .tarjeta-registro:hover {

            transform: translateY(-3px);

            box-shadow:
                0 8px 20px rgba(0,0,0,0.12);
        }


        /* ==========================================
           CABECERA DEL REGISTRO
        ========================================== */

        .registro-cabecera {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;
        }


        .registro-cabecera h3 {

            margin: 0;

            color: #5b2c83;
        }


        .fecha {

            color: #666;

            margin-top: 5px;
        }


        /* ==========================================
           DATOS
        ========================================== */

        .datos {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 12px;

            margin-top: 18px;
        }


        .dato {

            background: #f7f4fc;

            padding: 13px;

            border-radius: 10px;
        }


        .dato strong {

            display: block;

            color: #5b2c83;

            margin-bottom: 5px;
        }


        /* ==========================================
           ESTADO
        ========================================== */

        .estado {

            padding: 8px 13px;

            border-radius: 20px;

            background: #e8f7ed;

            color: #187a3d;

            font-weight: bold;

            white-space: nowrap;
        }


        /* ==========================================
           SIN REGISTROS
        ========================================== */

        .sin-registros {

            background: white;

            padding: 35px;

            text-align: center;

            border-radius: 18px;

            color: #666;
        }


        /* ==========================================
           RESPONSIVE
        ========================================== */

        @media (max-width: 900px) {

            .contenido {

                margin-left: 0;

                padding: 20px;
            }


            .fila-busqueda {

                flex-direction: column;
            }


            .datos {

                grid-template-columns: 1fr;
            }


            .registro-cabecera {

                align-items: flex-start;

                flex-direction: column;
            }

        }

    </style>

</head>


<body>


<?php include "../componentes/sidebar_profesor.php"; ?>


<div class="contenido">


    <!-- ==========================================
         ENCABEZADO
    ========================================== -->

    <div class="encabezado">

        <h1>
            📋 Registros PAE
        </h1>

        <p>
            Consulta los registros de entrega de los grupos.
        </p>

    </div>


    <?php

    /* =================================================
       MOSTRAR GRUPO DEL DIRECTOR
    ================================================= */

    if (
        $profesor["es_director"] == 1 &&
        !empty($profesor["id_curso_director"])
    ):

        $id_director =
            $profesor["id_curso_director"];


        $sql_director = "
            SELECT
                c.nombre AS nombre_curso,
                j.nombre AS nombre_jornada
            FROM cursos c
            INNER JOIN jornadas j
                ON c.id_jornada = j.id_jornada
            WHERE c.id_curso = ?
        ";


        $stmt_director =
            $conexion->prepare($sql_director);


        $stmt_director->bind_param(
            "i",
            $id_director
        );


        $stmt_director->execute();


        $resultado_director =
            $stmt_director->get_result();


        if ($resultado_director->num_rows > 0):

            $grupo_director =
                $resultado_director->fetch_assoc();

    ?>


            <!-- ======================================
                 GRUPO CLICKEABLE
            ======================================= -->

            <a
                href="ver_grupo.php?id_curso=<?= $id_director ?>"
                class="grupo-director"
            >

                <h2>
                    🏫 Tu grupo como director
                </h2>


                <strong>

                    <?= htmlspecialchars(
                        $grupo_director["nombre_curso"]
                    ) ?>

                </strong>


                <br>


                Jornada:

                <?= htmlspecialchars(
                    $grupo_director["nombre_jornada"]
                ) ?>


                <div class="ver">

                    👨‍🎓 Ver estudiantes

                </div>

            </a>


    <?php

        endif;

    endif;

    ?>


    <!-- ==========================================
         BUSCADOR
    ========================================== -->

    <div class="busqueda">

        <label>
            🔎 Buscar registros por grupo
        </label>


        <form method="GET">

            <div class="fila-busqueda">


                <select
                    name="id_curso"
                    required
                >

                    <option value="">
                        Seleccione un grupo
                    </option>


                    <?php

                    while (
                        $curso =
                        $resultado_cursos->fetch_assoc()
                    ):

                    ?>

                        <option
                            value="<?= $curso["id_curso"] ?>"
                            <?= (
                                $id_curso ==
                                $curso["id_curso"]
                            )
                                ? "selected"
                                : "" ?>
                        >

                            <?= htmlspecialchars(
                                $curso["nombre_curso"]
                            ) ?>

                            -

                            <?= htmlspecialchars(
                                $curso["nombre_jornada"]
                            ) ?>

                        </option>


                    <?php endwhile; ?>


                </select>


                <button
                    type="submit"
                    class="btn btn-buscar"
                >

                    🔎 Buscar

                </button>


                <a
                    href="mis_registros.php"
                    class="btn btn-limpiar"
                >

                    Limpiar

                </a>


            </div>

        </form>

    </div>


    <!-- ==========================================
         RESULTADOS
    ========================================== -->

    <?php if ($id_curso > 0): ?>


        <?php if (
            $registros &&
            $registros->num_rows > 0
        ): ?>


            <?php while (
                $registro =
                $registros->fetch_assoc()
            ): ?>


                <!-- ==================================
                     TARJETA CLICKEABLE
                =================================== -->

                <a
                    href="ver_registro.php?id_entrega=<?= $registro["id_entrega"] ?>"
                    class="tarjeta-registro"
                >


                    <!-- CABECERA -->

                    <div class="registro-cabecera">


                        <div>

                            <h3>

                                🍽️

                                <?= htmlspecialchars(
                                    $registro["nombre_curso"]
                                ) ?>

                            </h3>


                            <div class="fecha">

                                📅

                                <?= htmlspecialchars(
                                    $registro["fecha"]
                                ) ?>

                            </div>

                        </div>


                        <span class="estado">

                            <?= htmlspecialchars(
                                $registro["estado"]
                            ) ?>

                        </span>


                    </div>


                    <!-- DATOS -->

                    <div class="datos">


                        <div class="dato">

                            <strong>
                                🏫 Jornada
                            </strong>

                            <?= htmlspecialchars(
                                $registro["nombre_jornada"]
                            ) ?>

                        </div>


                        <div class="dato">

                            <strong>
                                👨‍🏫 Profesor
                            </strong>

                            <?= htmlspecialchars(
                                $registro["profesor"]
                                ?? "No registrado"
                            ) ?>

                        </div>


                        <div class="dato">

                            <strong>
                                👨‍🍳 Personal PAE
                            </strong>

                            <?= htmlspecialchars(
                                $registro["personal_pae"]
                                ?? "No registrado"
                            ) ?>

                        </div>


                    </div>


                </a>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="sin-registros">

                📭

                <h2>
                    No hay registros PAE
                </h2>

                <p>
                    Este grupo todavía no tiene
                    registros de entrega.
                </p>

            </div>


        <?php endif; ?>


    <?php else: ?>


        <div class="sin-registros">

            🔎

            <h2>
                Selecciona un grupo
            </h2>

            <p>
                Elige un curso para consultar
                sus registros PAE.
            </p>

        </div>


    <?php endif; ?>


</div>


</body>

</html>