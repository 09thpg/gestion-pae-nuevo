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

$id_personal = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_personal || $id_personal <= 0) {
    header("Location: listar.php");
    exit();
}

$sql = "SELECT p.id_personal, p.documento, p.nombres,
               p.apellidos, p.telefono, p.correo,
               p.estado, p.id_usuario, u.usuario
        FROM personal_pae p
        LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
        WHERE p.id_personal = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_personal);
$stmt->execute();

$resultado = $stmt->get_result();
$persona = $resultado->fetch_assoc();

if (!$persona) {
    header("Location: listar.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Personal PAE</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #eef3ff, #fdfcff);
        }

        .contenido {
            min-height: 100vh;
            padding: 90px 25px 50px;
        }

        .contenedor {
            max-width: 650px;
            margin: auto;
        }

        .tarjeta {
            background: white;
            padding: 35px;
            border-radius: 22px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        }

        h1 {
            color: #6d3bd1;
            margin-bottom: 10px;
        }

        .descripcion {
            color: #666;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .seccion {
            color: #6d3bd1;
            font-size: 19px;
            margin-top: 28px;
            margin-bottom: 18px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }

        .campo {
            margin-bottom: 18px;
        }

        label {
            display: block;
            color: #444;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input, select {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            outline: none;
            background: white;
        }

        input:focus, select:focus {
            border-color: #7e57c2;
            box-shadow: 0 0 0 3px rgba(126,87,194,.10);
        }

        .ayuda {
            color: #888;
            font-size: 13px;
            margin-top: 6px;
        }

        .botones {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .boton {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #8c52ff, #00d4ff);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .boton:hover {
            transform: translateY(-2px);
        }

        .cancelar {
            background: #f0ebfa;
            color: #6d3bd1;
        }

        @media (max-width: 650px) {
            .contenido {
                padding: 80px 15px 30px;
            }

            .tarjeta {
                padding: 25px 20px;
            }

            .botones {
                flex-direction: column;
            }

            .boton {
                width: 100%;
                min-height: 48px;
            }
        }
    </style>
</head>

<body>

<?php include("../componentes/sidebar.php"); ?>

<main class="contenido">
    <div class="contenedor">
        <div class="tarjeta">

            <h1>✏️ Editar Personal PAE</h1>

            <p class="descripcion">
                Modifica los datos personales y de acceso
                del personal encargado del Programa de Alimentación Escolar.
            </p>

            <form action="actualizar.php" method="POST">

                <input type="hidden" name="id_personal"
                       value="<?= (int)$persona['id_personal'] ?>">

                <input type="hidden" name="id_usuario"
                       value="<?= (int)$persona['id_usuario'] ?>">

                <h2 class="seccion">👤 Datos personales</h2>

                <div class="campo">
                    <label for="documento">Documento</label>
                    <input type="text" id="documento" name="documento"
                           value="<?= htmlspecialchars($persona['documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                </div>

                <div class="campo">
                    <label for="nombres">Nombres</label>
                    <input type="text" id="nombres" name="nombres"
                           value="<?= htmlspecialchars($persona['nombres'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                </div>

                <div class="campo">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos"
                           value="<?= htmlspecialchars($persona['apellidos'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                </div>

                <div class="campo">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono"
                           value="<?= htmlspecialchars($persona['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="campo">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo"
                           value="<?= htmlspecialchars($persona['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <h2 class="seccion">🔐 Datos de acceso</h2>

                <div class="campo">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario"
                           value="<?= htmlspecialchars($persona['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                    <p class="ayuda">
                        La contraseña actual se conserva.
                    </p>
                </div>

                <div class="campo">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" required>
                        <option value="Activo"
                            <?= ($persona['estado'] ?? '') === 'Activo' ? 'selected' : '' ?>>
                            Activo
                        </option>
                        <option value="Inactivo"
                            <?= ($persona['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>
                            Inactivo
                        </option>
                    </select>
                </div>

                <div class="botones">
                    <button type="submit" class="boton">
                        💾 Guardar cambios
                    </button>

                    <a href="listar.php" class="boton cancelar">
                        ✖ Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</main>

</body>
</html>
```