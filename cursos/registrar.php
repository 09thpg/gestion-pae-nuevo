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

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Agregar grupo | Gestión PAE</title>

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

            <h1>🏫 Agregar grupo</h1>

            <p class="descripcion">
                Crea un nuevo grupo o curso para que aparezca en el sistema de Gestión PAE.
            </p>

            <form action="guardar.php" method="POST">

                <label for="nombre">
                    Nombre del grupo
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Ejemplo: 11°A"
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

                        <option value="<?php echo $jornada["id_jornada"]; ?>">

                            <?php echo htmlspecialchars($jornada["nombre"]); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <div class="botones">

                    <a
                        href="listar.php"
                        class="boton volver">
                        ↩️ Volver
                    </a>

                    <button
                        type="submit"
                        class="boton guardar">
                        ➕ Agregar grupo
                    </button>

                </div>

            </form>

        </div>

    </main>

</body>

</html>