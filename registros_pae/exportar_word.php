
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION["id_rol"]) || $_SESSION["id_rol"] != 1) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";

function escaparWord($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, "UTF-8");
}

function fechaValidaWord($fecha) {
    if (!is_string($fecha) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return false;
    }

    $partes = explode("-", $fecha);
    return checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0]);
}

function fechaBonitaWord($fecha) {
    return date("d/m/Y", strtotime($fecha));
}


function consultarWord($conexion, $sql, $desde, $hasta, $cursos) {
    $parametros = [$desde, $hasta];
    $tipos = "ss";

    if (!empty($cursos)) {
        $marcadores = implode(",", array_fill(0, count($cursos), "?"));
        $condicion = " AND e.id_curso IN ($marcadores) ";

        // Insertar el filtro antes de GROUP BY u ORDER BY.
        if (preg_match('/\s+(GROUP\s+BY|ORDER\s+BY)\s+/i', $sql, $coincidencia, PREG_OFFSET_CAPTURE)) {
            $posicion = $coincidencia[0][1];

            $sql = substr($sql, 0, $posicion)
                 . $condicion
                 . substr($sql, $posicion);
        } else {
            $sql .= $condicion;
        }

        foreach ($cursos as $id) {
            $parametros[] = $id;
            $tipos .= "i";
        }
    }

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        exit("Error al preparar la consulta del informe Word: "
            . htmlspecialchars($conexion->error, ENT_QUOTES, "UTF-8"));
    }

    $referencias = [];
    $referencias[] = &$tipos;

    foreach ($parametros as $i => $valor) {
        $referencias[] = &$parametros[$i];
    }

    call_user_func_array([$stmt, "bind_param"], $referencias);

    if (!$stmt->execute()) {
        exit("Error al ejecutar la consulta del informe Word.");
    }

    $resultado = $stmt->get_result();
    $filas = [];

    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }

    $stmt->close();

    return $filas;
}

/* Recibir filtros */
$desde = $_GET["desde"] ?? "";
$hasta = $_GET["hasta"] ?? "";
$tipoFecha = $_GET["tipo_fecha"] ?? "dia";
$cursos = $_GET["id_cursos"] ?? [];

if (!is_array($cursos)) {
    $cursos = [];
}

$cursos = array_values(array_unique(array_filter(
    array_map("intval", $cursos),
    function ($id) {
        return $id > 0;
    }
)));

if (!fechaValidaWord($desde)) {
    exit("La fecha inicial no es válida.");
}

if ($tipoFecha === "dia") {
    $hasta = $desde;
} elseif (!fechaValidaWord($hasta) || $desde > $hasta) {
    exit("El rango de fechas no es válido.");
}

/* Estadísticas por curso */
$sqlCursos = "
    SELECT
        c.id_curso,
        c.nombre AS curso,
        j.nombre AS jornada,
        COUNT(CASE WHEN d.recibido = 1 THEN 1 END) AS refrigerios_entregados,
        COUNT(CASE WHEN d.recibido = 0 THEN 1 END) AS registros_sin_entrega,
        COUNT(DISTINCT CASE WHEN d.recibido = 1 THEN d.id_estudiante END)
            AS estudiantes_unicos_recibieron
    FROM entregas_pae e
    INNER JOIN detalle_entrega d ON e.id_entrega = d.id_entrega
    INNER JOIN cursos c ON e.id_curso = c.id_curso
    INNER JOIN jornadas j ON e.id_jornada = j.id_jornada
    WHERE e.fecha BETWEEN ? AND ?
    GROUP BY c.id_curso, c.nombre, j.nombre
    ORDER BY c.nombre ASC
";

$resultados = consultarWord(
    $conexion, $sqlCursos, $desde, $hasta, $cursos
);

/* Totales */
$totalEntregados = 0;
$totalSinEntrega = 0;
$totalEstudiantes = 0;

foreach ($resultados as $fila) {
    $totalEntregados += (int)$fila["refrigerios_entregados"];
    $totalSinEntrega += (int)$fila["registros_sin_entrega"];
    $totalEstudiantes += (int)$fila["estudiantes_unicos_recibieron"];
}

/* Refrigerios por día */
$sqlDias = "
    SELECT
        e.fecha,
        COUNT(CASE WHEN d.recibido = 1 THEN 1 END)
            AS refrigerios_entregados
    FROM entregas_pae e
    INNER JOIN detalle_entrega d ON e.id_entrega = d.id_entrega
    WHERE e.fecha BETWEEN ? AND ?
    GROUP BY e.fecha
    ORDER BY e.fecha ASC
";

$datosPorDia = consultarWord(
    $conexion, $sqlDias, $desde, $hasta, $cursos
);

/* Detalle de estudiantes */
$sqlDetalle = "
    SELECT
        c.nombre AS curso,
        j.nombre AS jornada,
        es.documento,
        es.nombres,
        es.apellidos,
        e.fecha,
        d.recibido,
        d.observacion
    FROM entregas_pae e
    INNER JOIN detalle_entrega d ON e.id_entrega = d.id_entrega
    INNER JOIN estudiantes es ON d.id_estudiante = es.id_estudiante
    INNER JOIN cursos c ON e.id_curso = c.id_curso
    INNER JOIN jornadas j ON e.id_jornada = j.id_jornada
    WHERE e.fecha BETWEEN ? AND ?
    ORDER BY c.nombre, es.apellidos, es.nombres, e.fecha
";

$detalle = consultarWord(
    $conexion, $sqlDetalle, $desde, $hasta, $cursos
);

/* Descargar documento Word editable */
$nombreArchivo = "informe_PAE_" . $desde . "_al_" . $hasta . ".doc";

header("Content-Type: application/msword; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
header("Cache-Control: no-store, no-cache, must-revalidate");

echo "\xEF\xBB\xBF";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Informe de alimentación PAE</title>

<style>
body {
    font-family: Arial, sans-serif;
    color: #333333;
    font-size: 11pt;
}

h1 {
    color: #5b2c83;
    text-align: center;
    font-size: 22pt;
}

h2 {
    color: #5b2c83;
    font-size: 15pt;
    margin-top: 24px;
}

.subtitulo {
    text-align: center;
    color: #666666;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
    margin-bottom: 20px;
}

th {
    background-color: #6d3bd1;
    color: #ffffff;
    font-weight: bold;
    padding: 8px;
    border: 1px solid #d5c7f3;
    text-align: left;
}

td {
    padding: 7px;
    border: 1px solid #dddddd;
}

.resumen td {
    background-color: #f2ecff;
    text-align: center;
    padding: 12px;
}

.numero {
    display: block;
    color: #6d3bd1;
    font-size: 18pt;
    font-weight: bold;
    margin-top: 5px;
}

.nota {
    color: #666666;
    font-size: 9pt;
}

.recibio {
    color: #218838;
    font-weight: bold;
}

.no-recibio {
    color: #c62828;
    font-weight: bold;
}
</style>
</head>

<body>

<h1>Informe de alimentación PAE</h1>

<p class="subtitulo">
    Periodo: <?= escaparWord(fechaBonitaWord($desde)) ?>
    al <?= escaparWord(fechaBonitaWord($hasta)) ?>
</p>

<h2>Resumen general</h2>

<table class="resumen">
<tr>
    <td>
        Refrigerios entregados
        <span class="numero"><?= number_format($totalEntregados) ?></span>
    </td>
    <td>
        Registros sin entrega
        <span class="numero"><?= number_format($totalSinEntrega) ?></span>
    </td>
    <td>
        Estudiantes únicos atendidos*
        <span class="numero"><?= number_format($totalEstudiantes) ?></span>
    </td>
</tr>
</table>

<h2>Comparación por curso</h2>

<table>
<thead>
<tr>
    <th>Curso</th>
    <th>Jornada</th>
    <th>Refrigerios entregados</th>
    <th>Registros sin entrega</th>
    <th>Estudiantes únicos atendidos</th>
</tr>
</thead>
<tbody>
<?php if (count($resultados) > 0): ?>
    <?php foreach ($resultados as $fila): ?>
    <tr>
        <td><?= escaparWord($fila["curso"]) ?></td>
        <td><?= escaparWord($fila["jornada"]) ?></td>
        <td><?= number_format((int)$fila["refrigerios_entregados"]) ?></td>
        <td><?= number_format((int)$fila["registros_sin_entrega"]) ?></td>
        <td><?= number_format((int)$fila["estudiantes_unicos_recibieron"]) ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="5">No hay registros para los filtros seleccionados.</td></tr>
<?php endif; ?>
</tbody>
</table>

<h2>Refrigerios entregados por día</h2>

<table>
<thead>
<tr>
    <th>Fecha</th>
    <th>Cantidad entregada</th>
    <th>Representación gráfica</th>
</tr>
</thead>
<tbody>
<?php if (count($datosPorDia) > 0): ?>
    <?php
    $maximoDia = max(array_map(function ($fila) {
        return (int)$fila["refrigerios_entregados"];
    }, $datosPorDia));
    ?>
    <?php foreach ($datosPorDia as $filaDia): ?>
        <?php
        $cantidad = (int)$filaDia["refrigerios_entregados"];
        $ancho = $maximoDia > 0 ? ($cantidad / $maximoDia) * 100 : 0;
        ?>
        <tr>
            <td><?= escaparWord(fechaBonitaWord($filaDia["fecha"])) ?></td>
            <td><?= number_format($cantidad) ?></td>
            <td>
                <table style="width:100%; margin:0;">
                    <tr>
                        <td style="width:<?= $ancho ?>%; background-color:#6d3bd1; color:#ffffff;">
                            <?= number_format($cantidad) ?>
                        </td>
                        <td style="border:none;"></td>
                    </tr>
                </table>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="3">No hay entregas para las fechas seleccionadas.</td></tr>
<?php endif; ?>
</tbody>
</table>

<h2>Detalle de estudiantes</h2>

<table>
<thead>
<tr>
    <th>Fecha</th>
    <th>Curso</th>
    <th>Estudiante</th>
    <th>Documento</th>
    <th>Estado</th>
    <th>Observación</th>
</tr>
</thead>
<tbody>
<?php if (count($detalle) > 0): ?>
    <?php foreach ($detalle as $estudiante): ?>
    <tr>
        <td><?= escaparWord(fechaBonitaWord($estudiante["fecha"])) ?></td>
        <td>
            <?= escaparWord($estudiante["curso"]) ?><br>
            <?= escaparWord($estudiante["jornada"]) ?>
        </td>
        <td>
            <?= escaparWord($estudiante["apellidos"] . " " . $estudiante["nombres"]) ?>
        </td>
        <td><?= escaparWord($estudiante["documento"]) ?></td>
        <td>
            <?php if ((int)$estudiante["recibido"] === 1): ?>
                <span class="recibio">Recibió</span>
            <?php else: ?>
                <span class="no-recibio">No recibió</span>
            <?php endif; ?>
        </td>
        <td>
            <?= trim((string)$estudiante["observacion"]) !== ""
                ? escaparWord($estudiante["observacion"])
                : "Sin observación" ?>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="6">No hay estudiantes registrados.</td></tr>
<?php endif; ?>
</tbody>
</table>

<p class="nota">
    *La cantidad de estudiantes únicos se calcula por curso.
    Un estudiante puede aparecer en varias fechas.function consultarWord
    Documento generado por el sistema de gestión PAE.
</p>

</body>
</html>
<?php exit(); ?>
