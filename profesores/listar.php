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
   CONSULTAR PROFESORES
   ========================================== */

$sql = "
    SELECT 
        p.id_profesor,
        p.nombres,
        p.apellidos,
        p.correo,
        p.telefono,
        p.documento,
        p.estado,
        p.es_director,
        p.id_curso_director,
        c.nombre AS nombre_curso,
        j.nombre AS nombre_jornada

    FROM profesores p

    LEFT JOIN cursos c
        ON p.id_curso_director = c.id_curso

    LEFT JOIN jornadas j
        ON c.id_jornada = j.id_jornada

    ORDER BY p.id_profesor ASC
";

$resultado = $conexion->query($sql);

if (!$resultado) {

    die(
        "Error al consultar profesores: "
        . $conexion->error
    );

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

    <title>Profesores - Gestión PAE</title>


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

            max-width: 1100px;

            margin: auto;

        }


        .encabezado {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;

        }


        h1 {

            color: #6d3bd1;

            margin-bottom: 8px;

        }


        .descripcion {

            color: #666;

        }


        .boton {

            display: inline-block;

            padding: 13px 20px;

            border-radius: 10px;

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #00d4ff
                );

            color: white;

            text-decoration: none;

            font-weight: bold;

        }


        .total {

            background: white;

            padding: 15px 20px;

            border-radius: 15px;

            margin-bottom: 20px;

            box-shadow:
                0 8px 20px
                rgba(0,0,0,.08);

            color: #666;

        }


        .total strong {

            color: #6d3bd1;

            font-size: 22px;

        }


        .tabla {

            background: white;

            border-radius: 20px;

            overflow-x: auto;

            box-shadow:
                0 10px 25px
                rgba(0,0,0,.08);

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 900px;

        }


        th {

            background: #7e57c2;

            color: white;

            padding: 15px;

            text-align: center;

        }


        td {

            padding: 14px;

            text-align: center;

            border-bottom:
                1px solid #eeeeee;

            color: #555;

        }


        tr:hover {

            background: #faf8ff;

        }


        .director-si {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            background: #e7f8ee;

            color: #267044;

            font-weight: bold;

            font-size: 13px;

        }


        .director-no {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            background: #f1f1f1;

            color: #777;

            font-weight: bold;

            font-size: 13px;

        }


        .grupo {

            color: #6335bd;

            font-weight: bold;

        }


        .jornada {

            display: block;

            color: #888;

            font-size: 12px;

            margin-top: 4px;

        }


        .acciones {

            display: flex;

            justify-content: center;

            gap: 8px;

        }


        .editar {

            background: #e8ddff;

            color: #6335bd;

            padding: 8px 12px;

            border-radius: 8px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        .eliminar {

            background: #ffe5e5;

            color: #a52d2d;

            padding: 8px 12px;

            border-radius: 8px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        .vacio {

            background: white;

            padding: 50px;

            text-align: center;

            border-radius: 20px;

            box-shadow:
                0 10px 25px
                rgba(0,0,0,.08);

        }


        .vacio h2 {

            color: #6d3bd1;

            margin-bottom: 10px;

        }


        .vacio p {

            color: #777;

        }


        @media (max-width: 650px) {

            .contenido {

                padding:
                    90px 15px 40px;

            }


            .encabezado {

                flex-direction: column;

                align-items: flex-start;

            }


            .boton {

                width: 100%;

                text-align: center;

            }

        }

        .estado-activo {
    color: #16803c;
    font-weight: bold;
}

.estado-inactivo {
    color: #b42318;
    font-weight: bold;
}

    </style>

</head>


<body>


<?php include("../componentes/sidebar.php"); ?>


<main class="contenido">


    <div class="contenedor">


        <div class="encabezado">


            <div>

                <h1>

                    👨‍🏫 Profesores

                </h1>


                <p class="descripcion">

                    Profesores registrados
                    en el sistema.

                </p>

            </div>


            <a
                href="registrar.php"
                class="boton"
            >

                + Registrar profesor

            </a>


        </div>


        <div class="total">

            Total de profesores:

            <strong>

                <?php

                echo $resultado->num_rows;

                ?>

            </strong>

        </div>


        <?php if ($resultado->num_rows > 0) { ?>


            <div class="tabla">


                <table>


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Profesor
                            </th>

                            <th>
                                Correo
                            </th>

                            <th>
                                Director
                            </th>

                            <th>
                                Grupo
                            </th>

                            <th>Estado</th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $profesor =
                        $resultado->fetch_assoc()
                    ) { ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $profesor[
                                        "id_profesor"
                                    ]
                                );

                                ?>

                            </td>


                            <td>

                                👨‍🏫

                                <?php

                                echo htmlspecialchars(
                                    $profesor["nombres"]
                                    . " "
                                    .
                                    $profesor["apellidos"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $profesor["correo"]
                                );

                                ?>

                            </td>


                            <td>


                                <?php if (
                                    $profesor["es_director"] == 1
                                ) { ?>

                                    <span
                                        class="director-si"
                                    >

                                        ✅ Sí

                                    </span>

                                <?php } else { ?>

                                    <span
                                        class="director-no"
                                    >

                                        ❌ No

                                    </span>

                                <?php } ?>


                            </td>


                            <td>


                                <?php if (
                                    $profesor["es_director"] == 1
                                    &&
                                    !empty(
                                        $profesor[
                                            "nombre_curso"
                                        ]
                                    )
                                ) { ?>

                                    <span class="grupo">

                                        🏫

                                        <?php

                                        echo htmlspecialchars(
                                            $profesor[
                                                "nombre_curso"
                                            ]
                                        );

                                        ?>

                                    </span>


                                    <span class="jornada">

                                        <?php

                                        echo htmlspecialchars(
                                            $profesor[
                                                "nombre_jornada"
                                            ]
                                        );

                                        ?>

                                    </span>


                                <?php } else { ?>

                                    —

                                <?php } ?>


                            </td>

                          <td>
    <?php if ($profesor["estado"] === "Activo") { ?>
        <span class="estado-activo">● Activo</span>
    <?php } else { ?>
        <span class="estado-inactivo">● Inactivo</span>
    <?php } ?>
</td>


                            <td>


                                <div class="acciones">


                                    <a
                                        href="editar.php?id=<?php echo $profesor["id_profesor"]; ?>"
                                        class="editar"
                                    >

                                        ✏️ Editar

                                    </a>


                                    <a
                                        href="eliminar.php?id=<?php echo $profesor["id_profesor"]; ?>"
                                        class="eliminar"
                                        onclick="return confirm('¿Seguro que deseas eliminar este profesor?');"
                                    >

                                        🗑️ Eliminar

                                    </a>


                                </div>


                            </td>


                        </tr>


                    <?php } ?>


                    </tbody>


                </table>


            </div>


        <?php } else { ?>


            <div class="vacio">


                <h2>

                    👨‍🏫 No hay profesores registrados

                </h2>


                <p>

                    Registra el primer profesor
                    para comenzar.

                </p>


            </div>


        <?php } ?>


    </div>


</main>


</body>

</html>