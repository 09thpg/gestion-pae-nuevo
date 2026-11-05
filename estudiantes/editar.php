
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ((int) $_SESSION["id_rol"] !== 1) {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../database/conexion.php";

function e($valor) {
    return htmlspecialchars((string)($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$id_estudiante = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_estudiante || $id_estudiante < 1) {
    header("Location: listar.php");
    exit();
}

$sql = "
    SELECT
        e.id_estudiante,
        e.documento,
        e.tipo_documento,
        e.nombres,
        e.apellidos,
        e.fecha_nacimiento,
        e.sexo,
        e.correo,
        e.foto,
        e.beneficiario,
        e.estado,
        e.id_curso,
        e.id_usuario
    FROM estudiantes e
    WHERE e.id_estudiante = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    header("Location: listar.php");
    exit();
}

$estudiante = $resultado->fetch_assoc();

$sql_cursos = "
    SELECT
        c.id_curso,
        c.nombre AS nombre_curso,
        j.nombre AS nombre_jornada
    FROM cursos c
    INNER JOIN jornadas j
        ON c.id_jornada = j.id_jornada
    ORDER BY c.nombre ASC
";

$resultado_cursos = $conexion->query($sql_cursos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar estudiante - Gestión PAE</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f4f1fc;
            color: #333;
        }

        .contenedor {
            max-width: 900px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 22px rgba(0,0,0,.08);
        }

        .encabezado {
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 15px;
            color: white;
            background: linear-gradient(135deg, #7650d6, #19add1);
        }

        .encabezado h1 {
            margin: 0 0 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .campo label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .campo input,
        .campo select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d8d0e8;
            border-radius: 9px;
            font-size: 15px;
            background: white;
        }

        .foto-actual {
            display: block;
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 14px;
            margin-bottom: 12px;
            border: 2px solid #e3d9fa;
        }

        .ayuda {
            display: block;
            margin-top: 7px;
            color: #777;
            font-size: 13px;
        }

        .acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .boton {
            display: inline-block;
            padding: 12px 20px;
            border: none;
            border-radius: 9px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            font-size: 15px;
        }

        .guardar {
            background: #7650d6;
            color: white;
        }

        .volver {
            background: #eee8f8;
            color: #5b2c83;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .contenedor {
                padding: 18px;
                margin: 10px auto;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .encabezado {
                padding: 18px;
            }

            .boton {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
<div class="contenedor">

    <div class="encabezado">
        <h1>✏️ Editar estudiante</h1>
        <p>Actualiza la información del estudiante registrado.</p>
    </div>

    <form action="actualizar.php" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="id_estudiante"
               value="<?= (int)$estudiante['id_estudiante'] ?>">

        <div class="grid">

            <div class="campo">
                <label for="tipo_documento">Tipo de documento</label>
                <select id="tipo_documento" name="tipo_documento" required>
                    <?php
                    $tipos = [
                        "TI" => "Tarjeta de identidad",
                        "CC" => "Cédula de ciudadanía",
                        "CE" => "Cédula de extranjería"
                    ];

                    foreach ($tipos as $valor => $texto):
                    ?>
                        <option value="<?= e($valor) ?>"
                            <?= $estudiante["tipo_documento"] === $valor
                                ? "selected" : "" ?>>
                            <?= e($texto) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="documento">Número de documento</label>
                <input type="text" id="documento" name="documento"
                       value="<?= e($estudiante['documento']) ?>" required>
            </div>

            <div class="campo">
                <label for="nombres">Nombres</label>
                <input type="text" id="nombres" name="nombres"
                       value="<?= e($estudiante['nombres']) ?>" required>
            </div>

            <div class="campo">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos"
                       value="<?= e($estudiante['apellidos']) ?>" required>
            </div>

            <div class="campo">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" id="fecha_nacimiento"
                       name="fecha_nacimiento"
                       value="<?= e($estudiante['fecha_nacimiento']) ?>">
            </div>

            <div class="campo">
                <label for="sexo">Sexo</label>
                <select id="sexo" name="sexo">
                    <option value="">Selecciona</option>
                    <option value="F" <?= $estudiante["sexo"] === "F" ? "selected" : "" ?>>Femenino</option>
                    <option value="M" <?= $estudiante["sexo"] === "M" ? "selected" : "" ?>>Masculino</option>
                </select>
            </div>

            <div class="campo">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo"
                       value="<?= e($estudiante['correo']) ?>">
            </div>

            <div class="campo">
                <label for="id_curso">Curso o grupo</label>
                <select id="id_curso" name="id_curso" required>
                    <?php while ($curso = $resultado_cursos->fetch_assoc()): ?>
                        <option value="<?= (int)$curso['id_curso'] ?>"
                            <?= (int)$estudiante['id_curso'] === (int)$curso['id_curso']
                                ? "selected" : "" ?>>
                            <?= e($curso['nombre_curso']) ?>
                            - <?= e($curso['nombre_jornada']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="campo">
                <label for="beneficiario">Beneficiario del PAE</label>
                <select id="beneficiario" name="beneficiario" required>
                    <option value="1" <?= (int)$estudiante['beneficiario'] === 1 ? "selected" : "" ?>>Sí</option>
                    <option value="0" <?= (int)$estudiante['beneficiario'] === 0 ? "selected" : "" ?>>No</option>
                </select>
            </div>

            
<div class="campo">
    <label for="estado">Estado del estudiante</label>

    <select id="estado" name="estado" required>
        <option value="Activo"
            <?= $estudiante["estado"] === "Activo" ? "selected" : "" ?>>
            Activo
        </option>

        <option value="Inactivo"
            <?= $estudiante["estado"] === "Inactivo" ? "selected" : "" ?>>
            Inactivo
        </option>
    </select>
</div>


            <div class="campo">
                <label for="foto">Fotografía</label>

                <?php if (!empty($estudiante["foto"])): ?>
                    <img class="foto-actual"
                         src="../uploads/<?= rawurlencode(basename($estudiante['foto'])) ?>"
                         alt="Foto actual del estudiante">
                <?php else: ?>
                    <p>No hay fotografía registrada.</p>
                <?php endif; ?>

                <input type="file" id="foto" name="foto"
                       accept=".jpg,.jpeg,.png,.webp">
                <span class="ayuda">Déjalo vacío para conservar la foto actual.</span>
            </div>

        </div>

        <div class="acciones">
            <button type="submit" class="boton guardar">
                💾 Guardar cambios
            </button>

            <a href="listar.php" class="boton volver">
                ← Volver a estudiantes
            </a>
        </div>

    </form>
</div>
</body>
</html>
