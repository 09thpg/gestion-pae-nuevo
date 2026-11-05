
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION["id_rol"]) || (int) $_SESSION["id_rol"] !== 3) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";

/* =========================================
   VALIDAR GRUPO
========================================= */

if (!isset($_GET["id_curso"]) || empty($_GET["id_curso"])) {
    header("Location: mis_registros.php");
    exit();
}

$id_curso = (int) $_GET["id_curso"];
$fecha = $_GET["fecha"] ?? "";

$fecha_valida = false;

if ($fecha !== "") {
    $fecha_objeto = DateTime::createFromFormat("!Y-m-d", $fecha);

    $fecha_valida = $fecha_objeto !== false
        && $fecha_objeto->format("Y-m-d") === $fecha;
}

/* =========================================
   DATOS DEL GRUPO
========================================= */

$sql_curso = "
    SELECT
        c.id_curso,
        c.nombre AS nombre_curso,
        j.nombre AS nombre_jornada
    FROM cursos c
    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada
    WHERE c.id_curso = ?
";

$stmt_curso = $conexion->prepare($sql_curso);
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();

$resultado_curso = $stmt_curso->get_result();

if ($resultado_curso->num_rows === 0) {
    header("Location: mis_registros.php");
    exit();
}

$curso = $resultado_curso->fetch_assoc();

/* =========================================
   ESTUDIANTES DEL GRUPO
========================================= */

if ($fecha_valida) {

    $sql_estudiantes = "
        SELECT
            es.id_estudiante,
            es.documento,
            es.nombres,
            es.apellidos,
            es.fecha_nacimiento,
            es.sexo,
            es.correo,
            es.beneficiario,
            es.estado,
            d.recibido,
            d.observacion AS observacion_comida
        FROM estudiantes es
        LEFT JOIN detalle_entrega d
            ON d.id_estudiante = es.id_estudiante
            AND d.id_entrega = (
                SELECT MAX(ep.id_entrega)
                FROM entregas_pae ep
                WHERE ep.id_curso = ?
                  AND ep.fecha = ?
            )
        WHERE es.id_curso = ?
        ORDER BY es.apellidos ASC, es.nombres ASC
    ";

    $stmt_estudiantes = $conexion->prepare($sql_estudiantes);
    $stmt_estudiantes->bind_param(
        "isi",
        $id_curso,
        $fecha,
        $id_curso
    );

} else {

    $sql_estudiantes = "
        SELECT
            id_estudiante,
            documento,
            nombres,
            apellidos,
            fecha_nacimiento,
            sexo,
            correo,
            beneficiario,
            estado,
            NULL AS recibido,
            NULL AS observacion_comida
        FROM estudiantes
        WHERE id_curso = ?
        ORDER BY apellidos ASC, nombres ASC
    ";

    $stmt_estudiantes = $conexion->prepare($sql_estudiantes);
    $stmt_estudiantes->bind_param("i", $id_curso);
}

$stmt_estudiantes->execute();
$estudiantes = $stmt_estudiantes->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Estudiantes del grupo</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f1fc;
        }

        .contenido {
            margin-left: 250px;
            padding: 40px;
        }

        .encabezado {
            background: linear-gradient(
                135deg,
                #7650d6,
                #19add1
            );
            color: white;
            padding: 28px;
            border-radius: 20px;
            margin-bottom: 25px;
        }

        .encabezado h1 {
            margin: 0 0 8px;
        }

        .encabezado p {
            margin: 5px 0;
        }

        .volver {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #5b2c83;
            font-weight: bold;
        }

        .resumen {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .tarjeta-resumen {
            background: white;
            padding: 18px 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .tarjeta-resumen strong {
            display: block;
            font-size: 24px;
            color: #6f42c1;
        }

        .tabla-contenedor {
            background: white;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f1ebfb;
            color: #5b2c83;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #faf8ff;
        }

        .estado {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .activo {
            background: #e8f7ed;
            color: #187a3d;
        }

        .inactivo {
            background: #fbeaea;
            color: #a33;
        }

        .sin-estudiantes {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .observacion-comida {
            display: block;
            margin-top: 6px;
            color: #666;
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .contenido {
                margin-left: 0;
                padding: 20px;
            }

            .resumen {
                flex-direction: column;
            }

            table {
                min-width: 750px;
            }
        }
    </style>
</head>

<body>

<?php include "../componentes/sidebar_profesor.php"; ?>

<div class="contenido">

    <a href="perfil.php" class="volver">
        ← Volver a registros PAE
    </a>

    <div class="encabezado">
        <h1>
            🏫 <?= htmlspecialchars($curso["nombre_curso"]) ?>
        </h1>

        <p>
            Jornada:
            <?= htmlspecialchars($curso["nombre_jornada"]) ?>
        </p>

        <p>👨‍🎓 Lista de estudiantes del grupo</p>

        <?php if ($fecha_valida): ?>
            <p>
                📅 Fecha de entrega:
                <?= htmlspecialchars($fecha) ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="resumen">
        <div class="tarjeta-resumen">
            <strong><?= $estudiantes->num_rows ?></strong>
            👨‍🎓 Estudiantes
        </div>
    </div>

    <?php if ($estudiantes->num_rows > 0): ?>

        
            <table>
                <thead>
                 
<div class="tabla-contenedor">
    <h2>👨‍🎓 Estudiantes del registro</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>👨‍🎓 Estudiante</th>
                <th>🍎 Consumo del refrigerio</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $numero = 1;

            while ($estudiante = $estudiantes->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $numero++ ?></td>

                    <td>
                        <strong>
                            <?= htmlspecialchars($estudiante["nombres"]) ?>
                            <?= htmlspecialchars($estudiante["apellidos"]) ?>
                        </strong>
                    </td>

                    <td>
                        <?php if (!$fecha_valida): ?>

                            <span class="estado inactivo">
                                Consulta por fecha
                            </span>

                        <?php elseif ($estudiante["recibido"] === null): ?>

                            <span class="estado inactivo">
                                ❌ Sin registro
                            </span>

                        <?php elseif (
                            in_array(
                                strtolower(trim((string) $estudiante["recibido"])),
                                ["1", "si", "sí", "true", "recibido", "comió", "comio"],
                                true
                            )
                        ): ?>

                            <span class="estado activo">
                                🍎 Consumió
                            </span>

                        <?php else: ?>

                            <span class="estado inactivo">
                                ❌ No consumió
                            </span>

                        <?php endif; ?>
                    </td>
                </tr>

            <?php endwhile; ?>
        </tbody>
    </table>
</div>


    <?php else: ?>

        <div class="tabla-contenedor sin-estudiantes">
            <h2>👨‍🎓 No hay estudiantes</h2>
            <p>Este grupo todavía no tiene estudiantes registrados.</p>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
