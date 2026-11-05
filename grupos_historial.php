
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


$fecha = $_GET["fecha"] ?? $_POST["fecha"] ?? "";

$fechaObjeto = DateTime::createFromFormat("!Y-m-d", $fecha);

if (
    !$fechaObjeto ||
    $fechaObjeto->format("Y-m-d") !== $fecha
) {
    header("Location: eliminar_historial.php");
    exit();
}

// Token de seguridad para los formularios
if (empty($_SESSION["csrf_historial"])) {
    $_SESSION["csrf_historial"] = bin2hex(random_bytes(32));
}

$mensaje = "";

// Procesar una solicitud de eliminación
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";
    $idEntrega = filter_input(
        INPUT_POST,
        "id_entrega",
        FILTER_VALIDATE_INT
    );
    $password = $_POST["password"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_historial"], $token)
    ) {
        $mensaje = "La solicitud no es válida. Recarga la página.";
    } elseif (!$idEntrega || $idEntrega < 1 || $password === "") {
        $mensaje = "Completa la contraseña e inténtalo de nuevo.";
    } else {

        // Consultar la contraseña actual del administrador
        $sqlUsuario = "
            SELECT password
            FROM usuarios
            WHERE id_usuario = ?
              AND id_rol = 1
              AND estado = 'Activo'
            LIMIT 1
        ";

        $stmtUsuario = $conexion->prepare($sqlUsuario);
        $stmtUsuario->bind_param(
            "i",
            $_SESSION["id_usuario"]
        );
        $stmtUsuario->execute();

        $usuarioAdmin = $stmtUsuario->get_result()->fetch_assoc();
        $stmtUsuario->close();

        if (
            !$usuarioAdmin ||
            !password_verify($password, $usuarioAdmin["password"])
        ) {
            $mensaje = "La contraseña del administrador es incorrecta.";
        } else {

            try {
                $conexion->begin_transaction();

                // Verificar que la entrega pertenece a la fecha seleccionada
                $sqlVerificar = "
                    SELECT id_entrega
                    FROM entregas_pae
                    WHERE id_entrega = ?
                      AND fecha = ?
                    FOR UPDATE
                ";

                $stmtVerificar = $conexion->prepare($sqlVerificar);
                $stmtVerificar->bind_param(
                    "is",
                    $idEntrega,
                    $fecha
                );
                $stmtVerificar->execute();

                $entrega = $stmtVerificar
                    ->get_result()
                    ->fetch_assoc();

                $stmtVerificar->close();

                if (!$entrega) {
                    throw new RuntimeException(
                        "La entrega ya no existe o no corresponde a esa fecha."
                    );
                }

                // Borrar primero los detalles de la entrega
                $sqlDetalles = "
                    DELETE FROM detalle_entrega
                    WHERE id_entrega = ?
                ";

                $stmtDetalles = $conexion->prepare($sqlDetalles);
                $stmtDetalles->bind_param("i", $idEntrega);
                $stmtDetalles->execute();
                $stmtDetalles->close();

                // Borrar únicamente la entrega seleccionada
                $sqlEliminar = "
                    DELETE FROM entregas_pae
                    WHERE id_entrega = ?
                      AND fecha = ?
                ";

                $stmtEliminar = $conexion->prepare($sqlEliminar);
                $stmtEliminar->bind_param(
                    "is",
                    $idEntrega,
                    $fecha
                );
                $stmtEliminar->execute();

                if ($stmtEliminar->affected_rows !== 1) {
                    $stmtEliminar->close();

                    throw new RuntimeException(
                        "No se pudo eliminar la entrega."
                    );
                }

                $stmtEliminar->close();
                $conexion->commit();

                // Evitar reutilizar el token tras una operación
                unset($_SESSION["csrf_historial"]);

                header(
                    "Location: grupos_historial.php?fecha=" .
                    urlencode($fecha) .
                    "&eliminado=1"
                );
                exit();

            } catch (Throwable $e) {
                $conexion->rollback();

                error_log(
                    "Error al eliminar historial PAE: " .
                    $e->getMessage()
                );

                $mensaje = "No se pudo eliminar el registro. No se conservaron cambios parciales.";
            }
        }
    }
}


// Consultar los grupos de esa fecha
$sql = "
    SELECT
    e.id_entrega,
        e.id_curso,
        c.nombre AS curso,
        j.nombre AS jornada,
        COUNT(DISTINCT d.id_estudiante) AS total_estudiantes,
        COUNT(DISTINCT CASE
            WHEN d.recibido = 1 THEN d.id_estudiante
        END) AS comieron,
        COUNT(DISTINCT CASE
            WHEN d.recibido = 0 THEN d.id_estudiante
        END) AS no_comieron,
        COALESCE(
            MAX(
                CASE
                    WHEN p.id_profesor IS NOT NULL
                    THEN CONCAT(
                        TRIM(p.nombres),
                        ' ',
                        TRIM(p.apellidos)
                    )
                END
            ),
            'Profesor eliminado'
        ) AS profesor
    FROM entregas_pae e
    INNER JOIN cursos c
        ON e.id_curso = c.id_curso
    INNER JOIN jornadas j
        ON e.id_jornada = j.id_jornada
    LEFT JOIN detalle_entrega d
        ON e.id_entrega = d.id_entrega
    LEFT JOIN profesores p
        ON e.id_profesor = p.id_profesor
    WHERE e.fecha = ?
    GROUP BY  e.id_entrega, e.id_curso, c.nombre, j.nombre
    ORDER BY c.nombre ASC
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("No fue posible consultar los grupos.");
}

$stmt->bind_param("s", $fecha);
$stmt->execute();

$grupos = $stmt->get_result();

$fechaFormateada = $fechaObjeto->format("d/m/Y");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Grupos del historial - Gestión PAE</title>

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

        .grupo {
            background: white;
            padding: 22px;
            margin-bottom: 18px;
            border-radius: 16px;
            box-shadow: 0 4px 12px #00000010;
        }

        .grupo h2 {
            margin: 0 0 12px;
            color: #50309b;
        }

        .dato {
            margin: 9px 0;
            color: #555;
            overflow-wrap: anywhere;
        }

        .estadisticas {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .estadistica {
            padding: 14px;
            border-radius: 12px;
            background: #f5f1ff;
            text-align: center;
        }

        .estadistica strong {
            display: block;
            font-size: 22px;
            color: #6d3bd1;
            margin-bottom: 5px;
        }

        .aviso {
            display: inline-block;
            margin-top: 18px;
            padding: 9px 13px;
            background: #fff0d8;
            color: #8a5700;
            border-radius: 9px;
            font-size: 14px;
        }

        .vacio {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            color: #666;
        }

        @media (max-width: 600px) {
            body {
                padding: 18px;
            }

            .estadisticas {
                grid-template-columns: 1fr;
            }

        }

        
.mensaje-exito,
.mensaje-error {
    padding: 14px;
    margin-bottom: 18px;
    border-radius: 10px;
}

.mensaje-exito {
    background: #e0f5e8;
    color: #176b3a;
}

.mensaje-error {
    background: #ffe5e5;
    color: #a52222;
}


.form-eliminar {
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #eee7ff;
}

.form-eliminar label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #50309b;
}

.form-eliminar input[type="password"] {
    width: 100%;
    max-width: 400px;
    padding: 12px;
    margin-bottom: 12px;
    border: 1px solid #d7ccef;
    border-radius: 9px;
}

.btn-eliminar {
    display: block;
    padding: 12px 16px;
    border: none;
    border-radius: 9px;
    background: #b42318;
    color: white;
    font-weight: bold;
    cursor: pointer;
}

.btn-eliminar:hover {
    background: #8f1d14;
}


    </style>
</head>

<body>
<main class="contenedor">

    <a
        class="volver"
        href="fechas_historial.php?mes=<?= urlencode(substr($fecha, 0, 7)) ?>"
    >
        ← Volver a las fechas
    </a>

    <h1>🏫 Grupos del <?= htmlspecialchars($fechaFormateada) ?></h1>

    <p class="descripcion">
        Consulta las entregas registradas para esta fecha.
    </p>

    
<?php if (isset($_GET["eliminado"]) && $_GET["eliminado"] === "1") { ?>
    <p class="mensaje-exito">
        Registro eliminado correctamente.
    </p>
<?php } ?>

<?php if ($mensaje !== "") { ?>
    <p class="mensaje-error">
        <?= htmlspecialchars($mensaje) ?>
    </p>
<?php } ?>


    <?php if ($grupos->num_rows > 0) { ?>

        <?php while ($grupo = $grupos->fetch_assoc()) { ?>

            <section class="grupo">

                <h2>
                    🏫 <?= htmlspecialchars($grupo["curso"]) ?>
                </h2>

                <p class="dato">
                    🌅 <strong>Jornada:</strong>
                    <?= htmlspecialchars($grupo["jornada"]) ?>
                </p>

                <p class="dato">
                    👨‍🏫 <strong>Profesor:</strong>
                    <?= htmlspecialchars($grupo["profesor"]) ?>
                </p>

                <div class="estadisticas">

                    <div class="estadistica">
                        <strong>
                            <?= (int) $grupo["total_estudiantes"] ?>
                        </strong>
                        Estudiantes registrados
                    </div>

                    <div class="estadistica">
                        <strong>
                            <?= (int) $grupo["comieron"] ?>
                        </strong>
                        Recibieron alimentación
                    </div>

                    <div class="estadistica">
                        <strong>
                            <?= (int) $grupo["no_comieron"] ?>
                        </strong>
                        No recibieron alimentación
                    </div>

                </div>

                
<form
    method="POST"
    class="form-eliminar"
    onsubmit="return confirm('¿Seguro que deseas eliminar esta entrega y todos sus detalles? Esta acción no se puede deshacer.');"
>
    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_historial']) ?>"
    >

    <input
        type="hidden"
        name="fecha"
        value="<?= htmlspecialchars($fecha) ?>"
    >

    <input
        type="hidden"
        name="id_entrega"
        value="<?= (int) $grupo['id_entrega'] ?>"
    >

    <label for="password-<?= (int) $grupo['id_curso'] ?>">
        Contraseña del administrador
    </label>

    <input
        type="password"
        id="password-<?= (int) $grupo['id_curso'] ?>"
        name="password"
        autocomplete="current-password"
        required
    >

    <button type="submit" class="btn-eliminar">
        🗑️ Eliminar entrega
    </button>
</form>


            </section>

        <?php } ?>

    <?php } else { ?>

        <div class="vacio">
            No hay grupos registrados para esta fecha.
        </div>

    <?php } ?>

</main>
</body>
</html>
