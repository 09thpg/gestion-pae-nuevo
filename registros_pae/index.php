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

// Solo el administrador puede registrar entregas

if ($_SESSION["id_rol"] != 1) {

    header("Location: ../estudiantes/perfil.php");

    exit();

}


include("../database/conexion.php");


/* ==========================================
   CURSOS Y JORNADAS
   ========================================== */

$sqlCursos = "
    SELECT
        c.id_curso,
        c.nombre AS curso,
        j.id_jornada,
        j.nombre AS jornada
    FROM cursos c
    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada
    ORDER BY c.nombre ASC
";

$cursos = $conexion->query($sqlCursos);

if (!$cursos) {

    die(
        "Error al consultar cursos: "
        . $conexion->error
    );

}


/* ==========================================
   PERSONAL PAE
   ========================================== */

$sqlPersonal = "
    SELECT
        id_personal,
        nombres,
        apellidos
    FROM personal_pae
    WHERE estado = 'Activo'
    ORDER BY apellidos ASC, nombres ASC
";

$personal = $conexion->query($sqlPersonal);

if (!$personal) {

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
        Registro PAE
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

            max-width: 850px;

            margin: auto;

        }


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

            line-height: 1.5;

        }


        .tarjeta {

            background: white;

            padding: 35px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

        }


        .seccion {

            margin-bottom: 28px;

        }


        .seccion h2 {

            color: #6d3bd1;

            font-size: 20px;

            margin-bottom: 18px;

            padding-bottom: 10px;

            border-bottom:
                2px solid #eeeeee;

        }


        .grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

        }


        .campo {

            display: flex;

            flex-direction: column;

        }


        label {

            font-weight: bold;

            color: #444;

            margin-bottom: 8px;

        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 13px;

            border:
                1px solid #ddd;

            border-radius: 10px;

            font-size: 16px;

            outline: none;

        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color: #7e57c2;

            box-shadow:
                0 0 0 3px
                rgba(126,87,194,.10);

        }


        textarea {

            min-height: 100px;

            resize: vertical;

        }


        .jornada {

            background: #f3efff;

            color: #6d3bd1;

            padding: 13px;

            border-radius: 10px;

            font-weight: bold;

        }


        .informacion {

            background: #f7f7fb;

            border-radius: 12px;

            padding: 18px;

            color: #666;

            line-height: 1.5;

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

        }


        .boton:hover {

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px
                rgba(0,0,0,.12);

        }


        .volver {

            display: block;

            text-align: center;

            margin-top: 18px;

            color: #6d3bd1;

            text-decoration: none;

            font-weight: bold;

        }


        @media (max-width: 650px) {

            .grid {

                grid-template-columns: 1fr;

            }


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


        <!-- ==================================
             ENCABEZADO
             ================================== -->

        <div class="encabezado">

            <h1>
                🍽️ Registro de Alimentación PAE
            </h1>

            <p>
                Registra la entrega de alimentación
                a los estudiantes de un grupo.
            </p>

        </div>


        <div class="tarjeta">


            <form
                action="guardar.php"
                method="POST"
            >


                <!-- ==================================
                     INFORMACIÓN DE LA ENTREGA
                     ================================== -->

                <div class="seccion">


                    <h2>
                        📋 Información de la entrega
                    </h2>


                    <div class="grid">


                        <!-- FECHA -->

                        <div class="campo">

                            <label for="fecha">
                                Fecha
                            </label>

                            <input
                                type="date"
                                id="fecha"
                                name="fecha"
                                value="<?php
                                    echo date("Y-m-d");
                                ?>"
                                required
                            >

                        </div>


                        <!-- PERSONAL PAE -->

                        <div class="campo">

                            <label for="id_personal">
                                Personal PAE responsable
                            </label>

                            <select
                                id="id_personal"
                                name="id_personal"
                                required
                            >

                                <option value="">
                                    Selecciona el personal PAE
                                </option>


                                <?php while (
                                    $persona =
                                    $personal->fetch_assoc()
                                ) { ?>

                                    <option
                                        value="<?php
                                            echo $persona[
                                                "id_personal"
                                            ];
                                        ?>"
                                    >

                                        <?php

                                        echo htmlspecialchars(

                                            $persona["nombres"]

                                            . " "

                                            .

                                            $persona["apellidos"]

                                        );

                                        ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>


                        <!-- CURSO -->

                        <div class="campo">

                            <label for="id_curso">
                                Curso / Grupo
                            </label>

                            <select
                                id="id_curso"
                                name="id_curso"
                                required
                            >

                                <option value="">
                                    Selecciona un grupo
                                </option>


                                <?php while (
                                    $curso =
                                    $cursos->fetch_assoc()
                                ) { ?>

                                    <option
                                        value="<?php
                                            echo $curso[
                                                "id_curso"
                                            ];
                                        ?>"
                                        data-jornada="<?php
                                            echo htmlspecialchars(
                                                $curso[
                                                    "jornada"
                                                ]
                                            );
                                        ?>"
                                        data-id-jornada="<?php
                                            echo $curso[
                                                "id_jornada"
                                            ];
                                        ?>"
                                    >

                                        <?php

                                        echo htmlspecialchars(

                                            $curso["curso"]

                                            . " — "

                                            .

                                            $curso["jornada"]

                                        );

                                        ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>


                        <!-- JORNADA -->

                        <div class="campo">

                            <label>
                                Jornada
                            </label>

                            <div
                                class="jornada"
                                id="jornadaTexto"
                            >
                                Selecciona un grupo
                            </div>

                            <input
                                type="hidden"
                                name="id_jornada"
                                id="idJornada"
                            >

                        </div>


                    </div>

                </div>


                <!-- ==================================
                     OBSERVACIONES
                     ================================== -->

                <div class="seccion">


                    <h2>
                        📝 Observaciones
                    </h2>


                    <div class="campo">

                        <label for="observaciones">
                            Observaciones de la entrega
                        </label>

                        <textarea
                            id="observaciones"
                            name="observaciones"
                            placeholder="Escribe alguna novedad de la entrega..."
                        ></textarea>

                    </div>


                </div>


                <!-- ==================================
                     INFORMACIÓN
                     ================================== -->

                <div class="informacion">

                    🍎 Al continuar, el sistema
                    mostrará los estudiantes
                    pertenecientes al grupo seleccionado
                    para registrar quién recibió
                    la alimentación.

                </div>


                <br>


                <button
                    type="submit"
                    class="boton"
                >

                    👥 Continuar con estudiantes

                </button>


            </form>


            <a
                href="../dashboard.php"
                class="volver"
            >
                ← Volver al inicio
            </a>


        </div>


    </div>


</main>


<script>

/* ==========================================
   MOSTRAR JORNADA AUTOMÁTICAMENTE
   ========================================== */

const curso =
    document.getElementById("id_curso");

const jornadaTexto =
    document.getElementById("jornadaTexto");

const idJornada =
    document.getElementById("idJornada");


curso.addEventListener(
    "change",
    function () {

        const opcion =
            curso.options[
                curso.selectedIndex
            ];


        const jornada =
            opcion.getAttribute(
                "data-jornada"
            );


        const jornadaId =
            opcion.getAttribute(
                "data-id-jornada"
            );


        if (jornada) {

            jornadaTexto.textContent =
                "🌅 " + jornada;

            idJornada.value =
                jornadaId;

        } else {

            jornadaTexto.textContent =
                "Selecciona un grupo";

            idJornada.value = "";

        }

    }
);

</script>


</body>

</html>