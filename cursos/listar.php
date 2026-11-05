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

$mensaje = $_GET["mensaje"] ?? "";

// Obtener todos los grupos
$sql = "SELECT 
            c.id_curso,
            c.nombre,
            j.nombre AS jornada
        FROM cursos c
        INNER JOIN jornadas j 
            ON c.id_jornada = j.id_jornada
        ORDER BY c.nombre ASC";

$resultado = $conexion->query($sql);

$cursos = [];

if ($resultado) {

    while ($fila = $resultado->fetch_assoc()) {
        $cursos[] = $fila;
    }

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cursos y grupos | Gestión PAE</title>

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

        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .encabezado h1 {
            margin: 0;
            color: #6335bd;
        }

        .encabezado p {
            color: #666;
            margin-top: 8px;
        }

        .boton-agregar {
            background: #7b4de2;
            color: white;
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 10px;
            font-weight: bold;
        }

        .boton-agregar:hover {
            background: #6335bd;
        }

        .mensaje {
            background: #e7f8ee;
            color: #267044;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .contenedor-tabla {
            background: white;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #eee8ff;
            color: #55369b;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #444;
        }

        tr:hover {
            background: #faf9ff;
        }

        .acciones {
            display: flex;
            gap: 8px;
        }

        .boton {
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .editar {
            background: #e5f4ff;
            color: #17628a;
        }

        .eliminar {
            background: #ffe7e7;
            color: #a52d2d;
        }

        .sin-datos {
            text-align: center;
            padding: 35px;
            color: #777;
        }

        @media (max-width: 768px) {

            .contenido {
                margin-left: 0;
                padding: 20px;
            }

            .encabezado {
                flex-direction: column;
                align-items: flex-start;
            }

            .boton-agregar {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
            }

        }

    </style>

</head>

<body>

    <?php include "../componentes/sidebar.php"; ?>

    <main class="contenido">

        <div class="encabezado">

            <div>

                <h1>🏫 Cursos / Grupos</h1>

                <p>
                    Administra los grupos y cursos registrados en el sistema.
                </p>

            </div>

            <a
                href="registrar.php"
                class="boton-agregar">
                ➕ Agregar grupo
            </a>

        </div>


        <?php if ($mensaje != ""): ?>

            <div class="mensaje">

                ✅ <?php echo htmlspecialchars($mensaje); ?>

            </div>

        <?php endif; ?>


        <div class="contenedor-tabla">

            <table>

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Grupo</th>

                        <th>Jornada</th>

                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (count($cursos) > 0): ?>

                        <?php foreach ($cursos as $curso): ?>

                            <tr>

                                <td>
                                    <?php echo $curso["id_curso"]; ?>
                                </td>

                                <td>
                                    🏫
                                    <?php echo htmlspecialchars($curso["nombre"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($curso["jornada"]); ?>
                                </td>

                                <td>

                                    <div class="acciones">

                                        <a
                                            href="editar.php?id=<?php echo $curso["id_curso"]; ?>"
                                            class="boton editar">
                                            ✏️ Editar
                                        </a>

                                        <a
                                            href="eliminar.php?id=<?php echo $curso["id_curso"]; ?>"
                                            class="boton eliminar"
                                            onclick="return confirm('¿Seguro que deseas eliminar este grupo?');">
                                            🗑️ Eliminar
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="4" class="sin-datos">

                                🏫 No hay grupos registrados todavía.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </main>

</body>

</html>