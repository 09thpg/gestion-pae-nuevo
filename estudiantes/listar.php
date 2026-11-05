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

// Solo el administrador puede ver el listado

if ($_SESSION["id_rol"] != 1) {

    header("Location: perfil.php");

    exit();

}


include("../database/conexion.php");


/* ==========================================
   OBTENER ESTUDIANTES ORGANIZADOS
   ========================================== */

$sql = "
    SELECT
        e.id_estudiante,
        e.documento,
        e.tipo_documento,
        e.nombres,
        e.apellidos,
        e.foto,
        e.beneficiario,
        e.estado,
        c.id_curso,
        c.nombre AS curso,
        j.nombre AS jornada

    FROM estudiantes e

    LEFT JOIN cursos c
        ON e.id_curso = c.id_curso

    LEFT JOIN jornadas j
        ON c.id_jornada = j.id_jornada


    ORDER BY
        c.nombre ASC,
        e.apellidos ASC,
        e.nombres ASC
";


$resultado = $conexion->query($sql);


if (!$resultado) {

    die(
        "Error al consultar estudiantes: "
        . $conexion->error
    );

}


/* ==========================================
   AGRUPAR ESTUDIANTES POR CURSO
   ========================================== */

$grupos = [];


while ($estudiante = $resultado->fetch_assoc()) {

    $curso = $estudiante["curso"] ?? "Sin curso";


    if (!isset($grupos[$curso])) {

        $grupos[$curso] = [

            "jornada" =>
                $estudiante["jornada"]
                ?? "Sin jornada",

            "estudiantes" => []

        ];

    }


    $grupos[$curso]["estudiantes"][] =
        $estudiante;

}


/* ==========================================
   TOTAL
   ========================================== */

$totalEstudiantes = 0;


foreach ($grupos as $grupo) {

    $totalEstudiantes +=
        count($grupo["estudiantes"]);

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

    <title>Estudiantes - Gestión PAE</title>


    <style>


        /* ==========================================
           GENERAL
           ========================================== */

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


        /* ==========================================
           CONTENIDO
           ========================================== */

        .contenido {

            min-height: 100vh;

            width: 100%;

            padding: 90px 30px 50px;

        }


        .contenedor {

            max-width: 1100px;

            margin: auto;

        }


        /* ==========================================
           ENCABEZADO
           ========================================== */

        .encabezado {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 30px;

        }


        .encabezado h1 {

            color: #6d3bd1;

            font-size: 32px;

            margin-bottom: 8px;

        }


        .encabezado p {

            color: #666;

        }


        /* ==========================================
           BOTÓN
           ========================================== */

        .boton {

            display: inline-block;

            padding: 13px 20px;

            background:
                linear-gradient(
                    90deg,
                    #8c52ff,
                    #00d4ff
                );

            color: white;

            text-decoration: none;

            border-radius: 10px;

            font-weight: bold;

            white-space: nowrap;

        }


        /* ==========================================
           TOTAL
           ========================================== */

        .total {

            background: white;

            padding: 15px 20px;

            border-radius: 15px;

            box-shadow:
                0 7px 20px rgba(0,0,0,.08);

            margin-bottom: 25px;

            color: #666;

        }


        .total strong {

            color: #6d3bd1;

            font-size: 22px;

        }


        /* ==========================================
           GRUPO
           ========================================== */

        .grupo {

            background: white;

            border-radius: 20px;

            padding: 25px;

            margin-bottom: 30px;

            box-shadow:
                0 10px 25px rgba(0,0,0,.08);

        }


        .grupo-titulo {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            margin-bottom: 20px;

            padding-bottom: 15px;

            border-bottom:
                2px solid #eeeeee;

        }


        .grupo-titulo h2 {

            color: #6d3bd1;

            font-size: 24px;

        }


        .jornada {

            background: #f0eaff;

            color: #6d3bd1;

            padding: 8px 15px;

            border-radius: 20px;

            font-weight: bold;

        }


        .cantidad {

            color: #777;

            font-size: 14px;

            margin-bottom: 15px;

        }


        /* ==========================================
           TABLA
           ========================================== */

        .tabla {

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th {

            background: #7e57c2;

            color: white;

            padding: 13px;

            text-align: center;

        }


        td {

            padding: 12px;

            text-align: center;

            border-bottom:
                1px solid #eeeeee;

            color: #555;

        }


        tr:hover {

            background: #faf8ff;

        }


        /* ==========================================
           FOTO
           ========================================== */

        .foto {

            width: 55px;

            height: 55px;

            border-radius: 50%;

            object-fit: cover;

        }


        .sin-foto {

            width: 55px;

            height: 55px;

            border-radius: 50%;

            background: #eee;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            font-size: 25px;

        }


        /* ==========================================
           BENEFICIARIO
           ========================================== */

        .si {

            color: #2e7d32;

            font-weight: bold;

        }


        .no {

            color: #c62828;

            font-weight: bold;

        }


        /* ==========================================
           NOMBRE DEL ESTUDIANTE
           ========================================== */

        .nombre-estudiante {

            color: #6d3bd1;

            text-decoration: none;

            font-weight: bold;

        }


        .nombre-estudiante:hover {

            text-decoration: underline;

        }


        /* ==========================================
           SIN ESTUDIANTES
           ========================================== */

        .vacio {

            background: white;

            padding: 50px;

            border-radius: 20px;

            text-align: center;

            box-shadow:
                0 10px 25px rgba(0,0,0,.08);

        }


        .vacio h2 {

            color: #6d3bd1;

            margin-bottom: 10px;

        }


        .vacio p {

            color: #777;

        }


        /* ==========================================
           CELULAR
           ========================================== */

        @media (max-width: 700px) {

            .encabezado {

                flex-direction: column;

                align-items: flex-start;

            }


            .grupo-titulo {

                flex-direction: column;

                align-items: flex-start;

                gap: 10px;

            }

        }

        
/* ACCIONES DE ESTUDIANTES */
.acciones-estudiante {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    min-width: 150px;
}

.boton-accion {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 8px 11px;
    border: none;
    border-radius: 9px;
    text-decoration: none;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s ease;
}

.boton-editar {
    background: #eee6ff;
    color: #6535b5;
}

.boton-editar:hover {
    background: #6d3bd1;
    color: white;
}

.boton-eliminar {
    background: #ffe8eb;
    color: #c62828;
}

.boton-eliminar:hover {
    background: #c62828;
    color: white;
}

.form-eliminar {
    display: inline;
    margin: 0;
}

@media (max-width: 700px) {
    .acciones-estudiante {
        min-width: 150px;
        flex-direction: column;
    }

    .boton-accion {
        width: 100%;
        max-width: 130px;
    }
}

.boton-eliminar {
    display: inline-block;
    padding: 8px 12px;
    border: none;
    border-radius: 10px;
    background: #ffe8e8;
    color: #d93636;
    font-size: 14px;
    text-decoration: none;
    cursor: pointer;
    font-family: Arial, sans-serif;
}

.boton-eliminar:hover {
    background: #ffd6d6;
}


/* GRUPOS DESPLEGABLES */
.grupo {
    overflow: hidden;
}

.grupo-titulo {
    cursor: pointer;
    list-style: none;
    user-select: none;
    transition: background 0.2s ease;
}

.grupo-titulo::-webkit-details-marker {
    display: none;
}

.grupo-titulo h2::before {
    content: "▶";
    display: inline-block;
    margin-right: 12px;
    font-size: 15px;
    color: #8c52ff;
    transition: transform 0.2s ease;
}

.grupo[open] > .grupo-titulo h2::before {
    content: "▼";
}

.grupo-titulo:hover h2 {
    color: #8c52ff;
}

.grupo > .cantidad,
.grupo > .tabla {
    animation: aparecer 0.2s ease;
}

@keyframes aparecer {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}


    </style>

</head>


<body>


<?php include("../componentes/sidebar.php"); ?>


<main class="contenido">


<div class="contenedor">


    <!-- ==========================================
         ENCABEZADO
         ========================================== -->

    <div class="encabezado">


        <div>

            <h1>
                🎓 Listado institucional
            </h1>


            <p>
                Estudiantes organizados por
                curso y jornada.
            </p>

        </div>


        <a
            href="registrar.php"
            class="boton"
        >
            + Registrar estudiante
        </a>


    </div>


    <!-- ==========================================
         TOTAL
         ========================================== -->

    <div class="total">

        Total de estudiantes:

        <strong>

            <?php
            echo $totalEstudiantes;
            ?>

        </strong>

    </div>


    <!-- ==========================================
         GRUPOS
         ========================================== -->

    <?php if (count($grupos) > 0) { ?>


        <?php foreach (
            $grupos as $nombreCurso => $grupo
        ) { ?>


            <details class="grupo">


                <summary class="grupo-titulo">


                    <h2>

                        🏫

                        <?php

                        echo htmlspecialchars(
                            $nombreCurso
                        );

                        ?>

                    </h2>


                    <div class="jornada">

                        🌅

                        <?php

                        echo htmlspecialchars(
                            $grupo["jornada"]
                        );

                        ?>

                    </div>


                </summary>


                <p class="cantidad">

                    👥

                    <?php

                    echo count(
                        $grupo["estudiantes"]
                    );

                    ?>

                    estudiante(s) en este grupo.

                </p>


                <div class="tabla">


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    Foto
                                </th>

                                <th>
                                    Documento
                                </th>

                                <th>
                                    Estudiante
                                </th>

                                <th>
                                    Beneficiario PAE
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>Acciones</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach (
                            $grupo["estudiantes"]
                            as $estudiante
                        ) { ?>


                            <tr>


                                <!-- FOTO -->

                                <td>

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
                                            alt="Foto del estudiante"
                                        >


                                    <?php } else { ?>


                                        <div class="sin-foto">

                                            👤

                                        </div>


                                    <?php } ?>

                                </td>


                                <!-- DOCUMENTO -->

                                <td>

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

                                </td>


                                <!-- NOMBRE -->

                                <td>


                                    <a
                                        href="perfil.php?id=<?php
                                            echo $estudiante[
                                                "id_estudiante"
                                            ];
                                        ?>"
                                        class="nombre-estudiante"
                                    >

                                        <?php

                                        echo htmlspecialchars(

                                            $estudiante["nombres"]

                                            . " "

                                            .

                                            $estudiante["apellidos"]

                                        );

                                        ?>

                                    </a>


                                </td>


                                <!-- BENEFICIARIO -->

                                <td>


                                    <?php if (
                                        $estudiante[
                                            "beneficiario"
                                        ]
                                    ) { ?>


                                        <span class="si">

                                            ✅ Sí

                                        </span>


                                    <?php } else { ?>


                                        <span class="no">

                                            ❌ No

                                        </span>


                                    <?php } ?>


                                </td>


                                
<!-- ESTADO -->
<td>
    <?php if (($estudiante["estado"] ?? "") === "Activo") { ?>
        <span class="si">● Activo</span>
    <?php } else { ?>
        <span class="no">● Inactivo</span>
    <?php } ?>
</td>


                                    
<td>
    <div class="acciones-estudiante">

        <a
            href="editar.php?id=<?php
                echo (int) $estudiante['id_estudiante'];
            ?>"
            class="boton-accion boton-editar"
        >
            ✏️ Editar
        </a>

        
<form
    action="eliminar.php"
    method="POST"
    onsubmit="return confirm('¿Seguro que deseas eliminar definitivamente a este estudiante y sus registros de entregas?');"
    style="display:inline;"
>
    <input
        type="hidden"
        name="id_estudiante"
        value="<?= (int) $estudiante['id_estudiante'] ?>"
    >

    <button type="submit" class="boton-eliminar">
        🗑️ Eliminar
    </button>
</form>


    </div>
</td>


                                </td>


                            </tr>


                        <?php } ?>


                        </tbody>


                    </table>


                </div>


            </details>


        <?php } ?>


    <?php } else { ?>


        <div class="vacio">


            <h2>

                🎓 No hay estudiantes registrados

            </h2>


            <p>

                Registra el primer estudiante
                para comenzar el listado institucional.

            </p>


        </div>


    <?php } ?>


</div>


</main>


</body>

</html>