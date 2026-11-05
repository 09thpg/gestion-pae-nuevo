<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}

include("database/conexion.php");


/* ==========================================
   COMPROBAR ROL
   ========================================== */

if ($_SESSION["id_rol"] != 1) {
    header("Location: login.php");
    exit();
}


/* ==========================================
   CONTAR ESTUDIANTES
   ========================================== */

$sql = "SELECT COUNT(*) AS total FROM estudiantes WHERE estado = 'Activo'";

$resultado = $conexion->query($sql);

$estudiantes = 0;

if ($resultado) {
    $datos = $resultado->fetch_assoc();
    $estudiantes = $datos["total"];
}


/* ==========================================
   CONTAR PROFESORES
   ========================================== */

$sql = "SELECT COUNT(*) AS total FROM profesores WHERE estado = 'Activo'";

$resultado = $conexion->query($sql);

$profesores = 0;

if ($resultado) {
    $datos = $resultado->fetch_assoc();
    $profesores = $datos["total"];
}


/* ==========================================
   CONTAR PERSONAL PAE
   ========================================== */

$sql = "SELECT COUNT(*) AS total FROM personal_pae WHERE estado = 'Activo'";

$resultado = $conexion->query($sql);

$personal = 0;

if ($resultado) {
    $datos = $resultado->fetch_assoc();
    $personal = $datos["total"];
}


/* ==========================================
   CONTAR ENTREGAS DE HOY
   ========================================== */

$sql = "
    SELECT COUNT(*) AS total
    FROM entregas_pae
    WHERE fecha = CURDATE()
    AND estado = 'Terminado'
";

$resultado = $conexion->query($sql);

$entregas = 0;

if ($resultado) {
    $datos = $resultado->fetch_assoc();
    $entregas = $datos["total"];
}


/* ==========================================
   CALCULAR PORCENTAJE
   ========================================== */

$porcentaje = 0;
$recibidos = 0;

$sql_recibidos = "
    SELECT COUNT(DISTINCT d.id_estudiante) AS total
    FROM detalle_entrega d
    INNER JOIN entregas_pae e
        ON d.id_entrega = e.id_entrega
    WHERE e.fecha = CURDATE()
    AND e.estado = 'Terminado'
    AND d.recibido = 1
";

$resultado_recibidos = $conexion->query($sql_recibidos);

if ($resultado_recibidos) {
    $datos_recibidos = $resultado_recibidos->fetch_assoc();
    $recibidos = (int) $datos_recibidos["total"];
}

if ($estudiantes > 0) {
    $porcentaje = ($recibidos / $estudiantes) * 100;

    if ($porcentaje > 100) {
        $porcentaje = 100;
    }
}


/* ==========================================
   FECHA Y HORA
   ========================================== */

date_default_timezone_set("America/Bogota");

$horaActual = date("H");

$fecha = date("d/m/Y");

$hora = date("g:i:s a");


if ($horaActual < 12) {

    $saludo = "Buenos días";

} elseif ($horaActual < 18) {

    $saludo = "Buenas tardes";

} else {

    $saludo = "Buenas noches";

}

?>


<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - Gestión PAE</title>


    <style>

        /* ==========================================
           GENERAL
           ========================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }


        body {

            min-height: 100vh;

            background: linear-gradient(
                135deg,
                #eef3ff,
                #fdfcff
            );

        }


        /* ==========================================
           CONTENIDO
           ========================================== */

        .contenido-dashboard {

            min-height: 100vh;

            width: 100%;

            padding: 80px 40px 50px;

        }


        /* ==========================================
           ENCABEZADO
           ========================================== */

        .encabezado {

            max-width: 1100px;

            margin: 0 auto 40px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 30px;

        }


        .bienvenida h1 {

            color: #6d3bd1;

            font-size: 32px;

            line-height: 1.2;

            margin-bottom: 10px;

        }


        .bienvenida p {

            color: #666;

            font-size: 17px;

        }


        /* ==========================================
           USUARIO
           ========================================== */

        .usuario {

            background: white;

            min-width: 190px;

            padding: 18px 25px;

            border-radius: 18px;

            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.08);

            text-align: center;

        }


        .usuario strong {

            display: block;

            color: #333;

            margin-bottom: 8px;

        }


        .usuario span {

            display: block;

            color: #666;

            font-size: 14px;

            margin-top: 5px;

        }


        /* ==========================================
           TARJETAS
           ========================================== */

        .tarjetas {

            max-width: 1100px;

            margin: 0 auto;

            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 25px;

        }


        .tarjeta {

            min-height: 230px;

            background: white;

            padding: 25px;

            border-radius: 20px;

            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.10);

            text-align: center;

            text-decoration: none;

            color: inherit;

            display: flex;

            flex-direction: column;

            justify-content: center;

            align-items: center;

            transition: 0.3s;

        }


        .tarjeta:hover {

            transform: translateY(-7px);

            box-shadow:
                0 15px 35px rgba(0, 0, 0, 0.15);

        }


        /* ==========================================
           BORDES DE LAS TARJETAS
           ========================================== */

        .tarjeta:nth-child(1) {

            border-top: 6px solid #4f8dfd;

        }


        .tarjeta:nth-child(2) {

            border-top: 6px solid #ff9800;

        }


        .tarjeta:nth-child(3) {

            border-top: 6px solid #4caf50;

        }


        .tarjeta:nth-child(4) {

            border-top: 6px solid #9c27b0;

        }


        /* ==========================================
           ICONOS
           ========================================== */

        .icono {

            font-size: 48px;

            margin-bottom: 12px;

        }


        /* ==========================================
           TITULO
           ========================================== */

        .tarjeta h2 {

            color: #6d3bd1;

            font-size: 19px;

            margin-bottom: 8px;

        }


        /* ==========================================
           NUMERO
           ========================================== */

        .numero {

            color: #6d3bd1;

            font-size: 45px;

            font-weight: bold;

            margin: 5px 0 10px;

        }


        /* ==========================================
           DESCRIPCION
           ========================================== */

        .tarjeta p {

            color: #777;

            font-size: 15px;

        }


        /* ==========================================
           BALANCE
           ========================================== */

        .balance {

            max-width: 1100px;

            margin: 35px auto 0;

            background: white;

            padding: 25px;

            border-radius: 20px;

            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.08);

        }


        .balance h2 {

            color: #6d3bd1;

            margin-bottom: 10px;

        }


        .balance p {

            color: #777;

            margin-bottom: 20px;

        }


        .barra {

            width: 100%;

            height: 18px;

            background: #eeeeee;

            border-radius: 20px;

            overflow: hidden;

        }


        .barra-progreso {

            height: 100%;

            background: linear-gradient(
                90deg,
                #8c52ff,
                #00d4ff
            );

            border-radius: 20px;

        }


        .balance-datos {

            display: flex;

            justify-content: space-between;

            margin-top: 10px;

            color: #666;

        }


        /* ==========================================
           RESPONSIVE
           ========================================== */

        
/* RESPONSIVE: TABLETAS */

@media (max-width: 950px) {

    .contenido-dashboard {
        padding: 60px 25px 35px;
    }

    .encabezado {
        gap: 20px;
    }

    .tarjetas {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .tarjeta {
        min-width: 0;
    }

    .usuario {
        min-width: 170px;
    }

}

/* RESPONSIVE: CELULARES */

@media (max-width: 650px) {

    .contenido-dashboard {
        padding: 30px 15px;
        min-width: 0;
    }

    .encabezado {
        flex-direction: column;
        align-items: stretch;
        gap: 20px;
        margin-bottom: 25px;
    }

    .bienvenida h1 {
        font-size: 27px;
        overflow-wrap: anywhere;
    }

    .bienvenida p {
        font-size: 15px;
        line-height: 1.5;
    }

    .usuario {
        width: 100%;
        min-width: 0;
        padding: 15px;
    }

    .tarjetas {
        grid-template-columns: minmax(0, 1fr);
        gap: 18px;
    }

    .tarjeta {
        width: 100%;
        min-height: 190px;
        padding: 22px 16px;
    }

    .balance {
        padding: 20px 16px;
        margin-top: 25px;
    }

    .balance h2 {
        font-size: 21px;
        line-height: 1.4;
    }

    .balance-datos {
        gap: 12px;
        flex-wrap: wrap;
        font-size: 14px;
    }

}

/* CELULARES MUY ESTRECHOS */

@media (max-width: 380px) {

    .contenido-dashboard {
        padding: 25px 10px;
    }

    .bienvenida h1 {
        font-size: 24px;
    }

    .tarjeta h2 {
        font-size: 17px;
    }

    .numero {
        font-size: 38px;
    }

}


    </style>

</head>


<body>


<?php include("componentes/sidebar.php"); ?>


<main class="contenido-dashboard">


    <!-- ==========================================
         ENCABEZADO
         ========================================== -->

    <section class="encabezado">


        <div class="bienvenida">

            <h1>

                🌤️ <?php echo $saludo; ?>,
                <br>
                Administrador

            </h1>


            <p>

                Sistema de Gestión del Programa
                de Alimentación Escolar

            </p>

        </div>


        <div class="usuario">

            <strong>

                👤
                <?php
                echo htmlspecialchars($_SESSION["usuario"]);
                ?>

            </strong>


            <span>

                📅 <?php echo $fecha; ?>

            </span>



                
<span id="reloj">🕐 Cargando hora...</span>



        </div>


    </section>


    <!-- ==========================================
         TARJETAS
         ========================================== -->

    <section class="tarjetas">


        <!-- ESTUDIANTES -->

        <a
            href="estudiantes/listar.php"
            class="tarjeta"
        >

            <div class="icono">
                🎓
            </div>


            <h2>
                Estudiantes
            </h2>


            <div class="numero">

                <?php echo $estudiantes; ?>

            </div>


            <p>
                Estudiantes registrados
            </p>

        </a>


        <!-- REGISTROS PAE -->

        <a
            href="registros_pae/reportes.php"
            class="tarjeta"
        >

            <div class="icono">
                🍽️
            </div>


            <h2>
                Registros PAE
            </h2>


            <div class="numero">

                <?php echo $entregas; ?>

            </div>


            <p>
                Entregas registradas hoy
            </p>

        </a>


        <!-- PROFESORES -->

        <a
            href="profesores/listar.php"
            class="tarjeta"
        >

            <div class="icono">
                👨‍🏫
            </div>


            <h2>
                Profesores
            </h2>


            <div class="numero">

                <?php echo $profesores; ?>

            </div>


            <p>
                Profesores registrados
            </p>

        </a>


        <!-- PERSONAL PAE -->

        <a
            href="personal_pae/listar.php"
            class="tarjeta"
        >

            <div class="icono">
                👩‍🍳
            </div>


            <h2>
                Personal PAE
            </h2>


            <div class="numero">

                <?php echo $personal; ?>

            </div>


            <p>
                Personal registrado
            </p>

        </a>


    </section>


    <!-- ==========================================
         BALANCE
         ========================================== -->

    <section class="balance">

        <h2>
            📊 Balance de alimentación
        </h2>


        <p>
            Entregas de alimentación registradas
            durante el día.
        </p>


        <div class="barra">

            <div
                class="barra-progreso"
                style="width: <?php echo $porcentaje; ?>%;"
            ></div>

        </div>


        <div class="balance-datos">

            <span>

                🍽️
                <?php echo $recibidos; ?> estudiantes atendidos

            </span>


            <span>

                🎓
                <?php echo $estudiantes; ?>
                estudiantes

            </span>

        </div>

    </section>


</main>

<script>
    function actualizarReloj() {
        const ahora = new Date();

        const hora = ahora.toLocaleTimeString('es-CO', {
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
            timeZone: 'America/Bogota'
        });

        document.getElementById('reloj').textContent = '🕐 ' + hora;
    }

    actualizarReloj();
    setInterval(actualizarReloj, 1000);
</script>


</body>

</html>