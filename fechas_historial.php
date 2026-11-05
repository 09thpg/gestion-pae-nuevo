
<?php
session_start();

require_once __DIR__ . "/database/conexion.php";

// Solo puede entrar el administrador
if (
    !isset($_SESSION["id_usuario"]) ||
    !isset($_SESSION["id_rol"]) ||
    (int) $_SESSION["id_rol"] !== 1
) {
    header("Location: login.php");
    exit();
}

// Validar el mes recibido
$mes = $_GET["mes"] ?? "";

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
    header("Location: eliminar_historial.php");
    exit();
}

// Consultar las fechas del mes seleccionado
$sql = "
    SELECT
        fecha,
        COUNT(DISTINCT id_entrega) AS cantidad_registros
    FROM entregas_pae
    WHERE fecha >= CONCAT(?, '-01')
      AND fecha < DATE_ADD(
          CONCAT(?, '-01'),
          INTERVAL 1 MONTH
      )
    GROUP BY fecha
    ORDER BY fecha DESC
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("No fue posible consultar las fechas.");
}

$stmt->bind_param("ss", $mes, $mes);
$stmt->execute();
$resultado = $stmt->get_result();

// Nombre del mes en español
$nombresMeses = [
    1 => "enero", 2 => "febrero", 3 => "marzo",
    4 => "abril", 5 => "mayo", 6 => "junio",
    7 => "julio", 8 => "agosto", 9 => "septiembre",
    10 => "octubre", 11 => "noviembre", 12 => "diciembre"
];

$numeroMes = (int) substr($mes, 5, 2);
$anio = substr($mes, 0, 4);
$nombreMes = ucfirst($nombresMeses[$numeroMes]) . " " . $anio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Fechas del historial - Gestión PAE</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f5f2fb;
            color: #333;
        }

        .contenedor {
            max-width: 1000px;
            margin: auto;
        }

        .volver {
            display: inline-block;
            margin-bottom: 22px;
            color: #6d3bd1;
            text-decoration: none;
            font-weight: bold;
        }

        h1 {
            color: #6d3bd1;
            margin-bottom: 10px;
        }

        .descripcion {
            color: #666;
            margin-bottom: 25px;
        }

        .fecha {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px #00000010;
            color: inherit;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .fecha:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px #00000015;
        }

        .fecha h2 {
            margin: 0 0 8px;
            color: #50309b;
            font-size: 20px;
        }

        .fecha p {
            margin: 0;
            color: #777;
        }

        .etiqueta {
            padding: 9px 13px;
            border-radius: 10px;
            background: #eee7ff;
            color: #6d3bd1;
            font-weight: bold;
            white-space: nowrap;
        }

        .vacio {
            padding: 25px;
            background: white;
            border-radius: 15px;
            text-align: center;
            color: #666;
        }

        @media (max-width: 500px) {
            body {
                padding: 18px;
            }

            .fecha {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
<main class="contenedor">

    <a class="volver" href="eliminar_historial.php">
        ← Volver a los meses
    </a>

    <h1>📅 <?= htmlspecialchars($nombreMes) ?></h1>

    <p class="descripcion">
        Selecciona una fecha para consultar los grupos de ese día.
    </p>

    <?php if ($resultado->num_rows > 0) { ?>

        <?php while ($fila = $resultado->fetch_assoc()) {
            $fecha = $fila["fecha"];

            $fechaFormateada = date(
                "d \\d\\e F \\d\\e Y",
                strtotime($fecha)
            );
        ?>

            <a
                class="fecha"
                href="grupos_historial.php?fecha=<?= urlencode($fecha) ?>"
            >
                <div>
                    <h2>
                        🗓️ <?= htmlspecialchars($fechaFormateada) ?>
                    </h2>

                    <p>
                        <?= (int) $fila["cantidad_registros"] ?>
                        registro(s) de entrega
                    </p>
                </div>

                <span class="etiqueta">Ver grupos →</span>
            </a>

        <?php } ?>

    <?php } else { ?>

        <div class="vacio">
            No hay entregas registradas en este mes.
        </div>

    <?php } ?>

</main>
</body>
</html>
