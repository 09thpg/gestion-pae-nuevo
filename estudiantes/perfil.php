<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");

$id_usuario = $_SESSION["id_usuario"];
$id_rol = $_SESSION["id_rol"];


/* =====================================================
   1. IDENTIFICAR EL ESTUDIANTE
   ===================================================== */

if ($id_rol == 2) {

    // El estudiante solamente puede consultar su propio perfil
    $sql = "
        SELECT
            e.id_estudiante,
            e.documento,
            e.tipo_documento,
            e.nombres,
            e.apellidos,
            e.fecha_nacimiento,
            e.sexo,
            e.correo,
            e.foto,
            e.beneficiario,
            e.estado,
            c.nombre AS curso,
            j.nombre AS jornada
        FROM estudiantes e
        INNER JOIN cursos c
            ON e.id_curso = c.id_curso
        INNER JOIN jornadas j
            ON c.id_jornada = j.id_jornada
        WHERE e.id_usuario = ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $estudiante = $resultado->fetch_assoc();

} else {

    // Administrador: puede consultar cualquier estudiante mediante ?id=
    $id_estudiante = $_GET["id"] ?? "";

    if (empty($id_estudiante)) {
        die("Estudiante no especificado.");
    }

    $sql = "
        SELECT
            e.id_estudiante,
            e.documento,
            e.tipo_documento,
            e.nombres,
            e.apellidos,
            e.fecha_nacimiento,
            e.sexo,
            e.correo,
            e.foto,
            e.beneficiario,
            e.estado,
            c.nombre AS curso,
            j.nombre AS jornada
        FROM estudiantes e
        INNER JOIN cursos c
            ON e.id_curso = c.id_curso
        INNER JOIN jornadas j
            ON c.id_jornada = j.id_jornada
        WHERE e.id_estudiante = ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_estudiante);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $estudiante = $resultado->fetch_assoc();
}


/* =====================================================
   2. VALIDAR ESTUDIANTE
   ===================================================== */

if (!$estudiante) {
    die("No se encontró la información del estudiante.");
}

$id_estudiante_real = $estudiante["id_estudiante"];


/* =====================================================
   3. HISTORIAL DEL PAE
   ===================================================== */

$sqlHistorial = "
    SELECT
        e.fecha,
        c.nombre AS curso,
        j.nombre AS jornada,
        d.recibido,
        d.observacion,
        e.observaciones
    FROM detalle_entrega d
    INNER JOIN entregas_pae e
        ON d.id_entrega = e.id_entrega
    INNER JOIN cursos c
        ON e.id_curso = c.id_curso
    INNER JOIN jornadas j
        ON e.id_jornada = j.id_jornada
    WHERE d.id_estudiante = ?
    ORDER BY e.fecha DESC, e.id_entrega DESC
";

$stmtHistorial = $conexion->prepare($sqlHistorial);
$stmtHistorial->bind_param("i", $id_estudiante_real);
$stmtHistorial->execute();

$historial = $stmtHistorial->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Perfil del estudiante | Gestión PAE</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f3fa;
            color: #333;
            padding: 30px;
        }

        .contenedor {
            max-width: 1100px;
            margin: auto;
        }

        .perfil-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .perfil-header {
            display: flex;
            align-items: center;
            gap: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }

        .foto-perfil {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #eee;
        }

        .foto-vacia {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }

        .nombre {
            font-size: 30px;
            color: #5b2c83;
            margin-bottom: 8px;
        }

        .curso {
            color: #777;
            font-size: 17px;
        }

        .seccion {
            margin-top: 30px;
        }

        .seccion h2 {
            color: #5b2c83;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .datos {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .dato {
            background: #faf9fc;
            padding: 15px;
            border-radius: 12px;
        }

        .dato strong {
            display: block;
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .dato span {
            font-size: 16px;
        }

        .beneficiario {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 20px;
            font-weight: bold;
        }

        .si {
            background: #dff5e5;
            color: #218838;
        }

        .no {
            background: #f8d7da;
            color: #b02a37;
        }

        .tabla-contenedor {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        th {
            background: #5b2c83;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #faf8fc;
        }

        .recibio-si {
            color: #218838;
            font-weight: bold;
        }

        .recibio-no {
            color: #b02a37;
            font-weight: bold;
        }

        .sin-registros {
            text-align: center;
            padding: 30px;
            color: #777;
            background: #faf9fc;
            border-radius: 12px;
        }

        /* ENCABEZADO DE LA PÁGINA */

        .titulo-pagina {
            margin-bottom: 25px;
        }

        .titulo-pagina h1 {
            color: #4b2a78;
            font-size: 30px;
            margin-bottom: 6px;
        }

        .titulo-pagina p {
            color: #777;
            font-size: 15px;
        }


/* BOTÓN PARA VOLVER */

.boton-volver {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 11px 17px;
    margin-bottom: 22px;
    background: white;
    color: #5b2c83;
    border: 1px solid #e5dcf3;
    border-radius: 12px;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
    transition: .2s ease;
}

.boton-volver:hover {
    background: #5b2c83;
    color: white;
    transform: translateX(-3px);
}

@media (max-width: 700px) {
    .boton-volver {
        width: fit-content;
        max-width: 100%;
        margin-top: 55px;
    }
}


        /* RESPONSIVE */

        @media (max-width: 700px) {

            body {
                padding: 15px;
            }

            .perfil-card {
                padding: 20px;
            }

            .perfil-header {
                flex-direction: column;
                text-align: center;
            }

            .nombre {
                font-size: 24px;
            }

            .datos {
                grid-template-columns: 1fr;
            }

            table {
                min-width: 750px;
            }

        }

        
/* ==========================================
   RESPONSIVE DEL PERFIL DEL ESTUDIANTE
   ========================================== */

/* Evitar desbordamientos */
html {
    width: 100%;
    overflow-x: hidden;
}

body {
    width: 100%;
    min-width: 0;
    padding: 30px;
}

.contenedor {
    width: 100%;
    min-width: 0;
}

.perfil-card {
    width: 100%;
    min-width: 0;
}

.perfil-header > div {
    min-width: 0;
}

.nombre,
.curso,
.dato span {
    overflow-wrap: anywhere;
    word-break: normal;
}

.foto-perfil,
.foto-vacia {
    flex-shrink: 0;
}

.tabla-contenedor {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

table {
    min-width: 750px;
}

/* Tabletas */
@media (max-width: 900px) {
    body {
        padding: 20px;
    }

    .perfil-card {
        padding: 24px;
    }

    .nombre {
        font-size: 27px;
    }

    .datos {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Celulares */
@media (max-width: 700px) {
    body {
        padding: 15px;
    }

    .contenedor {
        width: 100%;
    }

    .perfil-card {
        padding: 18px;
        margin-bottom: 20px;
        border-radius: 15px;
    }

    .perfil-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 15px;
        padding-bottom: 20px;
    }

    .foto-perfil,
    .foto-vacia {
        width: 110px;
        height: 110px;
    }

    .foto-vacia {
        font-size: 42px;
    }

    .nombre {
        font-size: 23px;
        line-height: 1.3;
    }

    .curso {
        font-size: 14px;
        line-height: 1.6;
    }

    .titulo-pagina {
        margin-top: 15px;
        margin-bottom: 20px;
    }

    .titulo-pagina h1 {
        font-size: 25px;
        line-height: 1.3;
    }

    .titulo-pagina p {
        font-size: 14px;
        line-height: 1.5;
    }

    .seccion {
        margin-top: 24px;
    }

    .seccion h2 {
        font-size: 19px;
        margin-bottom: 15px;
    }

    .datos {
        grid-template-columns: minmax(0, 1fr);
        gap: 10px;
    }

    .dato {
        padding: 13px;
        min-width: 0;
    }

    .dato span {
        font-size: 14px;
    }

    .beneficiario {
        max-width: 100%;
        white-space: normal;
        line-height: 1.5;
    }

    .tabla-contenedor {
        border-radius: 10px;
    }

    table {
        min-width: 650px;
        font-size: 13px;
    }

    th,
    td {
        padding: 10px;
    }

    .sin-registros {
        padding: 20px 12px;
        line-height: 1.6;
    }

    .boton-volver {
        margin-top: 55px;
        max-width: 100%;
        white-space: normal;
    }
}

/* Celulares muy pequeños */
@media (max-width: 380px) {
    body {
        padding: 10px;
    }

    .perfil-card {
        padding: 14px;
    }

    .nombre {
        font-size: 21px;
    }

    .seccion h2 {
        font-size: 17px;
    }

    .dato {
        padding: 11px;
    }
}


    </style>

</head>


<body>


<?php if ($id_rol == 2): ?>

    <!-- SIDEBAR DEL ESTUDIANTE -->
    <?php include("../componentes/sidebar_estudiante.php"); ?>

<?php endif; ?>


<div class="contenedor">


<?php if ($id_rol == 1): ?>

    <a href="listar.php" class="boton-volver">
        ← Volver a estudiantes
    </a>

<?php elseif ($id_rol == 2): ?>

    

<?php endif; ?>

    <!-- ==========================================
         TÍTULO
         ========================================== -->

    <?php if ($id_rol == 2): ?>

        <div class="titulo-pagina">

            <h1>
                👨‍🎓 Mi perfil
            </h1>

            <p>
                Consulta tu información personal, académica y del PAE.
            </p>

        </div>

    <?php endif; ?>


    <!-- ==========================================
         PERFIL DEL ESTUDIANTE
         ========================================== -->

    <div class="perfil-card">

        <div class="perfil-header">


            <?php if (!empty($estudiante["foto"])): ?>

                <img
                    src="../uploads/<?php echo htmlspecialchars($estudiante["foto"]); ?>"
                    class="foto-perfil"
                    alt="Foto del estudiante"
                >

            <?php else: ?>

                <div class="foto-vacia">
                    👤
                </div>

            <?php endif; ?>


            <div>

                <h1 class="nombre">

                    <?php
                    echo htmlspecialchars(
                        $estudiante["nombres"] . " " . $estudiante["apellidos"]
                    );
                    ?>

                </h1>

                <p class="curso">

                    🎓 <?php echo htmlspecialchars($estudiante["curso"]); ?>

                    ·

                    🕐 <?php echo htmlspecialchars($estudiante["jornada"]); ?>

                </p>

            </div>

        </div>


        <!-- ==========================================
             DATOS PERSONALES
             ========================================== -->

        <div class="seccion">

            <h2>
                👤 Información personal
            </h2>


            <div class="datos">


                <div class="dato">

                    <strong>
                        Tipo de documento
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($estudiante["tipo_documento"]); ?>
                    </span>

                </div>


                <div class="dato">

                    <strong>
                        Documento
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($estudiante["documento"]); ?>
                    </span>

                </div>


                <div class="dato">

                    <strong>
                        Fecha de nacimiento
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($estudiante["fecha_nacimiento"]); ?>
                    </span>

                </div>


                <div class="dato">

                    <strong>
                        Sexo
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($estudiante["sexo"]); ?>
                    </span>

                </div>


                <div class="dato">

                    <strong>
                        Correo electrónico
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($estudiante["correo"]); ?>
                    </span>

                </div>


                <div class="dato">

                    <strong>
                        Estado
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($estudiante["estado"]); ?>
                    </span>

                </div>


            </div>

        </div>


        <!-- ==========================================
             INFORMACIÓN ACADÉMICA
             ========================================== -->

        <div class="seccion">

            <h2>
                🎓 Información académica
            </h2>


            <div class="datos">


                <div class="dato">

                    <strong>
                        Curso
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($estudiante["curso"]); ?>
                    </span>

                </div>


                <div class="dato">

                    <strong>
                        Jornada
                    </strong>

                    <span>
                        <?php echo htmlspecialchars($estudiante["jornada"]); ?>
                    </span>

                </div>


            </div>

        </div>


        <!-- ==========================================
             INFORMACIÓN PAE
             ========================================== -->

        <div class="seccion">

            <h2>
                🍽️ Información PAE
            </h2>


            <div class="dato">

                <strong>
                    Beneficiario del PAE
                </strong>


                
<?php if ((int)$estudiante["beneficiario"] === 1): ?>

    <span class="beneficiario si">
        ✓ Sí, es beneficiario
    </span>

<?php else: ?>

    <span class="beneficiario no">
        ✕ No es beneficiario
    </span>

<?php endif; ?>



            </div>

        </div>


    </div>


    <!-- ==========================================
         HISTORIAL PAE
         ========================================== -->

    <div class="perfil-card">

        <div class="seccion" style="margin-top: 0;">

            <h2>
                🍽️ Historial de entregas PAE
            </h2>


            <?php if ($historial->num_rows > 0): ?>


                <div class="tabla-contenedor">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Jornada
                                </th>

                                <th>
                                    Curso
                                </th>

                                <th>
                                    Recibió
                                </th>

                                <th>
                                    Observación
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($registro = $historial->fetch_assoc()): ?>

                            <tr>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $registro["fecha"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $registro["jornada"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $registro["curso"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php if ($registro["recibido"] == 1): ?>

                                        <span class="recibio-si">
                                            ✓ Sí
                                        </span>

                                    <?php else: ?>

                                        <span class="recibio-no">
                                            ✕ No
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php

                                    if (!empty($registro["observacion"])) {

                                        echo htmlspecialchars(
                                            $registro["observacion"]
                                        );

                                    } elseif (!empty($registro["observaciones"])) {

                                        echo htmlspecialchars(
                                            $registro["observaciones"]
                                        );

                                    } else {

                                        echo "Sin observación";

                                    }

                                    ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="sin-registros">

                    🍽️ Este estudiante todavía no tiene registros
                    de entregas del PAE.

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>


</body>

</html>
