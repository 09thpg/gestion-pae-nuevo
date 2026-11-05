<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["id_rol"] != 1) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";

$sql_cursos = "
    SELECT 
        c.id_curso,
        c.nombre AS nombre_curso,
        j.nombre AS nombre_jornada
    FROM cursos c
    INNER JOIN jornadas j ON c.id_jornada = j.id_jornada
    ORDER BY c.nombre ASC
";

$resultado_cursos = $conexion->query($sql_cursos);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrar profesor</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f1fa;
        }

        .contenido {
            margin-left: 250px;
            padding: 40px;
        }

        .contenedor-formulario {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        h1 {
            color: #5b2c83;
            margin-bottom: 10px;
        }

        .descripcion {
            color: #666;
            margin-bottom: 25px;
        }

        .grupo-formulario {
            margin-bottom: 18px;
        }

        .grupo-formulario label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #444;
        }

        .grupo-formulario input,
        .grupo-formulario select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 15px;
        }

        .fila {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .seccion {
            margin-top: 30px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f7f2fc;
            border-radius: 14px;
        }

        .seccion h2 {
            margin-top: 0;
            color: #5b2c83;
            font-size: 20px;
        }

        .opciones {
            display: flex;
            gap: 25px;
            margin-top: 10px;
        }

        .opciones label {
            font-weight: normal;
        }

        .grupo-director {
            display: none;
            margin-top: 18px;
        }

        .botones {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            border: none;
            padding: 13px 22px;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-size: 15px;
        }

        .btn-guardar {
            background: #6f42c1;
            color: white;
        }

        .btn-guardar:hover {
            background: #59339d;
        }

        .btn-cancelar {
            background: #ddd;
            color: #333;
        }

        @media (max-width: 768px) {

            .contenido {
                margin-left: 0;
                padding: 20px;
            }

            .fila {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<?php include "../componentes/sidebar.php"; ?>


<div class="contenido">

    <div class="contenedor-formulario">

        <h1>👨‍🏫 Registrar profesor</h1>

        <p class="descripcion">
            Registra los datos del profesor y crea su cuenta de acceso.
        </p>


        <form action="guardar.php" method="POST">


            <!-- DATOS DEL PROFESOR -->

            <div class="seccion">

                <h2>📋 Datos personales</h2>

                <div class="grupo-formulario">

                    <label>Tipo de documento</label>

                    <select name="tipo_documento" required>

                        <option value="">Seleccione</option>
                        <option value="CC">Cédula de ciudadanía</option>
                        <option value="CE">Cédula de extranjería</option>

                    </select>

                </div>


                <div class="grupo-formulario">

                    <label>Documento</label>

                    <input
                        type="text"
                        name="documento"
                        required
                    >

                </div>


                <div class="fila">

                    <div class="grupo-formulario">

                        <label>Nombres</label>

                        <input
                            type="text"
                            name="nombres"
                            required
                        >

                    </div>


                    <div class="grupo-formulario">

                        <label>Apellidos</label>

                        <input
                            type="text"
                            name="apellidos"
                            required
                        >

                    </div>

                </div>


                <div class="fila">

                    <div class="grupo-formulario">

                        <label>Correo</label>

                        <input
                            type="email"
                            name="correo"
                            required
                        >

                    </div>


                    <div class="grupo-formulario">

                        <label>Teléfono</label>

                        <input
                            type="text"
                            name="telefono"
                            required
                        >

                    </div>

                </div>

            </div>


            <!-- CUENTA -->

            <div class="seccion">

                <h2>🔐 Cuenta de acceso</h2>

                <div class="grupo-formulario">

                    <label>Usuario</label>

                    <input
                        type="text"
                        name="usuario"
                        required
                    >

                </div>


                <div class="fila">

                    <div class="grupo-formulario">

                        <label>Contraseña</label>

                        <input
                            type="password"
                            name="password"
                            required
                        >

                    </div>


                    <div class="grupo-formulario">

                        <label>Confirmar contraseña</label>

                        <input
                            type="password"
                            name="confirmar_password"
                            required
                        >

                    </div>

                </div>

            </div>


<!-- ESTADO DEL PROFESOR -->
<div class="seccion">

    <h2>📌 Estado del profesor</h2>

    <div class="grupo-formulario">
        <label for="estado">Estado</label>

        <select name="estado" id="estado" required>
            <option value="Activo" selected>Activo</option>
            <option value="Inactivo">Inactivo</option>
        </select>
    </div>

</div>


            <!-- DIRECCIÓN DE GRUPO -->

            <div class="seccion">

                <h2>🏫 Dirección de grupo</h2>

                <p>
                    ¿Este profesor es director de algún grupo?
                </p>


                <div class="opciones">

                    <label>

                        <input
                            type="radio"
                            name="es_director"
                            value="0"
                            checked
                            onclick="mostrarGrupo(false)"
                        >

                        No

                    </label>


                    <label>

                        <input
                            type="radio"
                            name="es_director"
                            value="1"
                            onclick="mostrarGrupo(true)"
                        >

                        Sí

                    </label>

                </div>


                <div
                    class="grupo-director"
                    id="grupoDirector"
                >

                    <div class="grupo-formulario">

                        <label>
                            Seleccione el grupo que dirige
                        </label>

                        <select
                            name="id_curso_director"
                            id="id_curso_director"
                        >

                            <option value="">
                                Seleccione un grupo
                            </option>

                            <?php while ($curso = $resultado_cursos->fetch_assoc()) { ?>

                                <option value="<?= $curso["id_curso"] ?>">

                                    <?= htmlspecialchars($curso["nombre_curso"]) ?>
                                    -
                                    <?= htmlspecialchars($curso["nombre_jornada"]) ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                </div>

            </div>


            <!-- BOTONES -->

            <div class="botones">

                <button
                    type="submit"
                    class="btn btn-guardar"
                >
                    💾 Registrar profesor
                </button>


                <a
                    href="listar.php"
                    class="btn btn-cancelar"
                >
                    ↩ Cancelar
                </a>

            </div>


        </form>

    </div>

</div>


<script>

function mostrarGrupo(mostrar) {

    const grupo = document.getElementById("grupoDirector");

    const select = document.getElementById("id_curso_director");


    if (mostrar) {

        grupo.style.display = "block";

        select.required = true;

    } else {

        grupo.style.display = "none";

        select.required = false;

        select.value = "";

    }

}

</script>


</body>

</html>