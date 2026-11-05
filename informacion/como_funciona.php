<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>¿Cómo funciona? | Gestión PAE</title>

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
            background: #dcf2ed;
            color: #287f77;
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
                    rgba(53, 164, 156, 0.18),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 85% 70%,
                    rgba(62, 142, 195, 0.16),
                    transparent 30%
                ),
                #f7faf9;

            text-align: center;
        }

        .page-icon {
            width: 90px;
            height: 90px;

            margin: 0 auto 20px;

            border-radius: 28px;

            background: linear-gradient(
                135deg,
                #dcf2ed,
                #dceefa
            );

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 45px;
        }

        .page-header h1 {
            color: #315f5b;

            font-size: 43px;

            margin-bottom: 15px;
        }

        .page-header p {
            max-width: 720px;

            margin: auto;

            color: #687474;

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
            color: #356c67;

            font-size: 27px;

            margin-bottom: 18px;
        }

        .intro p {
            color: #686f72;

            line-height: 1.8;

            margin-bottom: 12px;
        }

        .intro strong {
            color: #287f77;
        }


        /* ==============================
           PASOS
        ============================== */

        .steps {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 22px;
        }

        .step {
            background: white;

            border-radius: 20px;

            padding: 30px;

            box-shadow:
                0 8px 25px rgba(60, 40, 90, 0.07);

            display: flex;

            gap: 20px;

            transition: 0.3s;
        }

        .step:hover {
            transform: translateY(-5px);

            box-shadow:
                0 12px 28px rgba(60, 40, 90, 0.11);
        }

        .step-number {
            min-width: 60px;
            height: 60px;

            border-radius: 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 23px;
            font-weight: bold;

            color: white;

            background: linear-gradient(
                135deg,
                #4387c1,
                #35a49c
            );
        }

        .step h3 {
            color: #3e6865;

            margin-bottom: 9px;
        }

        .step p {
            color: #71777a;

            line-height: 1.6;

            font-size: 14px;
        }


        /* ==============================
           ROLES
        ============================== */

        .roles {
            margin-top: 40px;

            padding: 35px;

            border-radius: 20px;

            background:
                linear-gradient(
                    120deg,
                    #eef7f5,
                    #eef5fb
                );
        }

        .roles h2 {
            color: #315f5b;

            margin-bottom: 15px;
        }

        .roles p {
            color: #626b6d;

            line-height: 1.7;

            margin-bottom: 20px;
        }

        .role-list {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;
        }

        .role {
            background: white;

            border-radius: 14px;

            padding: 20px;

            text-align: center;
        }

        .role-icon {
            font-size: 30px;

            margin-bottom: 10px;
        }

        .role h3 {
            color: #3f6562;

            font-size: 16px;
        }


        /* ==============================
           SISTEMA
        ============================== */

        .system {
            margin-top: 40px;

            background: white;

            border-radius: 20px;

            padding: 35px;

            box-shadow:
                0 8px 25px rgba(60, 40, 90, 0.07);
        }

        .system h2 {
            color: #4d2c70;

            margin-bottom: 15px;
        }

        .system p {
            color: #686373;

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

            .role-list {
                grid-template-columns: 1fr;
            }

            .steps {
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

            .step {
                padding: 25px;
            }

            .roles,
            .system {
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

            <a
                href="como_funciona.php"
                class="active"
            >
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
        ⚙️
    </div>

    <h1>
        ¿Cómo funciona?
    </h1>

    <p>
        Conoce de manera sencilla cómo se organiza el proceso
        de alimentación escolar y cómo Gestión PAE ayuda a
        registrar y consultar la información.
    </p>

</section>


<!-- =====================================
     CONTENIDO
===================================== -->

<main class="content">


    <!-- INTRODUCCIÓN -->

    <div class="intro">

        <h2>
            🔄 Un proceso organizado
        </h2>

        <p>
            Gestión PAE permite organizar diferentes elementos
            relacionados con la alimentación escolar dentro de
            la institución.
        </p>

        <p>
            El proceso comienza con la organización de los
            estudiantes y grupos y continúa con el registro de
            las entregas realizadas durante la jornada.
        </p>

        <p>
            Finalmente, la información almacenada puede ser
            consultada por los usuarios autorizados según
            las funciones de cada rol.
        </p>

    </div>


    <!-- PASOS -->

    <div class="steps">


        <!-- PASO 1 -->

        <div class="step">

            <div class="step-number">
                1
            </div>

            <div>

                <h3>
                    👨‍🎓 Organización de estudiantes
                </h3>

                <p>
                    Los estudiantes se registran y se organizan
                    de acuerdo con el curso o grupo al que
                    pertenecen.
                </p>

            </div>

        </div>


        <!-- PASO 2 -->

        <div class="step">

            <div class="step-number">
                2
            </div>

            <div>

                <h3>
                    🏫 Organización de grupos
                </h3>

                <p>
                    Los cursos permiten organizar a los estudiantes
                    y facilitar el proceso de registro de las
                    entregas de alimentación.
                </p>

            </div>

        </div>


        <!-- PASO 3 -->

        <div class="step">

            <div class="step-number">
                3
            </div>

            <div>

                <h3>
                    🍽️ Entrega de alimentación
                </h3>

                <p>
                    El personal encargado realiza la entrega
                    correspondiente y registra los estudiantes
                    que reciben el servicio.
                </p>

            </div>

        </div>


        <!-- PASO 4 -->

        <div class="step">

            <div class="step-number">
                4
            </div>

            <div>

                <h3>
                    📋 Registro de información
                </h3>

                <p>
                    Los datos de la entrega quedan almacenados
                    para mantener un registro organizado de
                    cada jornada.
                </p>

            </div>

        </div>


    </div>


    <!-- ROLES -->

    <div class="roles">

        <h2>
            👥 Participación de los usuarios
        </h2>

        <p>
            Cada usuario puede tener diferentes funciones dentro
            de Gestión PAE. El sistema muestra las herramientas
            correspondientes según el rol asignado.
        </p>


        <div class="role-list">


            <div class="role">

                <div class="role-icon">
                    👨‍💼
                </div>

                <h3>
                    Administrador
                </h3>

            </div>


            <div class="role">

                <div class="role-icon">
                    👩‍🏫
                </div>

                <h3>
                    Profesor
                </h3>

            </div>


            <div class="role">

                <div class="role-icon">
                    👨‍🍳
                </div>

                <h3>
                    Personal PAE
                </h3>

            </div>


        </div>

    </div>


    <!-- SISTEMA -->

    <div class="system">

        <h2>
            💻 ¿Dónde entra Gestión PAE?
        </h2>

        <p>
            La plataforma permite centralizar la información
            relacionada con estudiantes, cursos, profesores,
            personal PAE y registros de alimentación.
        </p>

        <br>

        <p>
            De esta manera, la información puede mantenerse
            organizada y los usuarios autorizados pueden
            consultar los registros correspondientes a sus
            funciones.
        </p>

    </div>


    <!-- BOTONES -->

    <div class="navigation-buttons">

        <a
            href="beneficiarios.php"
            class="button back-button"
        >
            ← Anterior
        </a>


        <a
            href="para_que_sirve.php"
            class="button next-button"
        >
            Siguiente: ¿Para qué sirve? →
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