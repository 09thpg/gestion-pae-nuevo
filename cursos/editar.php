<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["id_rol"] != 1) {
    header("Location: ../estudiantes/perfil.php");
    exit();
}

require_once "../database/conexion.php";

$id_curso = $_GET["id"] ?? "";

if ($id_curso === "" || !is_numeric($id_curso)) {
    header("Location: listar.php");
    exit();
}

try {

    // Buscar el grupo
    $sql = "SELECT id_curso, nombre, id_jornada
            FROM cursos
            WHERE id_curso = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        header("Location: listar.php");
        exit();
    }

    $curso = $resultado->fetch_assoc();

    $stmt->close();


    // Obtener jornadas
    $sql = "SELECT id_jornada, nombre
            FROM jornadas
            ORDER BY nombre ASC";

    $resultado = $conexion->query($sql);

    $jornadas = [];

    if ($resultado) {

        while ($fila = $resultado->fetch_assoc()) {
            $jornadas[] = $fila;
        }

    }

} catch (Exception $e) {

    echo "❌ Error: " . $e->getMessage();
    exit();

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar grupo | Gestión PAE</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f3ff;
        }

        .contenido {
            margin-left: 250px;
            padding: 40px;
        }

        .contenedor-formulario {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #6c3fd1;
            margin-bottom: 10px;
        }

        .descripcion {
            color: #666;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 18px;
            margin-bottom: 7px;
            font-weight: bold;
            color: #444;
        }

        input,
        select {
            width: 100%;
            padding: 13px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 15px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #7b4de2;
        }

        .botones {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .boton {
            border: none;
            padding: 13px 20px;
            border-radius: 10px;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
        }

        .guardar {
            background: #7b4de2;
            color: white;
            flex: 1;
        }

        .guardar:hover {
            background: #6335bd;
        }

        .volver {
            background: #e9e5f8;
            color: #55369b;
        }

        .volver:hover {
            background: #ddd5f5;
        }

    </style>

</head>

<body>

    <?php include "../componentes/sidebar.php"; ?>

    <main class="contenido">

        <div class="contenedor-formulario">

            <h1>✏️ Editar grupo</h1>

            <p class="descripcion">
                Modifica el nombre o la jornada del grupo.
            </p>

            <form action="actualizar.php" method="POST">

                <input
                    type="hidden"
                    name="id_curso"
                    value="<?php echo $curso["id_curso"]; ?>"
                >

                <label for="nombre">
                    Nombre del grupo
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="<?php echo htmlspecialchars($curso["nombre"]); ?>"
                    maxlength="50"
                    required
                >

                <label for="id_jornada">
                    Jornada
                </label>

                <select
                    id="id_jornada"
                    name="id_jornada"
                    required
                >

                    <option value="">
                        Selecciona una jornada
                    </option>

                    <?php foreach ($jornadas as $jornada): ?>

                        <option
                            value="<?php echo $jornada["id_jornada"]; ?>"
                            <?php
                            if ($jornada["id_jornada"] == $curso["id_jornada"]) {
                                echo "selected";
                            }
                            ?>
                        >

                            <?php echo htmlspecialchars($jornada["nombre"]); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <div class="botones">

                    <a
                        href="listar.php"
                        class="boton volver">
                        ↩️ Cancelar
                    </a>

                    <button
                        type="submit"
                        class="boton guardar">
                        💾 Guardar cambios
                    </button>

                </div>

            </form>

        </div>

    </main>

</body>

</html>