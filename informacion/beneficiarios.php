<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Beneficiarios | Gestión PAE</title>

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
            background: #dfeef9;
            color: #397ba9;
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
                    rgba(62, 142, 195, 0.18),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 85% 70%,
                    rgba(53, 164, 156, 0.15),
                    transparent 30%
                ),
                #f7fafc;

            text-align: center;
        }

        .page-icon {
            width: 90px;
            height: 90px;

            margin: 0 auto 20px;

            border-radius: 28px;

            background: linear-gradient(
                135deg,
                #dceefa,
                #dcf2ed
            );

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 45px;
        }

        .page-header h1 {
            color: #304f68;
            font-size: 43px;
            margin-bottom: 15px;
        }

        .page-header p {
            max-width: 720px;
            margin: auto;

            color: #6a7077;
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

        .intro {
            background: white;

            padding: 40px;

            border-radius: 22px;

            box-shadow:
                0 10px 30px rgba(60, 40, 90, 0.08);

            margin-bottom: 35px;
        }

        .intro h2 {
            color: #3c6079;
            margin-bottom: 18px;
            font-size: 27px;
        }

        .intro p {
            color: #686d75;
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .intro strong {
            color: #39759c;
        }


        /* ==============================
           BENEFICIARIOS
        ============================== */

        .beneficiaries {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
        }

        .beneficiary {
            background: white;

            padding: 30px;

            border-radius: 20px;

            box-shadow:
                0 8px 25px rgba(60, 40, 90, 0.07);

            display: flex;
            gap: 20px;

            transition: 0.3s;
        }

        .beneficiary:hover {
            transform: translateY(-5px);

            box-shadow:
                0 12px 28px rgba(60, 40, 90, 0.11);
        }

        .beneficiary-icon {
            min-width: 65px;
            height: 65px;

            border-radius: 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 32px;
        }

        .student {
            background: #eee3fa;
        }

        .teacher {
            background: #dfeef9;
        }

        .pae {
            background: #dcf2ed;
        }

        .school {
            background: #fff2d4;
        }

        .beneficiary h3 {
            color: #42586a;
            margin-bottom: 9px;
        }

        .beneficiary p {
            color: #71757b;
            line-height: 1.6;
            font-size: 14px;
        }


        /* ==============================
           NOTA
        ============================== */

        .note {
            margin-top: 35px;

            padding: 30px;

            border-radius: 18px;

            background: linear-gradient(
                120deg,
                #eef5fb,
                #eaf7f5
            );

            border-left: 5px solid #4387c1;
        }

        .note h2 {
            color: #365c75;
            margin-bottom: 12px;
        }

        .note p {
            color: #626970;
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
                #4387c1,
                #35a49c
            );

            color: white;
        }

        .next-button:hover {
            transform: translateY(-2px);

            box-shadow:
                0 6px 15px rgba(48, 123, 148, 0.20);
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

            .beneficiaries {
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

            .beneficiary {
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

            <a
                href="beneficiarios.php"
                class="active"
            >
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
        👨‍🎓
    </div>

    <h1>
        ¿Quiénes se benefician?
    </h1>

    <p>
        Conoce a las personas que participan en el proceso de
        alimentación escolar y el papel que pueden desempeñar
        dentro de la comunidad educativa.
    </p>

</section>


<!-- =====================================
     CONTENIDO
===================================== -->

<main class="content">


    <!-- INTRODUCCIÓN -->

    <div class="intro">

        <h2>
            👥 La comunidad educativa
        </h2>

        <p>
            El proceso de alimentación escolar involucra a diferentes
            integrantes de la comunidad educativa. Cada uno puede tener
            funciones diferentes dentro de la organización y seguimiento
            del servicio.
        </p>

        <p>
            En <strong>Gestión PAE</strong>, la información se organiza
            teniendo en cuenta los diferentes usuarios y roles que
            participan en el proceso.
        </p>

    </div>


    <!-- BENEFICIARIOS -->

    <div class="beneficiaries">


        <!-- ESTUDIANTES -->

        <div class="beneficiary">

            <div class="beneficiary-icon student">
                👨‍🎓
            </div>

            <div>

                <h3>
                    Estudiantes
                </h3>

                <p>
                    Son los principales beneficiarios del servicio de
                    alimentación escolar, de acuerdo con los criterios
                    y la organización establecida por la institución.
                </p>

            </div>

        </div>


        <!-- DOCENTES -->

        <div class="beneficiary">

            <div class="beneficiary-icon teacher">
                👩‍🏫
            </div>

            <div>

                <h3>
                    Docentes
                </h3>

                <p>
                    Pueden acompañar el proceso de los grupos y consultar
                    información relacionada con los registros de
                    alimentación cuando sus funciones lo requieran.
                </p>

            </div>

        </div>


        <!-- PERSONAL PAE -->

        <div class="beneficiary">

            <div class="beneficiary-icon pae">
                👨‍🍳
            </div>

            <div>

                <h3>
                    Personal PAE
                </h3>

                <p>
                    Participa directamente en la entrega de los alimentos
                    y en el registro de los estudiantes atendidos durante
                    cada jornada.
                </p>

            </div>

        </div>


        <!-- INSTITUCIÓN -->

        <div class="beneficiary">

            <div class="beneficiary-icon school">
                🏫
            </div>

            <div>

                <h3>
                    Institución educativa
                </h3>

                <p>
                    Puede organizar y consultar la información necesaria
                    para realizar seguimiento al proceso de alimentación
                    escolar dentro de la institución.
                </p>

            </div>

        </div>


    </div>


    <!-- NOTA -->

    <div class="note">

        <h2>
            💡 Un trabajo organizado
        </h2>

        <p>
            La participación de los diferentes integrantes permite que
            el proceso pueda desarrollarse de manera organizada. Por
            medio de Gestión PAE, cada usuario puede acceder a las
            funciones correspondientes a su rol.
        </p>

    </div>


    <!-- BOTONES -->

    <div class="navigation-buttons">

        <a
            href="que_es_pae.php"
            class="button back-button"
        >
            ← Anterior
        </a>


        <a
            href="como_funciona.php"
            class="button next-button"
        >
            Siguiente: ¿Cómo funciona? →
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