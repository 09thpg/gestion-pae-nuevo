<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");


/* ==========================================
   OBTENER CURSOS
   ========================================== */

$sql = "
    SELECT
        c.id_curso,
        c.nombre AS curso,
        j.nombre AS jornada
    FROM cursos c
    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada
    ORDER BY c.nombre ASC
";

$cursos = $conexion->query($sql);

if (!$cursos) {
    die("Error al consultar los cursos: " . $conexion->error);
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

    <title>Registrar estudiante - Gestión PAE</title>


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

        }


        .formulario {

            background: white;

            padding: 35px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px rgba(0,0,0,.08);

        }


        .seccion {

            margin-bottom: 30px;

        }


        .seccion h2 {

            color: #6d3bd1;

            font-size: 20px;

            margin-bottom: 20px;

            padding-bottom: 8px;

            border-bottom:
                2px solid #eee;

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


        .campo-completo {

            grid-column: 1 / -1;

        }


        label {

            color: #444;

            font-weight: bold;

            margin-bottom: 7px;

        }


        input,
        select {

            width: 100%;

            padding: 13px;

            border: 1px solid #ddd;

            border-radius: 10px;

            font-size: 16px;

            outline: none;

        }


        input:focus,
        select:focus {

            border-color: #7e57c2;

            box-shadow:
                0 0 0 3px
                rgba(126,87,194,.10);

        }


        .foto-contenedor {

            text-align: center;

            margin-bottom: 25px;

        }


        .vista-foto {

            width: 130px;

            height: 130px;

            border-radius: 50%;

            background: #f0ecfa;

            display: flex;

            justify-content: center;

            align-items: center;

            margin: 0 auto 15px;

            font-size: 55px;

            overflow: hidden;

        }


        .vista-foto img {

            width: 100%;

            height: 100%;

            object-fit: cover;

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

            transition: .3s;

        }


        .boton:hover {

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px rgba(0,0,0,.15);

        }

        
.botones-formulario {
    display: flex;
    gap: 14px;
    margin-top: 10px;
}

.botones-formulario .boton {
    flex: 1;
    width: auto;
}

.boton-cancelar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 15px 22px;
    border-radius: 10px;
    border: 1px solid #ded5ed;
    background: #f5f1fc;
    color: #6335a0;
    text-decoration: none;
    font-size: 16px;
    font-weight: bold;
    transition: .2s ease;
}

.boton-cancelar:hover {
    background: #e9defa;
    transform: translateY(-2px);
}

@media (max-width: 650px) {
    .botones-formulario {
        flex-direction: column;
    }

    .botones-formulario .boton,
    .boton-cancelar {
        width: 100%;
        min-height: 48px;
    }
}


        .jornada {

            background: #f4f0ff;

            color: #6d3bd1;

            padding: 13px;

            border-radius: 10px;

            font-weight: bold;

        }


        .ayuda {

            background: #f4f0ff;

            color: #666;

            padding: 12px;

            border-radius: 10px;

            margin-top: 15px;

            font-size: 14px;

            line-height: 1.5;

        }


        @media (max-width: 650px) {

            .grid {

                grid-template-columns: 1fr;

            }

            .campo-completo {

                grid-column: auto;

            }

            .formulario {

                padding: 25px 20px;

            }

        }

    </style>

</head>


<body>


<?php include("../componentes/sidebar.php"); ?>


<main class="contenido">


    <div class="contenedor">


        <div class="encabezado">

            <h1>
                🎓 Registrar estudiante
            </h1>

            <p>
                Registra la información del estudiante
                en el listado institucional.
            </p>

        </div>


        <form
            action="guardar.php"
            method="POST"
            enctype="multipart/form-data"
            class="formulario"
        >


            <!-- ======================================
                 FOTO
                 ====================================== -->

            <div class="seccion">

                <h2>
                    📸 Foto del estudiante
                </h2>


                <div class="foto-contenedor">

                    <div
                        class="vista-foto"
                        id="vistaFoto"
                    >
                        👤
                    </div>


                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        accept="image/*"
                    >

                </div>

            </div>


            <!-- ======================================
                 INFORMACIÓN PERSONAL
                 ====================================== -->

            <div class="seccion">

                <h2>
                    👤 Información personal
                </h2>


                <div class="grid">


                    <div class="campo">

                        <label for="tipo_documento">
                            Tipo de documento
                        </label>

                        <select
                            id="tipo_documento"
                            name="tipo_documento"
                            required
                        >

                            <option value="">
                                Selecciona
                            </option>

                            <option value="TI">
                                Tarjeta de identidad
                            </option>

                            <option value="CC">
                                Cédula de ciudadanía
                            </option>

                            <option value="CE">
                                Cédula de extranjería
                            </option>

                        </select>

                    </div>


                    <div class="campo">

                        <label for="documento">
                            Número de documento
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

                        <label for="fecha_nacimiento">
                            Fecha de nacimiento
                        </label>

                        <input
                            type="date"
                            id="fecha_nacimiento"
                            name="fecha_nacimiento"
                        >

                    </div>


                    <div class="campo">

                        <label for="sexo">
                            Sexo
                        </label>

                        <select
                            id="sexo"
                            name="sexo"
                        >

                            <option value="">
                                Selecciona
                            </option>

                            <option value="F">
                                Femenino
                            </option>

                            <option value="M">
                                Masculino
                            </option>

                            <option value="Otro">
                                Otro
                            </option>

                        </select>

                    </div>


                    <div class="campo campo-completo">

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


                </div>

            </div>


            <!-- ======================================
                 INFORMACIÓN ACADÉMICA
                 ====================================== -->

            <div class="seccion">

                <h2>
                    🏫 Información académica
                </h2>


                <div class="grid">


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
                                Selecciona el grupo
                            </option>


                            <?php while (
                                $curso =
                                $cursos->fetch_assoc()
                            ) { ?>

                                <option
                                    value="<?php
                                        echo $curso["id_curso"];
                                    ?>"
                                    data-jornada="<?php
                                        echo htmlspecialchars(
                                            $curso["jornada"]
                                        );
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $curso["curso"]
                                        . " — "
                                        . $curso["jornada"]
                                    );
                                    ?>

                                </option>

                            <?php } ?>


                        </select>

                    </div>


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

                    </div>


                </div>

            </div>


            <!-- ======================================
                 PAE
                 ====================================== -->

            <div class="seccion">

                <h2>
                    🍎 Información PAE
                </h2>


                <div class="campo">

                    <label for="beneficiario">
                        ¿Es beneficiario del PAE?
                    </label>


                    <select
                        id="beneficiario"
                        name="beneficiario"
                        required
                    >

                        <option value="">
                            Selecciona
                        </option>

                        <option value="1">
                            Sí
                        </option>

                        <option value="0">
                            No
                        </option>

                    </select>

                </div>

            </div>


            <!-- ======================================
                 DATOS DE ACCESO
                 ====================================== -->

            <div class="seccion">

                <h2>
                    🔐 Datos de acceso
                </h2>


                <div class="grid">


                    <div class="campo">

                        <label for="usuario">
                            Usuario
                        </label>

                        <input
                            type="text"
                            id="usuario"
                            name="usuario"
                            placeholder="Crea un usuario"
                            required
                        >

                    </div>


                    <div class="campo">

                        <label for="password">
                            Contraseña
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Crea una contraseña"
                            required
                        >

                    </div>


                    <div class="campo campo-completo">

                        <label for="confirmar_password">
                            Confirmar contraseña
                        </label>

                        <input
                            type="password"
                            id="confirmar_password"
                            name="confirmar_password"
                            placeholder="Repite la contraseña"
                            required
                        >

                    </div>


                </div>


                <div class="ayuda">

                    🔒 Estos datos serán utilizados por el estudiante
                    para iniciar sesión en el sistema.

                </div>

            </div>


            <!-- ======================================
                 GUARDAR
                 ====================================== -->

            
<div class="botones-formulario">

    <button type="submit" class="boton">
        💾 Guardar estudiante
    </button>

    <a href="listar.php" class="boton-cancelar">
        ✖ Cancelar
    </a>

</div>



        </form>


    </div>


</main>


<script>

const curso =
    document.getElementById("id_curso");

const jornada =
    document.getElementById("jornadaTexto");


curso.addEventListener(
    "change",
    function() {

        const opcion =
            curso.options[curso.selectedIndex];

        const nombreJornada =
            opcion.getAttribute(
                "data-jornada"
            );


        if (nombreJornada) {

            jornada.textContent =
                "🌅 " + nombreJornada;

        } else {

            jornada.textContent =
                "Selecciona un grupo";

        }

    }
);


/* ==========================================
   VISTA PREVIA DE FOTO
   ========================================== */

const foto =
    document.getElementById("foto");

const vistaFoto =
    document.getElementById("vistaFoto");


foto.addEventListener(
    "change",
    function() {

        const archivo =
            foto.files[0];

        if (archivo) {

            const lector =
                new FileReader();


            lector.onload =
                function(e) {

                    vistaFoto.innerHTML =
                        "<img src='"
                        + e.target.result
                        + "'>";

                };


            lector.readAsDataURL(
                archivo
            );

        }

    }
);

</script>


</body>

</html>