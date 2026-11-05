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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listar.php");
    exit();
}

$id_personal = filter_input(INPUT_POST, "id_personal", FILTER_VALIDATE_INT);
$id_usuario = filter_input(INPUT_POST, "id_usuario", FILTER_VALIDATE_INT);

$documento = trim($_POST["documento"] ?? "");
$nombres = trim($_POST["nombres"] ?? "");
$apellidos = trim($_POST["apellidos"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$usuario = trim($_POST["usuario"] ?? "");
$estado = trim($_POST["estado"] ?? "");

if (
    !$id_personal || $id_personal <= 0 ||
    !$id_usuario || $id_usuario <= 0 ||
    $documento === "" ||
    $nombres === "" ||
    $apellidos === "" ||
    $usuario === "" ||
    !in_array($estado, ["Activo", "Inactivo"], true)
) {
    die("
        <h2>⚠️ Datos incompletos o inválidos.</h2>
        <a href='listar.php'>Volver al listado</a>
    ");
}

$conexion->begin_transaction();

try {
    // Comprobar que el registro y su usuario coincidan.
    $sqlComprobar = "
        SELECT p.id_personal
        FROM personal_pae p
        WHERE p.id_personal = ?
          AND p.id_usuario = ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sqlComprobar);
    $stmt->bind_param("ii", $id_personal, $id_usuario);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("No se encontró el personal PAE seleccionado.");
    }

    // Comprobar que el documento no pertenezca a otra persona.
    $sqlDocumento = "
        SELECT id_personal
        FROM personal_pae
        WHERE documento = ?
          AND id_personal <> ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sqlDocumento);
    $stmt->bind_param("si", $documento, $id_personal);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Ese documento ya está registrado para otra persona.");
    }

    // Comprobar que el nombre de usuario no esté ocupado.
    $sqlUsuario = "
        SELECT id_usuario
        FROM usuarios
        WHERE usuario = ?
          AND id_usuario <> ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sqlUsuario);
    $stmt->bind_param("si", $usuario, $id_usuario);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Ese nombre de usuario ya está en uso.");
    }

    // Actualizar datos personales.
    $sqlPersonal = "
        UPDATE personal_pae
        SET documento = ?,
            nombres = ?,
            apellidos = ?,
            telefono = ?,
            correo = ?,
            estado = ?
        WHERE id_personal = ?
          AND id_usuario = ?
    ";

    $stmt = $conexion->prepare($sqlPersonal);
    $stmt->bind_param(
        "ssssssii",
        $documento,
        $nombres,
        $apellidos,
        $telefono,
        $correo,
        $estado,
        $id_personal,
        $id_usuario
    );
    $stmt->execute();

    // Actualizar nombre de usuario y estado de acceso.
    $sqlAcceso = "
        UPDATE usuarios
        SET usuario = ?,
            estado = ?
        WHERE id_usuario = ?
          AND id_rol = 4
    ";

    $stmt = $conexion->prepare($sqlAcceso);
    $stmt->bind_param("ssi", $usuario, $estado, $id_usuario);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        // Verificar que el usuario exista y pertenezca al rol correcto.
        $sqlVerificar = "
            SELECT id_usuario
            FROM usuarios
            WHERE id_usuario = ?
              AND id_rol = 4
              AND usuario = ?
              AND estado = ?
            LIMIT 1
        ";

        $stmt = $conexion->prepare($sqlVerificar);
        $stmt->bind_param("iss", $id_usuario, $usuario, $estado);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("No se pudo actualizar el usuario de acceso.");
        }
    }

    $conexion->commit();

    header("Location: listar.php?actualizado=1");
    exit();

} catch (Throwable $e) {
    $conexion->rollback();

    http_response_code(400);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error al actualizar</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f5f1fc;
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 20px;
                box-sizing: border-box;
            }
            .tarjeta {
                background: white;
                max-width: 500px;
                width: 100%;
                padding: 30px;
                border-radius: 18px;
                box-sizing: border-box;
                box-shadow: 0 10px 30px rgba(0,0,0,.08);
            }
            h2 { color: #6d3bd1; }
            p { color: #555; line-height: 1.6; }
            a {
                display: inline-block;
                margin-top: 15px;
                padding: 12px 18px;
                border-radius: 9px;
                background: #6d3bd1;
                color: white;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="tarjeta">
            <h2>⚠️ No se pudo actualizar</h2>
            <p><?= htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8") ?></p>
            <a href="listar.php">Volver al listado</a>
        </div>
    </body>
    </html>
    <?php
}
?>
```