<?php

session_start();


/* ==========================================
   COMPROBAR SESIÓN
   ========================================== */

if (!isset($_SESSION["id_usuario"])) {

    header("Location: ../login.php");

    exit();

}


/* ==========================================
   COMPROBAR ROL
   ========================================== */

// Solo el administrador puede ver el personal PAE

if ($_SESSION["id_rol"] != 1) {

    header("Location: ../estudiantes/perfil.php");

    exit();

}


include("../database/conexion.php");


/* ==========================================
   CONSULTAR PERSONAL PAE
   ========================================== */

$sql = "
    SELECT
        id_personal,
        documento,
        nombres,
        apellidos,
        telefono,
        correo,
        estado
    FROM personal_pae
    ORDER BY apellidos ASC, nombres ASC
";


$resultado = $conexion->query($sql);


if (!$resultado) {

    die(
        "Error al consultar personal PAE: "
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

    <title>
        Personal PAE
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


        .encabezado h1 {

            color: #6d3bd1;

            margin-bottom: 8px;

        }


        .encabezado p {

            color: #666;

        }


        .boton-agregar {

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #00d4ff
                );

            color: white;

            text-decoration: none;

            padding: 13px 20px;

            border-radius: 10px;

            font-weight: bold;

            white-space: nowrap;

        }


        .tarjeta {

            background: white;

            padding: 25px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 750px;

        }


        th {

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #6d3bd1
                );

            color: white;

            padding: 15px;

            text-align: left;

        }


        td {

            padding: 14px;

            border-bottom:
                1px solid #eeeeee;

            color: #555;

        }


        tr:hover {

            background: #faf8ff;

        }


        .nombre {

            color: #6d3bd1;

            font-weight: bold;

        }


        .estado {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            background: #e8f7e8;

            color: #32933a;

            font-weight: bold;

            font-size: 13px;

        }


        .acciones {

            display: flex;

            gap: 8px;

        }


        .editar,
        .eliminar {

            padding: 8px 11px;

            border-radius: 8px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        .editar {

            background: #eee8ff;

            color: #6d3bd1;

        }


        .eliminar {

            background: #ffeaea;

            color: #d33;

        }


        .vacio {

            text-align: center;

            padding: 40px;

            color: #777;

        }


        @media (max-width: 650px) {

            .encabezado {

                flex-direction: column;

                align-items: flex-start;

            }

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
                    👩‍🍳 Personal PAE
                </h1>

                <p>
                    Personal encargado de la gestión
                    del Programa de Alimentación Escolar.
                </p>

            </div>


            <a
                href="registrar.php"
                class="boton-agregar"
            >

                + Registrar personal

            </a>


        </div>


        <div class="tarjeta">


            <?php if (
                $resultado->num_rows > 0
            ) { ?>


                <table>


                    <thead>

                        <tr>

                            <th>
                                Documento
                            </th>

                            <th>
                                Nombre completo
                            </th>

                            <th>
                                Teléfono
                            </th>

                            <th>
                                Correo
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $persona =
                        $resultado->fetch_assoc()
                    ) { ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $persona[
                                        "documento"
                                    ]
                                );

                                ?>

                            </td>


                            <td class="nombre">

                                <?php

                                echo htmlspecialchars(

                                    $persona[
                                        "nombres"
                                    ]

                                    . " "

                                    .

                                    $persona[
                                        "apellidos"
                                    ]

                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $persona[
                                        "telefono"
                                    ]

                                    ?: "No registrado"

                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $persona[
                                        "correo"
                                    ]

                                    ?: "No registrado"

                                );

                                ?>

                            </td>


                            <td>

                                <span class="estado">

                                    <?php

                                    echo htmlspecialchars(

                                        $persona[
                                            "estado"
                                        ]

                                    );

                                    ?>

                                </span>

                            </td>


                            <td>


                                <div class="acciones">


                                    <a
                                        href="editar.php?id=<?php
                                            echo $persona[
                                                "id_personal"
                                            ];
                                        ?>"
                                        class="editar"
                                    >

                                        ✏️ Editar

                                    </a>


                                    <a
                                        href="eliminar.php?id=<?php
                                            echo $persona[
                                                "id_personal"
                                            ];
                                        ?>"
                                        class="eliminar"
                                        onclick="return confirm(
                                            '¿Seguro que deseas eliminar este registro?'
                                        );"
                                    >

                                        🗑️ Eliminar

                                    </a>


                                </div>


                            </td>


                        </tr>


                    <?php } ?>


                    </tbody>


                </table>


            <?php } else { ?>


                <div class="vacio">

                    <h2>
                        👩‍🍳 No hay personal registrado
                    </h2>

                    <p>
                        Comienza agregando el personal
                        encargado del PAE.
                    </p>

                </div>


            <?php } ?>


        </div>


    </div>


</main>


</body>

</html>