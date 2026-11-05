<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");


$id_usuario = $_SESSION["id_usuario"];

$sqlPersonal = "
    SELECT id_personal
    FROM personal_pae
    WHERE id_usuario = ?
    LIMIT 1
";

$stmtPersonal = $conexion->prepare($sqlPersonal);
$stmtPersonal->bind_param("i", $id_usuario);
$stmtPersonal->execute();

$resultadoPersonal = $stmtPersonal->get_result();
$personal = $resultadoPersonal->fetch_assoc();

if (!$personal) {
    die("No se encontró el Personal PAE para este usuario.");
}

$id_personal = $personal["id_personal"];

/* ==========================================
   OBTENER PERSONAL PAE
   ========================================== */

$sqlGrupos = "
    SELECT
        e.id_entrega,
        e.fecha,
        e.estado,
        e.observaciones,
        c.nombre AS curso,
        j.nombre AS jornada,
        COALESCE(
            CONCAT(p.nombres, ' ', p.apellidos),
            'Profesor eliminado'
        ) AS profesor,
        COUNT(d.id_detalle) AS total_estudiantes,
        SUM(
            CASE WHEN d.recibido = 1 THEN 1 ELSE 0 END
        ) AS recibieron,
        SUM(
            CASE WHEN d.recibido = 0 THEN 1 ELSE 0 END
        ) AS no_recibieron
    FROM entregas_pae e
    INNER JOIN cursos c
        ON e.id_curso = c.id_curso
    INNER JOIN jornadas j
        ON e.id_jornada = j.id_jornada
    LEFT JOIN profesores p
        ON e.id_profesor = p.id_profesor
    LEFT JOIN detalle_entrega d
        ON e.id_entrega = d.id_entrega
    WHERE e.id_personal = ?
    GROUP BY
        e.id_entrega,
        e.fecha,
        e.estado,
        e.observaciones,
        c.nombre,
        j.nombre,
        p.nombres,
        p.apellidos
    ORDER BY
        e.fecha DESC,
        e.id_entrega DESC
";


$stmtGrupos =
    $conexion->prepare($sqlGrupos);

$stmtGrupos->bind_param(
    "i",
    $id_personal
);

$stmtGrupos->execute();

$resultadoGrupos =
    $stmtGrupos->get_result();


$totalGrupos =
    $resultadoGrupos->num_rows;

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
        Mis registros
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
                55px 25px 50px;

            margin-left: 240px;

        }


        .contenedor {

            width: 100%;

            max-width: 1150px;

            margin: auto;

        }


        /* =================================
           ENCABEZADO
           ================================= */

        .encabezado {

            margin-bottom: 25px;

        }


        .icono {

            font-size: 45px;

            margin-bottom: 8px;

        }


        .encabezado h1 {

            color: #6d3bd1;

            font-size: 31px;

            margin-bottom: 8px;

        }


        .encabezado p {

            color: #666;

            font-size: 15px;

        }


        /* =================================
           CONTADOR
           ================================= */

        .contador {

            background: white;

            padding: 15px 18px;

            border-radius: 14px;

            margin-bottom: 22px;

            box-shadow:
                0 6px 20px
                rgba(0,0,0,.05);

            color: #666;

        }


        .contador strong {

            color: #6d3bd1;

            font-size: 18px;

        }


        /* =================================
           GRUPOS
           ================================= */

        .grupos {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

        }


        .grupo {

            background: white;

            border-radius: 20px;

            padding: 24px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

            transition: .2s;

        }


        .grupo:hover {

            transform: translateY(-3px);

            box-shadow:
                0 14px 35px
                rgba(0,0,0,.12);

        }


        /* =================================
           PARTE SUPERIOR
           ================================= */

        .grupo-superior {

            display: flex;

            justify-content:
                space-between;

            align-items:
                flex-start;

            gap: 15px;

            margin-bottom: 18px;

        }


        .grupo-icono {

            width: 52px;

            height: 52px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 14px;

            background: #f2edff;

            font-size: 27px;

        }


        .estado {

            padding: 7px 10px;

            border-radius: 8px;

            font-size: 12px;

            font-weight: bold;

        }


        .estado.proceso {

            background: #fff4d6;

            color: #9a6b00;

        }


        .estado.terminado {

            background: #e8fff1;

            color: #16834b;

        }


        /* =================================
           INFORMACIÓN
           ================================= */

        .grupo h2 {

            color: #6d3bd1;

            font-size: 22px;

            margin-bottom: 15px;

        }


        .informacion {

            display: flex;

            flex-direction: column;

            gap: 9px;

            margin-bottom: 20px;

        }


        .dato {

            color: #555;

            font-size: 14px;

        }


        .dato strong {

            color: #444;

        }


        /* =================================
           ESTADÍSTICAS
           ================================= */

        .estadisticas {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 8px;

            margin-bottom: 20px;

        }


        .estadistica {

            text-align: center;

            padding: 10px 5px;

            border-radius: 10px;

            background: #f8f6ff;

        }


        .estadistica .numero {

            display: block;

            font-size: 20px;

            font-weight: bold;

            color: #6d3bd1;

            margin-bottom: 3px;

        }


        .estadistica span {

            color: #777;

            font-size: 11px;

        }


        /* =================================
           BOTÓN VER GRUPO
           ================================= */

        .btn-ver {

            display: block;

            width: 100%;

            padding: 13px;

            border-radius: 11px;

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #6d3bd1
                );

            color: white;

            text-align: center;

            text-decoration: none;

            font-weight: bold;

            font-size: 14px;

            transition: .2s;

        }


        .btn-ver:hover {

            transform: translateY(-2px);

        }


        /* =================================
           SIN GRUPOS
           ================================= */

        .sin-registros {

            background: white;

            border-radius: 20px;

            padding: 60px 25px;

            text-align: center;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

        }


        .sin-registros .emoji {

            font-size: 60px;

            margin-bottom: 15px;

        }


        .sin-registros h2 {

            color: #6d3bd1;

            margin-bottom: 8px;

        }


        .sin-registros p {

            color: #777;

        }


        /* =================================
           MÓVIL
           ================================= */

        @media (max-width: 850px) {

            .grupos {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 700px) {

            .contenido {

                margin-left: 0;

                padding:
                    40px 15px;

            }


            .encabezado h1 {

                font-size: 27px;

            }


            .grupo {

                padding: 20px;

            }

        }

    </style>

</head>


<body>


<?php include("../componentes/sidebar_personal.php"); ?>


<main class="contenido">


    <div class="contenedor">


        <!-- ENCABEZADO -->

        <div class="encabezado">

            <div class="icono">
                📋
            </div>

            <h1>
                Mis registros
            </h1>

            <p>
                Aquí puedes consultar los grupos
                en los que has registrado refrigerios.
            </p>

        </div>


        <!-- CONTADOR -->

        <div class="contador">

            📊 Total de grupos:

            <strong>
                <?php echo $totalGrupos; ?>
            </strong>

        </div>


        <?php if ($totalGrupos > 0) { ?>


            <div class="grupos">


                <?php while (
                    $grupo =
                    $resultadoGrupos->fetch_assoc()
                ) { ?>


                    <div class="grupo">


                        <!-- PARTE SUPERIOR -->

                        <div class="grupo-superior">


                            <div class="grupo-icono">
                                🏫
                            </div>


                            <?php if (
                                $grupo["estado"] === "Terminado"
                            ) { ?>

                                <span class="estado terminado">

                                    🟢 Terminado

                                </span>

                            <?php } else { ?>

                                <span class="estado proceso">

                                    🟡 En proceso

                                </span>

                            <?php } ?>


                        </div>


                        <!-- CURSO -->

                        <h2>

                            <?php

                            echo htmlspecialchars(
                                $grupo["curso"]
                            );

                            ?>

                        </h2>


                        <!-- INFORMACIÓN -->

                        <div class="informacion">


                            <div class="dato">

                                📅

                                <strong>
                                    Fecha:
                                </strong>

                                <?php

                                echo htmlspecialchars(
                                    $grupo["fecha"]
                                );

                                ?>

                            </div>


                            <div class="dato">

                                🌞

                                <strong>
                                    Jornada:
                                </strong>

                                <?php

                                echo htmlspecialchars(
                                    $grupo["jornada"]
                                );

                                ?>

                            </div>


                            <div class="dato">

                                👨‍🏫

                                <strong>
                                    Profesor:
                                </strong>

                                <?php

                                echo htmlspecialchars(
                                    $grupo["profesor"]
                                );

                                ?>

                            </div>


                        </div>


                        <!-- ESTADÍSTICAS -->

                        <div class="estadisticas">


                            <div class="estadistica">

                                <span class="numero">

                                    <?php

                                    echo $grupo[
                                        "total_estudiantes"
                                    ];

                                    ?>

                                </span>

                                <span>
                                    👥 Estudiantes
                                </span>

                            </div>


                            <div class="estadistica">

                                <span class="numero">

                                    <?php

                                    echo $grupo[
                                        "recibieron"
                                    ] ?? 0;

                                    ?>

                                </span>

                                <span>
                                    ✅ Recibieron
                                </span>

                            </div>


                            <div class="estadistica">

                                <span class="numero">

                                    <?php

                                    echo $grupo[
                                        "no_recibieron"
                                    ] ?? 0;

                                    ?>

                                </span>

                                <span>
                                    ❌ No recibieron
                                </span>

                            </div>


                        </div>


                        <!-- VER GRUPO -->

                        <a
                            href="ver_grupo.php?id_entrega=<?php echo (int)$grupo["id_entrega"]; ?>"
                            class="btn-ver"
                        >

                            👀 Ver grupo

                        </a>


                    </div>


                <?php } ?>


            </div>


        <?php } else { ?>


            <div class="sin-registros">


                <div class="emoji">
                    🍎
                </div>


                <h2>
                    Aún no tienes grupos registrados
                </h2>


                <p>

                    Cuando registres un refrigerio,
                    los grupos aparecerán aquí.

                </p>


            </div>


        <?php } ?>


    </div>


</main>


</body>

</html>