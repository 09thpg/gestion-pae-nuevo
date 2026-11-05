<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>¿Para qué sirve? | Gestión PAE</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            background: #fff2d4;
            color: #9a6b18;
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
                    rgba(232, 178, 72, 0.18),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 85% 70%,
                    rgba(111, 69, 178, 0.14),
                    transparent 30%
                ),
                #fbfaf6;

            text-align: center;
        }

        .page-icon {
            width: 90px;
            height: 90px;

            margin: 0 auto 20px;

            border-radius: 28px;

            background: linear-gradient(
                135deg,
                #fff2d4,
                #eee3fa
            );

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 45px;
        }

        .page-header h1 {
            color: #654c27;

            font-size: 43px;

            margin-bottom: 15px;
        }

        .page-header p {
            max-width: 720px;

            margin: auto;

            color: #716d65;

            line-height: 1.7;

            font-size: 17px;
        }


        /* ==============================
           CONTENIDO
        ============================== */

        .content {
            max-width: 1100px;

            margin: auto;

            padding: 70px 25px;
        }


        /* ==============================
           INTRODUCCIÓN
        ============================== */

        .intro {
            background: white;

            padding: 40px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px rgba(60, 40, 90, 0.08);

            margin-bottom: 40px;
        }

        .intro h2 {
            color: #5c4727;

            font-size: 27px;

            margin-bottom: 18px;
        }

        .intro p {
            color: #6b6862;

            line-height: 1.8;

            margin-bottom: 12px;
        }

        .intro strong {
            color: #72551f;
        }


        /* ==============================
           FUNCIONES
        ============================== */

        .functions {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 22px;
        }

        .function {
            background: white;

            border-radius: 20px;

            padding: 30px;

            box-shadow:
                0 8px 25px rgba(60, 40, 90, 0.07);

            transition: 0.3s;

            border-left: 5px solid #7040b5;
        }

        .function:nth-child(2) {
            border-left-color: #4387c1;
        }

        .function:nth-child(3) {
            border-left-color: #35a49c;
        }

        .function:nth-child(4) {
            border-left-color: #e1aa3c;
        }

        .function:hover {
            transform: translateY(-5px);

            box-shadow:
                0 12px 28px rgba(60, 40, 90, 0.11);
        }

        .function-icon {
            width: 58px;
            height: 58px;

            border-radius: 17px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 29px;

            margin-bottom: 17px;

            background: #f1eafa;
        }

        .function:nth-child(2) .function-icon {
            background: #e1eff9;
        }

        .function:nth-child(3) .function-icon {
            background: #def3ee;
        }

        .function:nth-child(4) .function-icon {
            background: #fff2d4;
        }

        .function h3 {
            color: #4d2c70;

            margin-bottom: 10px;
        }

        .function p {
            color: #707078;

            line-height: 1.6;

            font-size: 14px;
        }


        /* ==============================
           VENTAJAS
        ============================== */

        .advantages {
            margin-top: 40px;

            padding: 35px;

            border-radius: 20px;

            background:
                linear-gradient(
                    120deg,
                    #f2eafa,
                    #eef7f5,
                    #fff8e9
                );
        }

        .advantages h2 {
            color: #4d3568;

            margin-bottom: 20px;
        }

        .advantage-list {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;
        }

        .advantage {
            display: flex;

            align-items: center;

            gap: 12px;

            background: rgba(255, 255, 255, 0.75);

            padding: 15px;

            border-radius: 12px;

            color: #625e68;
        }

        .advantage span {
            font-size: 22px;
        }


        /* ==============================
           CIERRE
        ============================== */

        .closing {
            margin-top: 40px;

            background: white;

            padding: 35px;

            border-radius: 20px;

            box-shadow:
                0 8px 25px rgba(60, 40, 90, 0.07);

            text-align: center;
        }

        .closing h2 {
            color: #4d2c70;

            margin-bottom: 15px;
        }

        .closing p {
            max-width: 750px;

            margin: auto;

            color: #6d6875;

            line-height: 1.7;
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

        .contact-button {
            background: linear-gradient(
                135deg,
                #65379a,
                #4387c1
            );

            color: white;
        }

        .contact-button:hover {
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

            .functions {
                grid-template-columns: 1fr;
            }

            .advantage-list {
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

            .intro {
                padding: 25px;
            }

            .function {
                padding: 25px;
            }

            .advantages,
            .closing {
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

            <a href="que_es_pae.php">
                🍎 ¿Qué es?
            </a>

            <a href="beneficiarios.php">
                👨‍🎓 Beneficiarios
            </a>

            <a href="como_funciona.php">
                ⚙️ ¿Cómo funciona?
            </a>

            <a
                href="para_que_sirve.php"
                class="active"
            >
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
        🎯
    </div>

    <h1>
        ¿Para qué sirve?
    </h1>

    <p>
        Conoce cómo Gestión PAE puede facilitar la organización,
        registro y consulta de la información relacionada con
        la alimentación escolar.
    </p>

</section>


<!-- =====================================
     CONTENIDO
===================================== -->

<main class="content">


    <!-- INTRODUCCIÓN -->

    <div class="intro">

        <h2>
            🎯 El propósito de Gestión PAE
        </h2>

        <p>
            <strong>Gestión PAE</strong> es una plataforma creada
            para organizar la información relacionada con el proceso
            de alimentación escolar dentro de la institución.
        </p>

        <p>
            Su propósito es facilitar el registro de información,
            mantener los datos organizados y permitir que los usuarios
            autorizados puedan consultar los registros correspondientes
            a sus funciones.
        </p>

    </div>


    <!-- FUNCIONES -->

    <div class="functions">


        <!-- FUNCIÓN 1 -->

        <div class="function">

            <div class="function-icon">
                📋
            </div>

            <h3>
                Organizar información
            </h3>

            <p>
                Permite mantener organizada la información de estudiantes,
                cursos, profesores, personal PAE y demás datos necesarios
                para el funcionamiento del sistema.
            </p>

        </div>


        <!-- FUNCIÓN 2 -->

        <div class="function">

            <div class="function-icon">
                🍽️
            </div>

            <h3>
                Registrar entregas
            </h3>

            <p>
                Facilita el registro de las entregas de alimentación
                realizadas a los estudiantes durante las diferentes
                jornadas.
            </p>

        </div>


        <!-- FUNCIÓN 3 -->

        <div class="function">

            <div class="function-icon">
                📊
            </div>

            <h3>
                Consultar registros
            </h3>

            <p>
                Permite consultar información de las entregas realizadas
                y revisar los datos almacenados en el sistema.
            </p>

        </div>


        <!-- FUNCIÓN 4 -->

        <div class="function">

            <div class="function-icon">
                🕐
            </div>

            <h3>
                Mantener un historial
            </h3>

            <p>
                Conserva los registros de las jornadas anteriores para
                facilitar su consulta y seguimiento.
            </p>

        </div>


    </div>


    <!-- VENTAJAS -->

    <div class="advantages">

        <h2>
            ✨ ¿Qué facilita la plataforma?
        </h2>


        <div class="advantage-list">


            <div class="advantage">

                <span>📁</span>

                <div>
                    Información centralizada y organizada.
                </div>

            </div>


            <div class="advantage">

                <span>🔎</span>

                <div>
                    Consulta de información de manera sencilla.
                </div>

            </div>


            <div class="advantage">

                <span>👥</span>

                <div>
                    Organización de usuarios según sus roles.
                </div>

            </div>


            <div class="advantage">

                <span>📅</span>

                <div>
                    Conservación de registros históricos.
                </div>

            </div>


            <div class="advantage">

                <span>📋</span>

                <div>
                    Registro organizado de las entregas.
                </div>

            </div>


            <div class="advantage">

                <span>💻</span>

                <div>
                    Acceso a las funciones desde una plataforma web.
                </div>

            </div>


        </div>

    </div>


    <!-- CIERRE -->

    <div class="closing">

        <h2>
            💜 Una herramienta para la institución
        </h2>

        <p>
            Gestión PAE busca apoyar el trabajo de la comunidad educativa
            mediante una plataforma que facilite la organización y
            consulta de la información relacionada con la alimentación
            escolar.
        </p>

    </div>


    <!-- BOTONES -->

    <div class="navigation-buttons">

        <a
            href="como_funciona.php"
            class="button back-button"
        >
            ← Anterior
        </a>


        <a
            href="../contacto/contacto.php"
            class="button contact-button"
        >
            📱 Ver canales de comunicación →
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