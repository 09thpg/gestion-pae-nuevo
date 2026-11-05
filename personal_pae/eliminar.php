```php
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["id_rol"] != 1) {
    header("Location: ../login.php");
    exit();
}

include("../database/conexion.php");

// Recibir y validar el ID.
$id_personal = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_personal || $id_personal <= 0) {
    header("Location: listar.php");
    exit();
}

// Buscar el personal y su usuario relacionado.
$sql = "
    SELECT p.id_personal, p.nombres, p.apellidos,
           p.id_usuario
    FROM personal_pae p
    WHERE p.id_personal = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_personal);
$stmt->execute();

$persona = $stmt->get_result()->fetch_assoc();

if (!$persona) {
    header("Location: listar.php?error=no_encontrado");
    exit();
}

$id_usuario = (int) $persona["id_usuario"];

$conexion->begin_transaction();

try {
    // Comprobar si el personal tiene entregas registradas.
    $sqlEntregas = "
        SELECT COUNT(*) AS total
        FROM entregas_pae
        WHERE id_personal = ?
    ";

    $stmt = $conexion->prepare($sqlEntregas);
    $stmt->bind_param("i", $id_personal);
    $stmt->execute();

    $totalEntregas = (int) $stmt->get_result()
        ->fetch_assoc()["total"];

    if ($totalEntregas > 0) {
        throw new Exception(
            "Esta persona tiene entregas PAE asociadas. " .
            "No se puede eliminar sin arriesgar el historial. " .
            "Puedes cambiar su estado a Inactivo desde Editar."
        );
    }

    // Eliminar el registro de personal.
    $sqlPersonal = "
        DELETE FROM personal_pae
        WHERE id_personal = ?
    ";

    $stmt = $conexion->prepare($sqlPersonal);
    $stmt->bind_param("i", $id_personal);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new Exception(
            "No se pudo eliminar el registro del personal."
        );
    }

    // Eliminar su usuario de acceso, si existe y es del rol PAE.
    if ($id_usuario > 0) {
        $sqlUsuario = "
            DELETE FROM usuarios
            WHERE id_usuario = ?
              AND id_rol = 4
        ";

        $stmt = $conexion->prepare($sqlUsuario);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        if ($stmt->affected_rows !== 1) {
            throw new Exception(
                "No se pudo eliminar el usuario de acceso."
            );
        }
    }

    $conexion->commit();

    header("Location: listar.php?eliminado=1");
    exit();

} catch (Throwable $e) {
    $conexion->rollback();

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>No se pudo eliminar</title>
        <style>
            * {
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }

            body {
                min-height: 100vh;
                margin: 0;
                padding: 20px;
                display: grid;
                place-items: center;
                background: linear-gradient(135deg, #eef3ff, #fdfcff);
            }

            .tarjeta {
                width: 100%;
                max-width: 520px;
                padding: 30px;
                background: white;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0,0,0,.08);
            }

            h1 {
                color: #6d3bd1;
                font-size: 24px;
            }

            p {
                color: #555;
                line-height: 1.6;
            }

            a {
                display: inline-block;
                margin-top: 12px;
                padding: 12px 18px;
                border-radius: 10px;
                background: #6d3bd1;
                color: white;
                text-decoration: none;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="tarjeta">
            <h1>⚠️ No se pudo eliminar</h1>

            <p>
                <?= htmlspecialchars(
                    $e->getMessage(),
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </p>

            <a href="listar.php">Volver al listado</a>
        </div>
    </body>
    </html>
    <?php
}
?>
```