
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

/* =========================================
   VALIDAR FECHAS
========================================= */

function fechaValida($fecha) {
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

function fechaBonita($fecha) {
    if (!$fecha) {
        return "";
    }

    return date("d/m/Y", strtotime($fecha));
}

function escapar($texto) {
    return htmlspecialchars(
        (string)$texto,
        ENT_QUOTES,
        "UTF-8"
    );
}

/* =========================================
   FILTROS
========================================= */

$desde = $_GET["desde"] ?? "";
$hasta = $_GET["hasta"] ?? "";
$cursosSeleccionados = $_GET["id_cursos"] ?? [];
$error = "";

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

$buscar = isset($_GET["buscar"]);

$tipoFecha = $_GET["tipo_fecha"] ?? "dia";

if ($tipoFecha === "dia" && $desde !== "") {
    $hasta = $desde;
}

if ($buscar) {
    if (!fechaValida($desde) || !fechaValida($hasta)) {
        $error = "Selecciona una fecha inicial y una fecha final válidas.";
    } elseif ($desde > $hasta) {
        $error = "La fecha inicial no puede ser posterior a la fecha final.";
    }
}

/* =========================================
   LISTA DE CURSOS
========================================= */

$cursos = [];

$sqlCursos = "
    SELECT
        c.id_curso,
        c.nombre AS curso,
        j.nombre AS jornada
    FROM cursos c
    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada
    ORDER BY c.nombre ASC
";

$resultadoCursos = $conexion->query($sqlCursos);

if (!$resultadoCursos) {
    die("Error al consultar los cursos: " .
        escapar($conexion->error));
}

while ($fila = $resultadoCursos->fetch_assoc()) {
    $cursos[] = $fila;
}

/* =========================================
   DATOS DEL INFORME
========================================= */

$resultados = [];
$totalRecibidos = 0;
$totalNoRecibidos = 0;
$totalEstudiantesUnicos = 0;
$totalNoRecibidosUnicos = 0;
$totalRegistros = 0;
$informeConsultado = false;

/* =========================================
   DETALLE DE ESTUDIANTES
========================================= */

$detalleEstudiantes = [];

if ($buscar && $error === "") {

    $sqlDetalle = "
        SELECT
            c.id_curso,
            c.nombre AS curso,
            j.nombre AS jornada,
            es.id_estudiante,
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

        $sqlDetalle .= "
            AND e.id_curso IN ($marcadoresDetalle)
        ";

        foreach ($cursosSeleccionados as $id) {
            $parametrosDetalle[] = $id;
            $tiposDetalle .= "i";
        }
    }

    $sqlDetalle .= "
        ORDER BY
            c.nombre ASC,
            es.apellidos ASC,
            es.nombres ASC,
            e.fecha ASC
    ";

    $stmtDetalle = $conexion->prepare($sqlDetalle);

    if (!$stmtDetalle) {
        die("Error al preparar el detalle: " .
            escapar($conexion->error));
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

    while ($filaDetalle = $resultadoDetalle->fetch_assoc()) {
        $detalleEstudiantes[] = $filaDetalle;
    }

    $stmtDetalle->close();
}


/* =========================================
   ESTADÍSTICAS DE REFRIGERIOS POR DÍA
========================================= */

$datosPorDia = [];

if ($buscar && $error === "") {

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

    $sqlDias .= "
        GROUP BY e.fecha
        ORDER BY e.fecha ASC
    ";

    $stmtDias = $conexion->prepare($sqlDias);

    if (!$stmtDias) {
        die("Error al consultar las estadísticas por día.");
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

    while ($filaDia = $resultadoDias->fetch_assoc()) {
        $datosPorDia[] = $filaDia;
    }

    $stmtDias->close();
}


if ($buscar && $error === "") {
    $informeConsultado = true;

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
            ) AS estudiantes_unicos_recibieron,

            COUNT(DISTINCT
                CASE WHEN d.recibido = 0
                THEN d.id_estudiante END
            ) AS estudiantes_unicos_no_recibieron,

            COUNT(d.id_detalle) AS total_registros

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
        GROUP BY
            c.id_curso,
            c.nombre,
            j.nombre
        ORDER BY c.nombre ASC
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error al preparar el informe: " .
            escapar($conexion->error));
    }

    // Vincular dinámicamente los parámetros.
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
    $consulta = $stmt->get_result();

    while ($fila = $consulta->fetch_assoc()) {
        $fila["refrigerios_entregados"] =
            (int)$fila["refrigerios_entregados"];

        $fila["registros_sin_entrega"] =
            (int)$fila["registros_sin_entrega"];

        $fila["estudiantes_unicos_recibieron"] =
            (int)$fila["estudiantes_unicos_recibieron"];

        $fila["estudiantes_unicos_no_recibieron"] =
            (int)$fila["estudiantes_unicos_no_recibieron"];

        $fila["total_registros"] =
            (int)$fila["total_registros"];

        $resultados[] = $fila;

        $totalRecibidos += $fila["refrigerios_entregados"];
        $totalNoRecibidos += $fila["registros_sin_entrega"];
        $totalEstudiantesUnicos +=
            $fila["estudiantes_unicos_recibieron"];
        $totalNoRecibidosUnicos +=
            $fila["estudiantes_unicos_no_recibieron"];
        $totalRegistros += $fila["total_registros"];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Informes y estadísticas PAE</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #eef3ff, #fdfcff);
            color: #333;
        }

        .contenido {
            min-height: 100vh;
            margin-left: 220px;
            padding: 80px 25px 45px;
        }

        .contenedor {
            width: 100%;
            max-width: 1250px;
            margin: auto;
        }

        .encabezado {
            margin-bottom: 25px;
        }

        .encabezado h1 {
            color: #6d3bd1;
            font-size: 30px;
            margin-bottom: 8px;
        }

        .encabezado p {
            color: #666;
            line-height: 1.5;
        }

        .tarjeta {
            background: white;
            padding: 24px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,.07);
            margin-bottom: 24px;
        }

        h2 {
            color: #5b2c83;
            margin-bottom: 16px;
            font-size: 21px;
        }

        .filtros {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .campo label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            color: #555;
        }

        .campo input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 9px;
            font-size: 15px;
        }

        .selector-cursos {
            margin-top: 20px;
        }

        .acciones-cursos {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .boton {
            display: inline-block;
            border: none;
            border-radius: 9px;
            padding: 12px 17px;
            background: #6d3bd1;
            color: white;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        .boton:hover {
            background: #5725b5;
        }

        .boton-secundario {
            background: #eee;
            color: #555;
        }

        .boton-secundario:hover {
            background: #ddd;
        }

        .lista-cursos {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .curso-opcion {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            background: #faf8ff;
            border: 1px solid #eee5ff;
            border-radius: 10px;
            padding: 12px;
            cursor: pointer;
        }

        .curso-opcion input {
            margin-top: 3px;
            accent-color: #6d3bd1;
        }

        .curso-opcion span {
            line-height: 1.4;
        }

        .curso-opcion small {
            display: block;
            color: #777;
            margin-top: 3px;
        }

        .botones-formulario {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .error {
            background: #fff0f0;
            border: 1px solid #f3b7b7;
            color: #a11;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .resumen {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .resumen-card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,.06);
        }

        .resumen-card h3 {
            color: #666;
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 10px;
        }

        .numero {
            color: #6d3bd1;
            font-size: 30px;
            font-weight: bold;
        }

        .nota {
            color: #777;
            font-size: 13px;
            line-height: 1.5;
            margin-top: 10px;
        }

        .tabla-contenedor {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th {
            background: linear-gradient(90deg, #8c52ff, #00aebe);
            color: white;
            text-align: left;
            padding: 13px;
            font-size: 14px;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #eee;
        }

        tbody tr:hover {
            background: #faf8ff;
        }

        .barra-fondo {
            height: 14px;
            width: 100%;
            background: #eee8f8;
            border-radius: 20px;
            overflow: hidden;
            margin: 8px 0 16px;
        }

        .barra {
            height: 100%;
            background: linear-gradient(90deg, #8c52ff, #00bcd4);
            border-radius: 20px;
            min-width: 0;
        }

        .grafica-fila {
            margin-bottom: 18px;
        }

        .grafica-encabezado {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 14px;
        }

        .vacio {
            text-align: center;
            padding: 30px 15px;
            color: #777;
            line-height: 1.6;
        }

        @media (max-width: 1000px) {
            .contenido {
                margin-left: 0;
                padding: 80px 15px 35px;
            }

            .lista-cursos {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .resumen {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .filtros,
            .lista-cursos,
            .resumen {
                grid-template-columns: 1fr;
            }

            .tarjeta {
                padding: 17px;
            }

            .encabezado h1 {
                font-size: 25px;
            }
        }
            
.recibio {
    display: inline-block;
    background: #e8f8ee;
    color: #218838;
    padding: 6px 10px;
    border-radius: 15px;
    font-weight: bold;
    font-size: 13px;
}

.no-recibio {
    display: inline-block;
    background: #fff0f0;
    color: #c62828;
    padding: 6px 10px;
    border-radius: 15px;
    font-weight: bold;
    font-size: 13px;
}

.sin-dato {
    color: #999;
    font-style: italic;
}


.grafica-dia {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
    gap: 16px;
    align-items: end;
    padding: 20px 10px 5px;
    min-height: 260px;
}

.columna-dia {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: end;
    min-width: 0;
    height: 220px;
    gap: 8px;
}

.columna-dia .valor {
    color: #5b2c83;
    font-weight: bold;
    font-size: 14px;
}

.columna-dia .barra-dia {
    width: min(70%, 48px);
    min-height: 3px;
    background: linear-gradient(180deg, #00bcd4, #8064c8);
    border-radius: 8px 8px 0 0;
}

.columna-dia .etiqueta-dia {
    color: #666;
    font-size: 12px;
    text-align: center;
    overflow-wrap: anywhere;
}


.icono-pdf {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e53935;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 5px 4px;
    border-radius: 4px;
    margin-right: 7px;
    vertical-align: middle;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}


.icono-word {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #2474c6;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 5px 4px;
    border-radius: 4px;
    margin-right: 7px;
    vertical-align: middle;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

        
    </style>

</head>

<body>

<?php include("../componentes/sidebar.php"); ?>

<main class="contenido">
<div class="contenedor">

    <header class="encabezado">
        <h1>📊 Informes y estadísticas PAE</h1>
        <p>
            Consulta las entregas de refrigerios por fecha,
            selecciona cursos y compara sus resultados.
        </p>
    </header>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= escapar($error) ?>
        </div>
    <?php endif; ?>

    <section class="tarjeta">
        <h2>🔎 Configurar informe</h2>

        <form method="GET" action="informes.php">
            <div class="filtros">
                
<div class="campo">
    <label for="tipo_fecha">📅 Tipo de consulta</label>

    <select id="tipo_fecha" name="tipo_fecha"
            onchange="cambiarTipoFecha()"
            style="width:100%; padding:12px; border:1px solid #ddd; border-radius:9px;">
        <option value="dia"
            <?= ($_GET["tipo_fecha"] ?? "dia") === "dia" ? "selected" : "" ?>>
            Un solo día
        </option>

        <option value="rango"
            <?= ($_GET["tipo_fecha"] ?? "dia") === "rango" ? "selected" : "" ?>>
            Rango de fechas
        </option>
    </select>
</div>

<div class="campo" id="campo_desde">
    <label for="desde">📅 Fecha inicial</label>
    <input
        type="date"
        id="desde"
        name="desde"
        value="<?= escapar($desde) ?>"
        required
    >
</div>

<div class="campo" id="campo_hasta">
    <label for="hasta">📅 Fecha final</label>
    <input
        type="date"
        id="hasta"
        name="hasta"
        value="<?= escapar($hasta) ?>"
        required
    >
</div>

            </div>

            <div class="selector-cursos">
                <h2>🏫 Seleccionar cursos</h2>

                <div class="acciones-cursos">
                    <button
                        type="button"
                        class="boton boton-secundario"
                        id="seleccionarTodos"
                    >
                        Seleccionar todos
                    </button>

                    <button
                        type="button"
                        class="boton boton-secundario"
                        id="deseleccionarTodos"
                    >
                        Desmarcar todos
                    </button>
                </div>

                <div class="lista-cursos">
                    <?php foreach ($cursos as $curso): ?>
                        <label class="curso-opcion">
                            <input
                                type="checkbox"
                                name="id_cursos[]"
                                value="<?= (int)$curso["id_curso"] ?>"
                                <?= in_array(
                                    (int)$curso["id_curso"],
                                    $cursosSeleccionados,
                                    true
                                ) ? "checked" : "" ?>
                            >

                            <span>
                                <strong>
                                    <?= escapar($curso["curso"]) ?>
                                </strong>
                                <small>
                                    Jornada:
                                    <?= escapar($curso["jornada"]) ?>
                                </small>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <p class="nota">
                    Si no marcas ningún curso, el informe incluirá
                    todos los cursos que tengan registros en esas fechas.
                </p>
            </div>

            <div class="botones-formulario">
                <button
                    type="submit"
                    name="buscar"
                    value="1"
                    class="boton"
                >
                    🔍 Generar informe
                </button>

                <a href="informes.php"
                   class="boton boton-secundario">
                    Limpiar filtros
                </a>




            </div>
        </form>
        

<?php if ($informeConsultado): ?>
    <div class="botones-formulario">

        
<a
    href="exportar_pdf.php?<?= http_build_query([
        'desde' => $desde,
        'hasta' => $hasta,
        'tipo_fecha' => $tipoFecha,
        'id_cursos' => $cursosSeleccionados
    ]) ?>"
    class="boton"
    target="_blank"
>
    <span class="icono-pdf">PDF</span>
    Descargar PDF
</a>
<a
    href="exportar_word.php?<?= http_build_query([
        'desde' => $desde,
        'hasta' => $hasta,
        'tipo_fecha' => $tipoFecha,
        'id_cursos' => $cursosSeleccionados
    ]) ?>"
    class="boton"
>
    <span class="icono-word">WORD</span>
    Descargar Word
</a>

    </div>
<?php endif; ?>

    </section>

    <?php if ($informeConsultado): ?>

        <section class="tarjeta">
            <h2>
                📅 Resultados del
                <?= escapar(fechaBonita($desde)) ?>
                al
                <?= escapar(fechaBonita($hasta)) ?>
            </h2>

            <p>
                Cursos con registros encontrados:
                <strong><?= count($resultados) ?></strong>
            </p>
        </section>

        <section class="resumen">
            <div class="resumen-card">
                <h3>🍽️ Refrigerios entregados</h3>
                <div class="numero">
                    <?= number_format($totalRecibidos) ?>
                </div>
                <p class="nota">
                    Cuenta cada registro de entrega con recibido = 1.
                </p>
            </div>

            <div class="resumen-card">
                <h3>❌ Registros sin entrega</h3>
                <div class="numero">
                    <?= number_format($totalNoRecibidos) ?>
                </div>
                <p class="nota">
                    Cuenta cada registro con recibido = 0.
                </p>
            </div>

            <div class="resumen-card">
                <h3>👨‍🎓 Estudiantes únicos que recibieron</h3>
                <div class="numero">
                    <?= number_format($totalEstudiantesUnicos) ?>
                </div>
                <p class="nota">
                    Cada estudiante se cuenta una sola vez por curso
                    durante el periodo.
                </p>
            </div>
        </section>

        <section class="tarjeta">
            <h2>📈 Comparación de cursos</h2>
            <p class="nota">
                La barra representa la cantidad de registros de
                refrigerios entregados en cada curso.
            </p>

            <?php
            $maximo = 0;

            foreach ($resultados as $fila) {
                $maximo = max(
                    $maximo,
                    $fila["refrigerios_entregados"]
                );
            }
            ?>

            <?php if (count($resultados) > 0): ?>

                <?php foreach ($resultados as $fila): ?>
                    <?php
                    $porcentaje = $maximo > 0
                        ? ($fila["refrigerios_entregados"] / $maximo) * 100
                        : 0;
                    ?>

                    <div class="grafica-fila">
                        <div class="grafica-encabezado">
                            <strong>
                                <?= escapar($fila["curso"]) ?>
                                — <?= escapar($fila["jornada"]) ?>
                            </strong>

                            <span>
                                <?= number_format(
                                    $fila["refrigerios_entregados"]
                                ) ?>
                                refrigerios
                            </span>
                        </div>

                        <div class="barra-fondo">
                            <div
                                class="barra"
                                style="width: <?= $porcentaje ?>%"
                            ></div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="vacio">
                    No hay entregas registradas en el periodo seleccionado.
                </div>
            <?php endif; ?>
        </section>

<section class="tarjeta">
    <h2>📅 Refrigerios entregados por día</h2>

    <p class="nota">
        Cada barra representa la cantidad de refrigerios
        entregados en una fecha según los filtros seleccionados.
    </p>

    <?php if (count($datosPorDia) > 0): ?>

        <?php
        $maximoDia = max(
            array_map(
                function ($fila) {
                    return (int)$fila["refrigerios_entregados"];
                },
                $datosPorDia
            )
        );
        ?>

        <div class="grafica-dia">

            <?php foreach ($datosPorDia as $filaDia): ?>

                <?php
                $cantidadDia =
                    (int)$filaDia["refrigerios_entregados"];

                $alturaDia = $maximoDia > 0
                    ? ($cantidadDia / $maximoDia) * 155
                    : 3;
                ?>

                <div class="columna-dia">

                    <span class="valor">
                        <?= number_format($cantidadDia) ?>
                    </span>

                    <div
                        class="barra-dia"
                        style="height: <?= $alturaDia ?>px"
                        title="<?= escapar(fechaBonita($filaDia["fecha"])) ?>: <?= $cantidadDia ?> refrigerios"
                    ></div>

                    <span class="etiqueta-dia">
                        <?= escapar(fechaBonita($filaDia["fecha"])) ?>
                    </span>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="vacio">
            No hay entregas para las fechas seleccionadas.
        </div>

    <?php endif; ?>
</section>

        <section class="tarjeta">
            <h2>📋 Detalle por curso</h2>

            <?php if (count($resultados) > 0): ?>
                <div class="tabla-contenedor">
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
                            <?php foreach ($resultados as $fila): ?>
                                <tr>
                                    <td>
                                        <?= escapar($fila["curso"]) ?>
                                    </td>

                                    <td>
                                        <?= escapar($fila["jornada"]) ?>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            $fila["refrigerios_entregados"]
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            $fila["registros_sin_entrega"]
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            $fila["estudiantes_unicos_recibieron"]
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <p class="nota">
                    Total de registros de entrega revisados:
                    <strong><?= number_format($totalRegistros) ?></strong>.
                    Los estudiantes únicos pueden aparecer en más de un
                    curso si sus registros están asociados a cursos distintos.
                </p>

            <?php else: ?>
                <div class="vacio">
                    No hay resultados para los filtros seleccionados.
                    Prueba con otras fechas o selecciona todos los cursos.
                </div>
            <?php endif; ?>
        </section>

        <section class="tarjeta">
    <h2>👨‍🎓 Detalle de estudiantes</h2>

    <p class="nota">
        Consulta el estado registrado de cada estudiante
        en cada fecha del periodo seleccionado.
    </p>

    <?php if (count($detalleEstudiantes) > 0): ?>

        <div class="tabla-contenedor">
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
                    <?php foreach ($detalleEstudiantes as $estudiante): ?>
                        <tr>
                            <td>
                                <?= escapar(
                                    fechaBonita($estudiante["fecha"])
                                ) ?>
                            </td>

                            <td>
                                <?= escapar($estudiante["curso"]) ?>
                                <br>
                                <small>
                                    <?= escapar($estudiante["jornada"]) ?>
                                </small>
                            </td>

                            <td>
                                <?= escapar($estudiante["apellidos"]) ?>
                                <?= escapar($estudiante["nombres"]) ?>
                            </td>

                            <td>
                                <?= escapar($estudiante["documento"]) ?>
                            </td>

                            <td>
                                <?php if ((int)$estudiante["recibido"] === 1): ?>
                                    <span class="recibio">
                                        ✅ Recibió
                                    </span>
                                <?php else: ?>
                                    <span class="no-recibio">
                                        ❌ No recibió
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($estudiante["observacion"])): ?>
                                    <?= escapar($estudiante["observacion"]) ?>
                                <?php else: ?>
                                    <span class="sin-dato">
                                        Sin observación
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <div class="vacio">
            No hay estudiantes registrados para los filtros seleccionados.
        </div>

    <?php endif; ?>
</section>

    <?php endif; ?>

</div>
</main>

<script>
    const seleccionarTodos =
        document.getElementById("seleccionarTodos");

    const deseleccionarTodos =
        document.getElementById("deseleccionarTodos");

    function casillasCursos() {
        return document.querySelectorAll(
            'input[name="id_cursos[]"]'
        );
    }

    seleccionarTodos.addEventListener("click", function () {
        casillasCursos().forEach(function (casilla) {
            casilla.checked = true;
        });
    });

    deseleccionarTodos.addEventListener("click", function () {
        casillasCursos().forEach(function (casilla) {
            casilla.checked = false;
        });
    });

    
function cambiarTipoFecha() {
    const tipo = document.getElementById("tipo_fecha").value;
    const campoDesde = document.getElementById("campo_desde");
    const campoHasta = document.getElementById("campo_hasta");
    const desde = document.getElementById("desde");
    const hasta = document.getElementById("hasta");

    if (tipo === "dia") {
        campoHasta.style.display = "none";
        hasta.required = false;
        hasta.disabled = true;

        desde.required = true;
        hasta.value = desde.value;
    } else {
        campoHasta.style.display = "";
        hasta.disabled = false;
        hasta.required = true;
    }
}

document.addEventListener("DOMContentLoaded", cambiarTipoFecha);

</script>

</body>
</html>
