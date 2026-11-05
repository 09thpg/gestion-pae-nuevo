
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
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================================
   VALIDAR FECHAS Y FILTROS
========================================= */

function fechaValidaPDF($fecha) {
    if (!is_string($fecha) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return false;
    }

    $partes = explode("-", $fecha);

    return checkdate(
        (int)$partes[1],
        (int)$partes[2],
        (int)$partes[0]
    );
}

function escaparPDF($texto) {
    return htmlspecialchars(
        (string)$texto,
        ENT_QUOTES,
        "UTF-8"
    );
}

function fechaBonitaPDF($fecha) {
    return date("d/m/Y", strtotime($fecha));
}

$desde = $_GET["desde"] ?? "";
$hasta = $_GET["hasta"] ?? "";
$tipoFecha = $_GET["tipo_fecha"] ?? "dia";

$cursosSeleccionados = $_GET["id_cursos"] ?? [];

if (!is_array($cursosSeleccionados)) {
    $cursosSeleccionados = [];
}

$cursosSeleccionados = array_values(
    array_unique(
        array_filter(
            array_map("intval", $cursosSeleccionados),
            function ($id) {
                return $id > 0;
            }
        )
    )
);

if (!fechaValidaPDF($desde)) {
    exit("La fecha inicial no es válida.");
}

if ($tipoFecha === "dia") {
    $hasta = $desde;
} elseif (!fechaValidaPDF($hasta) || $desde > $hasta) {
    exit("El rango de fechas no es válido.");
}

/* =========================================
   CONSULTAR ESTADÍSTICAS POR CURSO
========================================= */

$sql = "
    SELECT
        c.id_curso,
        c.nombre AS curso,
        j.nombre AS jornada,

        COUNT(
            CASE WHEN d.recibido = 1
            THEN 1 END
        ) AS refrigerios_entregados,

        COUNT(
            CASE WHEN d.recibido = 0
            THEN 1 END
        ) AS registros_sin_entrega,

        COUNT(DISTINCT
            CASE WHEN d.recibido = 1
            THEN d.id_estudiante END
        ) AS estudiantes_unicos_recibieron

    FROM entregas_pae e

    INNER JOIN detalle_entrega d
        ON e.id_entrega = d.id_entrega

    INNER JOIN cursos c
        ON e.id_curso = c.id_curso

    INNER JOIN jornadas j
        ON e.id_jornada = j.id_jornada

    WHERE e.fecha BETWEEN ? AND ?
";

$parametros = [$desde, $hasta];
$tipos = "ss";

if (!empty($cursosSeleccionados)) {
    $marcadores = implode(
        ",",
        array_fill(0, count($cursosSeleccionados), "?")
    );

    $sql .= " AND e.id_curso IN ($marcadores)";

    foreach ($cursosSeleccionados as $id) {
        $parametros[] = $id;
        $tipos .= "i";
    }
}

$sql .= "
    GROUP BY c.id_curso, c.nombre, j.nombre
    ORDER BY c.nombre ASC
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit("No se pudo preparar la consulta del PDF.");
}

$referencias = [];
$referencias[] = &$tipos;

foreach ($parametros as $indice => $valor) {
    $referencias[] = &$parametros[$indice];
}

call_user_func_array(
    [$stmt, "bind_param"],
    $referencias
);

$stmt->execute();
$resultado = $stmt->get_result();

$grupos = [];
$totalEntregados = 0;
$totalSinEntrega = 0;
$totalEstudiantesUnicos = 0;

while ($fila = $resultado->fetch_assoc()) {
    $fila["refrigerios_entregados"] =
        (int)$fila["refrigerios_entregados"];

    $fila["registros_sin_entrega"] =
        (int)$fila["registros_sin_entrega"];

    $fila["estudiantes_unicos_recibieron"] =
        (int)$fila["estudiantes_unicos_recibieron"];

    $grupos[] = $fila;

    $totalEntregados += $fila["refrigerios_entregados"];
    $totalSinEntrega += $fila["registros_sin_entrega"];
    $totalEstudiantesUnicos +=
        $fila["estudiantes_unicos_recibieron"];
}

$stmt->close();


/* CONSULTAR REFRIGERIOS ENTREGADOS POR DÍA */

$sqlDias = "
    SELECT
        e.fecha,
        COUNT(
            CASE WHEN d.recibido = 1
            THEN 1 END
        ) AS refrigerios_entregados
    FROM entregas_pae e
    INNER JOIN detalle_entrega d
        ON e.id_entrega = d.id_entrega
    WHERE e.fecha BETWEEN ? AND ?
";

$parametrosDias = [$desde, $hasta];
$tiposDias = "ss";

if (!empty($cursosSeleccionados)) {
    $marcadoresDias = implode(
        ",",
        array_fill(0, count($cursosSeleccionados), "?")
    );

    $sqlDias .= " AND e.id_curso IN ($marcadoresDias)";

    foreach ($cursosSeleccionados as $id) {
        $parametrosDias[] = $id;
        $tiposDias .= "i";
    }
}

$sqlDias .= " GROUP BY e.fecha ORDER BY e.fecha ASC";

$stmtDias = $conexion->prepare($sqlDias);

if (!$stmtDias) {
    exit("No se pudo consultar la gráfica diaria.");
}

$referenciasDias = [];
$referenciasDias[] = &$tiposDias;

foreach ($parametrosDias as $indice => $valor) {
    $referenciasDias[] = &$parametrosDias[$indice];
}

call_user_func_array(
    [$stmtDias, "bind_param"],
    $referenciasDias
);

$stmtDias->execute();
$resultadoDias = $stmtDias->get_result();

$datosPorDia = [];

while ($filaDia = $resultadoDias->fetch_assoc()) {
    $filaDia["refrigerios_entregados"] =
        (int) $filaDia["refrigerios_entregados"];

    $datosPorDia[] = $filaDia;
}

$stmtDias->close();


/* =========================================
   CONSULTAR DETALLE DE ESTUDIANTES
========================================= */

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

    INNER JOIN detalle_entrega d
        ON e.id_entrega = d.id_entrega

    INNER JOIN estudiantes es
        ON d.id_estudiante = es.id_estudiante

    INNER JOIN cursos c
        ON e.id_curso = c.id_curso

    INNER JOIN jornadas j
        ON e.id_jornada = j.id_jornada

    WHERE e.fecha BETWEEN ? AND ?
";

$parametrosDetalle = [$desde, $hasta];
$tiposDetalle = "ss";

if (!empty($cursosSeleccionados)) {
    $marcadoresDetalle = implode(
        ",",
        array_fill(0, count($cursosSeleccionados), "?")
    );

    $sqlDetalle .= " AND e.id_curso IN ($marcadoresDetalle)";

    foreach ($cursosSeleccionados as $id) {
        $parametrosDetalle[] = $id;
        $tiposDetalle .= "i";
    }
}

$sqlDetalle .= "
    ORDER BY c.nombre, es.apellidos, es.nombres, e.fecha
";

$stmtDetalle = $conexion->prepare($sqlDetalle);

if (!$stmtDetalle) {
    exit("No se pudo preparar el detalle de estudiantes.");
}

$referenciasDetalle = [];
$referenciasDetalle[] = &$tiposDetalle;

foreach ($parametrosDetalle as $indice => $valor) {
    $referenciasDetalle[] = &$parametrosDetalle[$indice];
}

call_user_func_array(
    [$stmtDetalle, "bind_param"],
    $referenciasDetalle
);

$stmtDetalle->execute();
$resultadoDetalle = $stmtDetalle->get_result();

/* =========================================
   CONSTRUIR DOCUMENTO PDF
========================================= */

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 35px 30px;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #333333;
    }

    h1 {
        color: #5b2c83;
        text-align: center;
        font-size: 22px;
        margin-bottom: 5px;
    }

    h2 {
        color: #5b2c83;
        font-size: 15px;
        margin-top: 22px;
    }

    .subtitulo {
        text-align: center;
        color: #666666;
        margin-bottom: 20px;
    }

    .resumen {
        width: 100%;
        margin: 18px 0;
        border-collapse: separate;
        border-spacing: 5px;
    }

    .resumen td {
        background: #f2ecff;
        border: 1px solid #e2d5ff;
        padding: 12px 5px;
        text-align: center;
        width: 33%;
    }

    .numero {
        display: block;
        color: #6d3bd1;
        font-size: 20px;
        font-weight: bold;
        margin-top: 5px;
    }

    table.datos {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    table.datos th {
        background: #6d3bd1;
        color: white;
        padding: 7px 5px;
        text-align: left;
    }

    table.datos td {
        border: 1px solid #dddddd;
        padding: 6px 5px;
    }

    table.datos tr:nth-child(even) {
        background: #f8f6fc;
    }

    .pie {
        color: #777777;
        font-size: 9px;
        margin-top: 20px;
    }
</style>
</head>
<body>

<h1>Informe de alimentación PAE</h1>

<p class="subtitulo">
    Periodo: ' . escaparPDF(fechaBonitaPDF($desde)) .
    ' al ' . escaparPDF(fechaBonitaPDF($hasta)) . '
</p>

<table class="resumen">
<tr>
    <td>
        Refrigerios entregados
        <span class="numero">' .
        number_format($totalEntregados) . '</span>
    </td>

    <td>
        Registros sin entrega
        <span class="numero">' .
        number_format($totalSinEntrega) . '</span>
    </td>

    <td>
        Estudiantes únicos atendidos*
        <span class="numero">' .
        number_format($totalEstudiantesUnicos) . '</span>
    </td>
</tr>
</table>

<h2>Comparación por curso</h2>

<table class="datos">
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
';

if (count($grupos) > 0) {
    foreach ($grupos as $grupo) {
        $html .= '
        <tr>
            <td>' . escaparPDF($grupo["curso"]) . '</td>
            <td>' . escaparPDF($grupo["jornada"]) . '</td>
            <td>' . $grupo["refrigerios_entregados"] . '</td>
            <td>' . $grupo["registros_sin_entrega"] . '</td>
            <td>' . $grupo["estudiantes_unicos_recibieron"] . '</td>
        </tr>';
    }
} else {
    $html .= '
    <tr>
        <td colspan="5">No hay registros para los filtros seleccionados.</td>
    </tr>';
}


$html .= '
</tbody>
</table>

<h2>Refrigerios entregados por día</h2>
';

if (count($datosPorDia) > 0) {
    $maximoDia = max(array_map(function ($fila) {
        return (int) $fila["refrigerios_entregados"];
    }, $datosPorDia));

    $html .= '
    <table style="
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        text-align: center;
    ">
        <tr>
    ';

    foreach ($datosPorDia as $filaDia) {
        $cantidadDia = (int) $filaDia["refrigerios_entregados"];

        $alturaDia = $maximoDia > 0
            ? max(3, ($cantidadDia / $maximoDia) * 100)
            : 3;

        $html .= '
            <td style="
                width: ' . (100 / count($datosPorDia)) . '%;
                vertical-align: bottom;
height: 90px;
text-align: center;
padding: 5px;
border: none;
            ">
                <div style="
                width: 28px;
height: ' . $alturaDia . 'px;
margin: 0 auto;
                    background-color: #6d3bd1;
                    color: white;
                    font-weight: bold;
                    padding-top: 5px;
                ">
                    ' . $cantidadDia . '
                </div>
            </td>
        ';
    }

    $html .= '
        </tr>
        <tr>
    ';

    foreach ($datosPorDia as $filaDia) {
        $html .= '
            <td style="
                padding: 5px;
                font-size: 9px;
                border: none;
            ">
                ' . escaparPDF(
                    fechaBonitaPDF($filaDia["fecha"])
                ) . '
            </td>
        ';
    }

    $html .= '
        </tr>
    </table>
    ';
} else {
    $html .= '
    <p>No hay entregas para las fechas seleccionadas.</p>
    ';
}

$html .= '
<h2>Detalle de estudiantes</h2>

<table class="datos">
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
';


if ($resultadoDetalle->num_rows > 0) {
    while ($estudiante = $resultadoDetalle->fetch_assoc()) {
        $estado = ((int)$estudiante["recibido"] === 1)
            ? "Recibió"
            : "No recibió";

        $observacion = $estudiante["observacion"] ?? "";

        $html .= '
        <tr>
            <td>' .
                escaparPDF(fechaBonitaPDF($estudiante["fecha"])) .
            '</td>
            <td>' . escaparPDF($estudiante["curso"]) . '</td>
            <td>' .
                escaparPDF(
                    $estudiante["apellidos"] . " " .
                    $estudiante["nombres"]
                ) .
            '</td>
            <td>' . escaparPDF($estudiante["documento"]) . '</td>
            <td>' . $estado . '</td>
            <td>' .
                (trim($observacion) !== ""
                    ? escaparPDF($observacion)
                    : "Sin observación") .
            '</td>
        </tr>';
    }
} else {
    $html .= '
    <tr>
        <td colspan="6">No hay estudiantes registrados.</td>
    </tr>';
}

$html .= '
</tbody>
</table>

<p class="pie">
    *La cantidad de estudiantes únicos se calcula dentro de cada curso.
    Un mismo estudiante puede aparecer en varias fechas.
    Documento generado por el sistema de gestión PAE.
</p>

</body>
</html>
';

$stmtDetalle->close();

/* =========================================
   GENERAR Y DESCARGAR PDF
========================================= */

$options = new Options();
$options->set("isRemoteEnabled", false);
$options->set("defaultFont", "DejaVu Sans");

$pdf = new Dompdf($options);
$pdf->loadHtml($html, "UTF-8");
$pdf->setPaper("A4", "landscape");
$pdf->render();

$nombreArchivo = "informe_PAE_" . $desde . "_al_" . $hasta . ".pdf";

$pdf->stream($nombreArchivo, [
    "Attachment" => true
]);

exit();
