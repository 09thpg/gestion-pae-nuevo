
<?php
session_start();

require_once __DIR__ . "/database/conexion.php";

// Solo puede entrar el administrador
if (
    !isset($_SESSION["id_usuario"]) ||
    !isset($_SESSION["id_rol"]) ||
    $_SESSION["id_rol"] != 1
) {
    header("Location: login.php");
    exit();
}

// Consultar los meses que tienen entregas
$sql = "
    SELECT
        DATE_FORMAT(fecha, '%Y-%m') AS mes,
        DATE_FORMAT(fecha, '%M %Y') AS nombre_mes,
        COUNT(DISTINCT fecha) AS cantidad_fechas
    FROM entregas_pae
    GROUP BY DATE_FORMAT(fecha, '%Y-%m'),
             DATE_FORMAT(fecha, '%M %Y')
    ORDER BY mes DESC
";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al consultar el historial.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar historial - Gestión PAE</title>

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

        h1 {
            color: #6d3bd1;
            margin-bottom: 10px;
        }

        .descripcion {
            color: #666;
            margin-bottom: 25px;
        }

        .mes {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 15px;
            box-shadow: 0 4px 12px #00000010;
        }

        .mes h2 {
            margin: 0 0 8px;
            color: #50309b;
            font-size: 20px;
        }

        .mes p {
            margin: 0;
            color: #777;
        }

        .etiqueta {
            background: #eee7ff;
            color: #6d3bd1;
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: bold;
            white-space: nowrap;
        }

        .vacio {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            color: #666;
        }

        .volver {
            display: inline-block;
            margin-bottom: 20px;
            color: #6d3bd1;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 500px) {
            body {
                padding: 18px;
            }

            .mes {
                align-items: flex-start;
                flex-direction: column;
                text-decoration: none;
color: inherit;
cursor: pointer;
transition: transform 0.2s, box-shadow 0.2s;
            }
        }


        .mes:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px #00000015;
}

    </style>
</head>

<body>
    <main class="contenedor">

        <a class="volver" href="dashboard.php">
            ← Volver al inicio
        </a>

        <h1>🗑️ Eliminar historial</h1>

        <p class="descripcion">
            Selecciona un mes para consultar las entregas registradas.
        </p>

        <?php if ($resultado->num_rows > 0) { ?>

            <?php while ($fila = $resultado->fetch_assoc()) { ?>

                
<a
    href="fechas_historial.php?mes=<?= urlencode($fila['mes']) ?>"
    class="mes"
>
    <div>
        <h2>
            📅 <?= htmlspecialchars($fila["nombre_mes"]) ?>
        </h2>

        <p>
            <?= (int) $fila["cantidad_fechas"] ?>
            fechas con entregas
        </p>
    </div>

    <span class="etiqueta">
        Ver fechas →
    </span>
</a>


            <?php } ?>

        <?php } else { ?>

            <div class="vacio">
                No hay entregas registradas en el historial.
            </div>

        <?php } ?>

    </main>
</body>
</html>
