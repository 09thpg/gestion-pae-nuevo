<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");


/* ==========================================
   DATOS RECIBIDOS
   ========================================== */

$id_curso = isset($_GET["id_curso"])
    ? intval($_GET["id_curso"])
    : 0;

$id_profesor = isset($_GET["id_profesor"])
    ? intval($_GET["id_profesor"])
    : 0;


/* ==========================================
   VALIDAR SELECCIÓN
   ========================================== */

if ($id_curso <= 0 || $id_profesor <= 0) {

    header("Location: seleccionar_registro.php");
    exit();

}


/* ==========================================
   OBTENER PERSONAL PAE
   ========================================== */

$id_usuario = $_SESSION["id_usuario"];

$sqlPersonal = "
    SELECT id_personal
    FROM personal_pae
    WHERE id_usuario = ?
    LIMIT 1
";

$stmtPersonal = $conexion->prepare($sqlPersonal);

$stmtPersonal->bind_param(
    "i",
    $id_usuario
);

$stmtPersonal->execute();

$resultadoPersonal = $stmtPersonal->get_result();

$personal = $resultadoPersonal->fetch_assoc();


if (!$personal) {

    die("No se encontró el Personal PAE asociado a esta cuenta.");

}

$id_personal = $personal["id_personal"];


/* ==========================================
   CONSULTAR CURSO
   ========================================== */

$sqlCurso = "
    SELECT
        c.id_curso,
        c.nombre AS curso,
        c.id_jornada,
        j.nombre AS jornada
    FROM cursos c

    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada

    WHERE c.id_curso = ?

    LIMIT 1
";

$stmtCurso = $conexion->prepare($sqlCurso);

$stmtCurso->bind_param(
    "i",
    $id_curso
);

$stmtCurso->execute();

$resultadoCurso = $stmtCurso->get_result();

$curso = $resultadoCurso->fetch_assoc();


if (!$curso) {

    die("El curso seleccionado no existe.");

}


/* ==========================================
   CONSULTAR PROFESOR
   ========================================== */

$sqlProfesor = "
    SELECT
        id_profesor,
        nombres,
        apellidos
    FROM profesores
    WHERE id_profesor = ?
    AND estado = 'Activo'
    LIMIT 1
";

$stmtProfesor = $conexion->prepare($sqlProfesor);

$stmtProfesor->bind_param(
    "i",
    $id_profesor
);

$stmtProfesor->execute();

$resultadoProfesor = $stmtProfesor->get_result();

$profesor = $resultadoProfesor->fetch_assoc();


if (!$profesor) {

    die(
        "El profesor seleccionado no existe "
        . "o está inactivo."
    );

}


/* ==========================================
   FECHA ACTUAL
   ========================================== */

$fecha = date("Y-m-d");


/* ==========================================
   BUSCAR GRUPO EN PROCESO
   ========================================== */

$sqlEntregaActual = "
    SELECT
        id_entrega,
        estado
    FROM entregas_pae

    WHERE fecha = ?
    AND id_curso = ?
    AND id_profesor = ?
    AND id_personal = ?

    ORDER BY id_entrega DESC

    LIMIT 1
";

$stmtEntregaActual = $conexion->prepare($sqlEntregaActual);

$stmtEntregaActual->bind_param(
    "siii",
    $fecha,
    $id_curso,
    $id_profesor,
    $id_personal
);

$stmtEntregaActual->execute();

$resultadoEntregaActual =
    $stmtEntregaActual->get_result();

$entregaActual =
    $resultadoEntregaActual->fetch_assoc();


$id_entrega = 0;
$estadoEntrega = "";


if ($entregaActual) {

    $id_entrega =
        $entregaActual["id_entrega"];

    $estadoEntrega =
        $entregaActual["estado"];

}


/* ==========================================
   MENSAJES
   ========================================== */

$mensaje = "";
$tipoMensaje = "";


/* ==========================================
   REGISTRAR REFRIGERIO
   ========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $documento = trim(
        $_POST["documento"] ?? ""
    );


    /* ======================================
       VERIFICAR SI EL GRUPO YA TERMINÓ
       ====================================== */

    if ($estadoEntrega === "Terminado") {

        $mensaje =
            "⚠️ Este grupo ya fue terminado. "
            . "No se pueden registrar más estudiantes.";

        $tipoMensaje = "error";

    } elseif ($documento === "") {

        $mensaje =
            "Por favor, escribe el documento del estudiante.";

        $tipoMensaje = "error";

    } else {

        try {

            /* ==============================
               BUSCAR ESTUDIANTE
               ============================== */

            $sqlEstudiante = "
                SELECT
                    id_estudiante,
                    documento,
                    nombres,
                    apellidos

                FROM estudiantes

                WHERE documento = ?

                AND id_curso = ?

                AND estado = 'Activo'

                LIMIT 1
            ";

            $stmtEstudiante =
                $conexion->prepare($sqlEstudiante);

            $stmtEstudiante->bind_param(
                "si",
                $documento,
                $id_curso
            );

            $stmtEstudiante->execute();

            $resultadoEstudiante =
                $stmtEstudiante->get_result();

            $estudiante =
                $resultadoEstudiante->fetch_assoc();


            /* ==============================
               VALIDAR ESTUDIANTE
               ============================== */

            if (!$estudiante) {

                $mensaje =
                    "❌ No se encontró un estudiante activo "
                    . "con ese documento en este curso.";

                $tipoMensaje = "error";

            } else {


                /* ==============================
                   SI NO EXISTE ENTREGA,
                   CREARLA
                   ============================== */

                if ($id_entrega == 0) {

                    $conexion->begin_transaction();


                    $observacionEntrega =
                        "Grupo en proceso.";


                    $estadoInicial =
                        "En proceso";


                    $sqlCrearEntrega = "
                        INSERT INTO entregas_pae
                        (
                            fecha,
                            id_jornada,
                            id_curso,
                            id_personal,
                            id_profesor,
                            estado,
                            observaciones
                        )

                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?
                        )
                    ";

                    $stmtCrearEntrega =
                        $conexion->prepare($sqlCrearEntrega);


                    $stmtCrearEntrega->bind_param(
                        "siiiiss",
                        $fecha,
                        $curso["id_jornada"],
                        $id_curso,
                        $id_personal,
                        $id_profesor,
                        $estadoInicial,
                        $observacionEntrega
                    );


                    $stmtCrearEntrega->execute();


                    $id_entrega =
                        $conexion->insert_id;


                    $estadoEntrega =
                        "En proceso";


                    $conexion->commit();

                }


                /* ==============================
                   VERIFICAR SI YA ESTÁ REGISTRADO
                   ============================== */

                $sqlDuplicado = "
                    SELECT
                        id_detalle

                    FROM detalle_entrega

                    WHERE id_entrega = ?

                    AND id_estudiante = ?

                    LIMIT 1
                ";

                $stmtDuplicado =
                    $conexion->prepare($sqlDuplicado);

                $stmtDuplicado->bind_param(
                    "ii",
                    $id_entrega,
                    $estudiante["id_estudiante"]
                );

                $stmtDuplicado->execute();

                $resultadoDuplicado =
                    $stmtDuplicado->get_result();


                if ($resultadoDuplicado->num_rows > 0) {

                    $mensaje =
                        "⚠️ Este estudiante ya está "
                        . "registrado en este grupo.";

                    $tipoMensaje = "error";

                } else {


                    /* ==============================
                       GUARDAR DETALLE
                       ============================== */

                    $recibido = 1;

                    $observacionDetalle =
                        "Refrigerio registrado.";


                    $sqlDetalle = "
                        INSERT INTO detalle_entrega
                        (
                            id_entrega,
                            id_estudiante,
                            recibido,
                            observacion
                        )

                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?
                        )
                    ";


                    $stmtDetalle =
                        $conexion->prepare($sqlDetalle);


                    $stmtDetalle->bind_param(
                        "iiis",
                        $id_entrega,
                        $estudiante["id_estudiante"],
                        $recibido,
                        $observacionDetalle
                    );


                    $stmtDetalle->execute();


                    $mensaje =
                        "✅ Refrigerio registrado correctamente "
                        . "para "
                        . $estudiante["nombres"]
                        . " "
                        . $estudiante["apellidos"]
                        . ".";

                    $tipoMensaje = "exito";

                }

            }


        } catch (Exception $e) {

            try {

                $conexion->rollback();

            } catch (Exception $error) {

                // Evitar errores adicionales.

            }


            $mensaje =
                "❌ Ocurrió un error al registrar "
                . "el refrigerio.";

            $tipoMensaje = "error";

        }

    }

}


/* ==========================================
   ACTUALIZAR INFORMACIÓN DE LA ENTREGA
   ========================================== */

if ($id_entrega > 0) {

    $sqlEstado = "
        SELECT estado
        FROM entregas_pae
        WHERE id_entrega = ?
        LIMIT 1
    ";

    $stmtEstado =
        $conexion->prepare($sqlEstado);

    $stmtEstado->bind_param(
        "i",
        $id_entrega
    );

    $stmtEstado->execute();

    $resultadoEstado =
        $stmtEstado->get_result();

    $entregaEstado =
        $resultadoEstado->fetch_assoc();

    if ($entregaEstado) {

        $estadoEntrega =
            $entregaEstado["estado"];

    }

}


/* ==========================================
   CONTAR ESTUDIANTES REGISTRADOS
   ========================================== */

$totalRegistrados = 0;

if ($id_entrega > 0) {

    $sqlTotal = "
        SELECT COUNT(*) AS total
        FROM detalle_entrega
        WHERE id_entrega = ?
    ";

    $stmtTotal =
        $conexion->prepare($sqlTotal);

    $stmtTotal->bind_param(
        "i",
        $id_entrega
    );

    $stmtTotal->execute();

    $resultadoTotal =
        $stmtTotal->get_result();

    $total =
        $resultadoTotal->fetch_assoc();

    $totalRegistrados =
        $total["total"];

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
        Registrar refrigerio
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
                60px 25px 50px;

            display: flex;

            justify-content: center;

            align-items: flex-start;

        }


        .contenedor {

            width: 100%;

            max-width: 650px;

        }


        .encabezado {

            text-align: center;

            margin-bottom: 25px;

        }


        .icono {

            font-size: 60px;

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


        .seleccion {

            background: white;

            padding: 18px 22px;

            border-radius: 18px;

            margin-bottom: 20px;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,.06);

        }


        .seleccion h2 {

            color: #6d3bd1;

            font-size: 18px;

            margin-bottom: 12px;

        }


        .datos {

            display: flex;

            flex-wrap: wrap;

            gap: 10px;

        }


        .dato {

            background: #f7f4ff;

            padding: 9px 13px;

            border-radius: 10px;

            color: #555;

            font-size: 14px;

        }


        .dato strong {

            color: #6d3bd1;

        }


        .estado-grupo {

            margin-top: 15px;

            padding: 12px;

            border-radius: 10px;

            background: #fff8e8;

            color: #9a6b00;

            font-weight: bold;

            text-align: center;

        }


        .estado-grupo.terminado {

            background: #e8fff1;

            color: #16834b;

        }


        .contador {

            margin-bottom: 20px;

            padding: 12px 15px;

            border-radius: 12px;

            background: #f8f5ff;

            color: #666;

            text-align: center;

        }


        .contador strong {

            color: #6d3bd1;

        }


        .tarjeta {

            background: white;

            padding: 35px;

            border-radius: 25px;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.08);

        }


        .titulo {

            color: #6d3bd1;

            font-size: 23px;

            margin-bottom: 10px;

        }


        .descripcion {

            color: #777;

            font-size: 14px;

            margin-bottom: 25px;

        }


        .campo {

            margin-bottom: 20px;

        }


        .campo label {

            display: block;

            color: #555;

            font-weight: bold;

            margin-bottom: 8px;

        }


        .campo input {

            width: 100%;

            padding: 16px;

            border:
                1px solid #ddd;

            border-radius: 12px;

            font-size: 18px;

            outline: none;

        }


        .campo input:focus {

            border-color: #8c52ff;

            box-shadow:
                0 0 0 3px
                rgba(140,82,255,.10);

        }


        .boton {

            width: 100%;

            padding: 16px;

            border: none;

            border-radius: 12px;

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

            transition: .2s;

        }


        .boton:hover {

            transform: translateY(-2px);

        }


        .boton-terminar {

            display: block;

            width: 100%;

            margin-top: 15px;

            padding: 15px;

            border-radius: 12px;

            background: #e8fff1;

            color: #16834b;

            border: 1px solid #a9e8c5;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            text-align: center;

        }


        .boton-terminar:hover {

            background: #d9fae7;

        }


        .mensaje {

            padding: 15px;

            border-radius: 12px;

            margin-bottom: 20px;

            text-align: center;

            font-weight: bold;

        }


        .mensaje.exito {

            background: #e8fff1;

            color: #16834b;

            border:
                1px solid #a9e8c5;

        }


        .mensaje.error {

            background: #fff0f0;

            color: #c0392b;

            border:
                1px solid #f0b5b0;

        }


        .volver {

            display: block;

            margin-top: 18px;

            text-align: center;

            color: #6d3bd1;

            text-decoration: none;

            font-weight: bold;

        }


        .volver:hover {

            text-decoration: underline;

        }


        @media (max-width: 700px) {

            .contenido {

                padding:
                    45px 15px 40px;

            }


            .tarjeta {

                padding: 25px 20px;

                border-radius: 20px;

            }


            .encabezado h1 {

                font-size: 27px;

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
                🍎
            </div>

            <h1>
                Registrar refrigerio
            </h1>

            <p>
                Registra rápidamente la entrega del refrigerio.
            </p>

        </div>


        <!-- INFORMACIÓN DEL GRUPO -->

        <div class="seleccion">

            <h2>
                📋 Información del grupo
            </h2>


            <div class="datos">


                <div class="dato">

                    🏫

                    <strong>
                        Curso:
                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $curso["curso"]
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
                        $curso["jornada"]
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
                        $profesor["nombres"]
                        . " "
                        . $profesor["apellidos"]
                    );

                    ?>

                </div>


            </div>


            <?php if ($id_entrega > 0) { ?>

                <?php if ($estadoEntrega === "Terminado") { ?>

                    <div class="estado-grupo terminado">

                        🟢 Grupo terminado

                    </div>

                <?php } else { ?>

                    <div class="estado-grupo">

                        🟡 Grupo en proceso

                    </div>

                <?php } ?>

            <?php } ?>


        </div>


        <!-- MENSAJE -->

        <?php if ($mensaje !== "") { ?>

            <div class="mensaje <?php echo $tipoMensaje; ?>">

                <?php

                echo htmlspecialchars(
                    $mensaje
                );

                ?>

            </div>

        <?php } ?>


        <!-- CONTADOR -->

        <?php if ($id_entrega > 0) { ?>

            <div class="contador">

                🍎 Estudiantes registrados:

                <strong>
                    <?php echo $totalRegistrados; ?>
                </strong>

            </div>

        <?php } ?>


        <!-- TARJETA -->

        <div class="tarjeta">


            <?php if ($estadoEntrega !== "Terminado") { ?>


                <h2 class="titulo">
                    🍎 Registrar estudiante
                </h2>


                <p class="descripcion">

                    Escribe el número de documento del estudiante
                    y presiona el botón para registrar su refrigerio.

                </p>


                <form
                    method="POST"
                    action=""
                >


                    <div class="campo">

                        <label for="documento">

                            🪪 Documento del estudiante

                        </label>


                        <input
                            type="text"
                            name="documento"
                            id="documento"
                            placeholder="Escribe el documento"
                            autocomplete="off"
                            autofocus
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="boton"
                    >

                        🍎 Registrar refrigerio

                    </button>


                </form>


                <?php if ($id_entrega > 0) { ?>

                    <form
                        method="POST"
                        action="terminar_grupo.php"
                        onsubmit="return confirm('¿Seguro que deseas terminar este grupo? Después de terminarlo se completará la lista de estudiantes.');"
                    >

                        <input
                            type="hidden"
                            name="id_entrega"
                            value="<?php echo $id_entrega; ?>"
                        >


                        <button
                            type="submit"
                            class="boton-terminar"
                        >

                            🏁 Terminar grupo

                        </button>

                    </form>

                <?php } ?>


            <?php } else { ?>


                <h2 class="titulo">

                    🏁 Grupo terminado

                </h2>


                <p class="descripcion">

                    Este grupo ya fue cerrado y no se pueden
                    registrar más estudiantes.

                </p>


            <?php } ?>


            <a
                href="seleccionar_registro.php"
                class="volver"
            >

                ← Cambiar curso o profesor

            </a>


        </div>


    </div>


</main>


<script>

    const campoDocumento =
        document.getElementById("documento");

    if (campoDocumento) {

        campoDocumento.focus();

    }

</script>


</body>

</html>