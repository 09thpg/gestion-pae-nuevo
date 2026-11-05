<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>¿Qué es el PAE? | Gestión PAE</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f7f8fc;
            color: #30283d;
        }


        /* ==============================
           NAVBAR
        ============================== */

        header {
            width: 100%;
            background: white;

            position: sticky;
            top: 0;
            z-index: 1000;

            box-shadow: 0 3px 15px rgba(60, 40, 90, 0.10);
        }

        .navbar {
            max-width: 1200px;
            min-height: 75px;

            margin: auto;
            padding: 0 25px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;
        }

        .logo {
            text-decoration: none;

            display: flex;
            align-items: center;
            gap: 10px;

            color: #56308c;

            font-size: 21px;
            font-weight: bold;
        }

        .logo-icon {
            width: 43px;
            height: 43px;

            border-radius: 13px;

            background: linear-gradient(
                135deg,
                #713cb4,
                #438ec2
            );

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 22px;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 5px;

            flex-wrap: wrap;
            justify-content: center;
        }

        .menu a {
            text-decoration: none;

            color: #5d5767;

            font-size: 13px;
            font-weight: 600;

            padding: 10px 12px;

            border-radius: 9px;

            transition: 0.3s;
        }

        .menu a:hover {
            background: #f0e9fa;
            color: #65379a;
        }

        .menu .active {
            background: #eee3fa;
            color: #65379a;
        }

        .menu .login {
            color: white;

            background: linear-gradient(
                135deg,
                #65379a,
                #4387c1
            );
        }

        .menu .login:hover {
            color: white;
        }


        /* ==============================
           ENCABEZADO
        ============================== */

        .page-header {
            padding: 75px 25px;

            background:
                radial-gradient(
                    circle at 15% 30%,
                    rgba(111, 69, 178, 0.18),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 85% 70%,
                    rgba(50, 175, 183, 0.15),
                    transparent 30%
                ),
                #f8f7fc;

            text-align: center;
        }

        .page-icon {
            width: 90px;
            height: 90px;

            margin: 0 auto 20px;

            border-radius: 28px;

            background: linear-gradient(
                135deg,
                #eadcf8,
                #d8f1f0
            );

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 45px;
        }

        .page-header h1 {
            color: #432761;

            font-size: 43px;

            margin-bottom: 15px;
        }

        .page-header p {
            max-width: 700px;

            margin: auto;

            color: #6d6878;

            line-height: 1.7;

            font-size: 17px;
        }


        /* ==============================
           CONTENIDO PRINCIPAL
        ============================== */

        .content {
            max-width: 1050px;

            margin: auto;

            padding: 70px 25px;
        }


        /* ==============================
           BLOQUE PRINCIPAL
        ============================== */

        .main-info {
            background: white;

            padding: 40px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px rgba(60, 40, 90, 0.08);

            margin-bottom: 30px;
        }

        .main-info h2 {
            color: #4d2c70;

            margin-bottom: 20px;

            font-size: 27px;
        }

        .main-info p {
            color: #686373;

            line-height: 1.8;

            margin-bottom: 15px;
        }

        .main-info strong {
            color: #57318a;
        }


        /* ==============================
           TARJETAS
        ============================== */

        .cards {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-top: 30px;
        }

        .card {
            padding: 30px 25px;

            border-radius: 18px;

            background: white;

            box-shadow:
                0 8px 25px rgba(60, 40, 90, 0.07);

            border-top: 5px solid #7040b5;
        }

        .card:nth-child(2) {
            border-top-color: #4387c1;
        }

        .card:nth-child(3) {
            border-top-color: #35a49c;
        }

        .card-icon {
            font-size: 32px;

            margin-bottom: 15px;
        }

        .card h3 {
            color: #4d2c70;

            margin-bottom: 10px;
        }

        .card p {
            color: #716c7d;

            line-height: 1.6;

            font-size: 14px;
        }


        /* ==============================
           IMPORTANCIA
        ============================== */

        .importance {
            margin-top: 35px;

            padding: 35px;

            border-radius: 20px;

            background:
                linear-gradient(
                    120deg,
                    #f2eafa,
                    #eaf7f6
                );
        }

        .importance h2 {
            color: #432761;

            margin-bottom: 18px;
        }

        .importance p {
            color: #625d6e;

            line-height: 1.8;
        }


        /* ==============================
           BOTONES
        ============================== */

        .navigation-buttons {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-top: 45px;

            gap: 15px;

            flex-wrap: wrap;
        }

        .button {
            display: inline-block;

            text-decoration: none;

            padding: 13px 21px;

            border-radius: 10px;

            font-weight: bold;

            transition: 0.3s;
        }

        .back-button {
            background: white;

            color: #60348f;

            border: 1px solid #ddd4e8;
        }

        .back-button:hover {
            background: #f1ebf8;
        }

        .next-button {
            background: linear-gradient(
                135deg,
                #65379a,
                #4387c1
            );

            color: white;
        }

        .next-button:hover {
            transform: translateY(-2px);

            box-shadow:
                0 6px 15px rgba(80, 50, 130, 0.20);
        }


        /* ==============================
           FOOTER
        ============================== */

        footer {
            background: #292239;

            color: white;

            padding: 35px 25px;

            text-align: center;
        }

        footer h3 {
            margin-bottom: 8px;
        }

        footer p {
            color: #c8c3d0;

            font-size: 13px;

            margin: 5px;
        }


        /* ==============================
           RESPONSIVE
        ============================== */

        @media (max-width: 900px) {

            .navbar {
                flex-direction: column;

                padding: 15px;
            }

            .menu {
                width: 100%;
            }

            .cards {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 600px) {

            .menu a {
                font-size: 11px;

                padding: 8px;
            }

            .page-header {
                padding: 55px 20px;
            }

            .page-header h1 {
                font-size: 34px;
            }

            .page-header p {
                font-size: 15px;
            }

            .content {
                padding: 50px 20px;
            }

            .main-info {
                padding: 25px;
            }

            .importance {
                padding: 25px;
            }

            .navigation-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .button {
                text-align: center;
            }

        }

    </style>

</head>


<body>


<!-- =====================================
     NAVBAR
===================================== -->

<header>

    <nav class="navbar">

        <a href="../index.php" class="logo">

            <span class="logo-icon">
                🍎
            </span>

            Gestión PAE

        </a>


        <div class="menu">

            <a href="../index.php">
                🏠 Inicio
            </a>

            <a
                href="que_es_pae.php"
                class="active"
            >
                🍎 ¿Qué es?
            </a>

            <a href="beneficiarios.php">
                👨‍🎓 Beneficiarios
            </a>

            <a href="como_funciona.php">
                ⚙️ ¿Cómo funciona?
            </a>

            <a href="para_que_sirve.php">
                🎯 ¿Para qué sirve?
            </a>

            <a href="../contacto/contacto.php">
                📱 Contacto
            </a>

            <a
                href="../login.php"
                class="login"
            >
                🔐 Registrar
            </a>

        </div>

    </nav>

</header>


<!-- =====================================
     ENCABEZADO
===================================== -->

<section class="page-header">

    <div class="page-icon">
        🍎
    </div>

    <h1>
        ¿Qué es el PAE?
    </h1>

    <p>
        Conoce de manera sencilla qué es el Programa de Alimentación
        Escolar y cuál es su importancia dentro de la comunidad educativa.
    </p>

</section>


<!-- =====================================
     CONTENIDO
===================================== -->

<main class="content">


    <!-- INFORMACIÓN PRINCIPAL -->

    <div class="main-info">

        <h2>
            🍎 Programa de Alimentación Escolar
        </h2>

        <p>
            El <strong>Programa de Alimentación Escolar (PAE)</strong>
            es una estrategia orientada a apoyar el acceso a la
            alimentación de los estudiantes durante su jornada escolar.
        </p>

        <p>
            La alimentación durante la jornada educativa puede contribuir
            al bienestar de los estudiantes y favorecer condiciones
            adecuadas para el desarrollo de sus actividades escolares.
        </p>

        <p>
            Dentro de este proceso participan diferentes personas de la
            comunidad educativa, quienes apoyan la organización y
            seguimiento de la entrega de los alimentos.
        </p>

    </div>


    <!-- TARJETAS -->

    <div class="cards">


        <div class="card">

            <div class="card-icon">
                🍽️
            </div>

            <h3>
                Alimentación
            </h3>

            <p>
                Apoya el acceso de los estudiantes a alimentos durante
                su jornada escolar, de acuerdo con la organización
                establecida por la institución.
            </p>

        </div>


        <div class="card">

            <div class="card-icon">
                👨‍🎓
            </div>

            <h3>
                Bienestar estudiantil
            </h3>

            <p>
                Busca contribuir a mejores condiciones para que los
                estudiantes desarrollen sus actividades durante la
                jornada educativa.
            </p>

        </div>


        <div class="card">

            <div class="card-icon">
                🏫
            </div>

            <h3>
                Comunidad educativa
            </h3>

            <p>
                Involucra la organización y participación de diferentes
                integrantes de la institución educativa.
            </p>

        </div>


    </div>


    <!-- IMPORTANCIA -->

    <div class="importance">

        <h2>
            💜 ¿Por qué es importante?
        </h2>

        <p>
            La alimentación escolar forma parte de las acciones que
            buscan apoyar el bienestar de los estudiantes durante su
            permanencia en la institución educativa.
        </p>

        <br>

        <p>
            Por esta razón, es importante contar con procesos organizados
            que permitan registrar la entrega de los alimentos y mantener
            información disponible para su consulta.
        </p>

        <br>

        <p>
            En este punto entra <strong>Gestión PAE</strong>, una
            herramienta diseñada para facilitar la organización,
            registro y consulta de esta información.
        </p>

    </div>


    <!-- BOTONES -->

    <div class="navigation-buttons">

        <a
            href="../index.php"
            class="button back-button"
        >
            ← Volver al inicio
        </a>


        <a
            href="beneficiarios.php"
            class="button next-button"
        >
            Siguiente: Beneficiarios →
        </a>

    </div>


</main>


<!-- =====================================
     FOOTER
===================================== -->

<footer>

    <h3>
        🍎 Gestión PAE
    </h3>

    <p>
        Programa de Alimentación Escolar
    </p>

    <p>
        Sistema de gestión y seguimiento de alimentación escolar.
    </p>

</footer>


</body>

</html>