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

require_once "../database/conexion.php";

$id_profesor = $_GET["id"] ?? "";

if ($id_profesor === "" || !is_numeric($id_profesor)) {
    header("Location: listar.php");
    exit();
}

try {

    /* ==========================================
       BUSCAR PROFESOR
       ========================================== */

    $sql = "
        SELECT
            id_profesor,
            documento,
            nombres,
            apellidos,
            correo,
            telefono,
            estado,
            es_director,
            id_curso_director
        FROM profesores
        WHERE id_profesor = ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "i",
        $id_profesor
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        header("Location: listar.php");
        exit();
    }

    $profesor = $resultado->fetch_assoc();

    $stmt->close();


    /* ==========================================
       OBTENER GRUPOS
       ========================================== */

    $sql = "
        SELECT
            c.id_curso,
            c.nombre,
            j.nombre AS jornada
        FROM cursos c
        INNER JOIN jornadas j
            ON c.id_jornada = j.id_jornada
        ORDER BY c.nombre ASC
    ";

    $resultado = $conexion->query($sql);

    $cursos = [];

    if ($resultado) {

        while ($fila = $resultado->fetch_assoc()) {
            $cursos[] = $fila;
        }

    }

} catch (Exception $e) {

    echo "❌ Error: " .
        htmlspecialchars($e->getMessage());

    exit();

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

    <title>Editar profesor</title>


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

            padding: 100px 25px 50px;

        }


        .contenedor {

            max-width: 650px;

            margin: auto;

        }


        .tarjeta {

            background: white;

            padding: 35px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

        }


        h1 {

            color: #6d3bd1;

            margin-bottom: 10px;

        }


        .descripcion {

            color: #666;

            margin-bottom: 25px;

            line-height: 1.5;

        }


        label {

            display: block;

            color: #444;

            font-weight: bold;

            margin-bottom: 8px;

        }


        input,
        select {

            width: 100%;

            padding: 14px;

            border: 1px solid #ddd;

            border-radius: 10px;

            font-size: 16px;

            margin-bottom: 18px;

            background: white;

        }


        input:focus,
        select:focus {

            outline: none;

            border-color: #7e57c2;

        }


        .grupo-director {

            background: #f7f3ff;

            padding: 20px;

            border-radius: 14px;

            margin-bottom: 20px;

        }


        .grupo-director h3 {

            color: #6335bd;

            margin-bottom: 8px;

        }


        .grupo-director p {

            color: #666;

            font-size: 14px;

            margin-bottom: 18px;

        }


        .opciones-director {

            display: flex;

            gap: 20px;

            margin-bottom: 18px;

        }


        .opcion {

            display: flex;

            align-items: center;

            gap: 8px;

            color: #444;

            font-weight: normal;

            cursor: pointer;

        }


        .opcion input {

            width: auto;

            margin: 0;

        }


        #selectorGrupo {

            display: none;

        }


        .botones {

            display: flex;

            gap: 10px;

        }


        button {

            flex: 1;

            padding: 14px;

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


        .volver {

            flex: 1;

            padding: 14px;

            border-radius: 10px;

            background: #eee8ff;

            color: #6335bd;

            text-decoration: none;

            text-align: center;

            font-weight: bold;

        }

    </style>

</head>


<body>


<?php include("../componentes/sidebar.php"); ?>


<main class="contenido">


    <div class="contenedor">


        <div class="tarjeta">


            <h1>
                ✏️ Editar profesor
            </h1>


            <p class="descripcion">

                Modifica la información del profesor
                y su dirección de grupo.

            </p>


            <form
                action="actualizar.php"
                method="POST"
            >


                <input
                    type="hidden"
                    name="id_profesor"
                    value="<?php echo $profesor["id_profesor"]; ?>"
                >


                <label for="documento">
                    Documento
                </label>

                <input
                    type="text"
                    id="documento"
                    name="documento"
                    value="<?php echo htmlspecialchars($profesor["documento"]); ?>"
                    required
                >


                <label for="nombres">
                    Nombres
                </label>

                <input
                    type="text"
                    id="nombres"
                    name="nombres"
                    value="<?php echo htmlspecialchars($profesor["nombres"]); ?>"
                    required
                >


                <label for="apellidos">
                    Apellidos
                </label>

                <input
                    type="text"
                    id="apellidos"
                    name="apellidos"
                    value="<?php echo htmlspecialchars($profesor["apellidos"]); ?>"
                    required
                >


                <label for="correo">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    value="<?php echo htmlspecialchars($profesor["correo"]); ?>"
                >


                <label for="telefono">
                    Teléfono
                </label>

                <input
                    type="text"
                    id="telefono"
                    name="telefono"
                    value="<?php echo htmlspecialchars($profesor["telefono"]); ?>"
                >

                
<!-- ESTADO DEL PROFESOR -->
<label for="estado">Estado del profesor</label>

<select name="estado" id="estado" required>
    <option
        value="Activo"
        <?= $profesor["estado"] === "Activo" ? "selected" : "" ?>
    >
        Activo
    </option>

    <option
        value="Inactivo"
        <?= $profesor["estado"] === "Inactivo" ? "selected" : "" ?>
    >
        Inactivo
    </option>
</select>



                <!-- DIRECTOR DE GRUPO -->

                <div class="grupo-director">

                    <h3>
                        🏫 Dirección de grupo
                    </h3>

                    <p>
                        Puedes cambiar si el profesor
                        es director y seleccionar el grupo
                        que dirige.
                    </p>


                    <div class="opciones-director">

                        <label class="opcion">

                            <input
                                type="radio"
                                name="es_director"
                                value="0"
                                <?php
                                if ($profesor["es_director"] == 0) {
                                    echo "checked";
                                }
                                ?>
                                onchange="mostrarGrupo()"
                            >

                            ❌ No

                        </label>


                        <label class="opcion">

                            <input
                                type="radio"
                                name="es_director"
                                value="1"
                                <?php
                                if ($profesor["es_director"] == 1) {
                                    echo "checked";
                                }
                                ?>
                                onchange="mostrarGrupo()"
                            >

                            ✅ Sí

                        </label>

                    </div>


                    <div id="selectorGrupo">

                        <label for="id_curso_director">

                            🏫 Grupo que dirige

                        </label>


                        <select
                            name="id_curso_director"
                            id="id_curso_director"
                        >

                            <option value="">
                                Selecciona un grupo
                            </option>


                            <?php foreach (
                                $cursos
                                as $curso
                            ): ?>

                                <option
                                    value="<?php echo $curso["id_curso"]; ?>"
                                    <?php

                                    if (
                                        $profesor[
                                            "id_curso_director"
                                        ] ==
                                        $curso[
                                            "id_curso"
                                        ]
                                    ) {

                                        echo "selected";

                                    }

                                    ?>
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $curso["nombre"]
                                    );

                                    ?>

                                    -
                                    <?php

                                    echo htmlspecialchars(
                                        $curso["jornada"]
                                    );

                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>


                <div class="botones">

                    <a
                        href="listar.php"
                        class="volver"
                    >
                        ↩️ Cancelar
                    </a>


                    <button type="submit">

                        💾 Guardar cambios

                    </button>

                </div>


            </form>


        </div>


    </div>


</main>


<script>

function mostrarGrupo() {

    const esDirector =
        document.querySelector(
            'input[name="es_director"]:checked'
        ).value;

    const selectorGrupo =
        document.getElementById(
            "selectorGrupo"
        );

    const grupo =
        document.getElementById(
            "id_curso_director"
        );


    if (esDirector === "1") {

        selectorGrupo.style.display =
            "block";

        grupo.required = true;

    } else {

        selectorGrupo.style.display =
            "none";

        grupo.required = false;

        grupo.value = "";

    }

}


// Mostrar correctamente el grupo
// al abrir la página

mostrarGrupo();

</script>


</body>

</html>