<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contacto | Gestión PAE</title>

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
                    rgba(50, 175, 183, 0.16),
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
            max-width: 720px;

            margin: auto;

            color: #6d6878;

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

            text-align: center;
        }

        .intro h2 {
            color: #4d2c70;

            font-size: 27px;

            margin-bottom: 15px;
        }

        .intro p {
            max-width: 750px;

            margin: auto;

            color: #686373;

            line-height: 1.8;
        }


        /* ==============================
           CANALES
        ============================== */

        .channels {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 22px;
        }

        .channel {
            background: white;

            border-radius: 20px;

            padding: 30px;

            box-shadow:
                0 8px 25px rgba(60, 40, 90, 0.07);

            display: flex;

            align-items: center;

            gap: 20px;

            text-decoration: none;

            transition: 0.3s;
        }

        .channel:hover {
            transform: translateY(-5px);

            box-shadow:
                0 12px 28px rgba(60, 40, 90, 0.12);
        }

        .channel-icon {
            min-width: 70px;
            height: 70px;

            border-radius: 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 34px;
        }

        .facebook {
            background: #e9f0ff;
        }

        .instagram {
            background: #f9eafa;
        }

        .youtube {
            background: #fff0f0;
        }

        .whatsapp {
            background: #e5f7ec;
        }

        .email {
            background: #eee7fa;
        }

        .phone {
            background: #e1f2f8;
        }

        .channel h3 {
            color: #4d2c70;

            margin-bottom: 7px;
        }

        .channel p {
            color: #74707c;

            font-size: 14px;

            line-height: 1.5;
        }


        /* ==============================
           INFORMACIÓN INSTITUCIONAL
        ============================== */

        .institution {
            margin-top: 40px;

            padding: 35px;

            border-radius: 20px;

            background:
                linear-gradient(
                    120deg,
                    #f2eafa,
                    #eef8f7
                );
        }

        .institution h2 {
            color: #432761;

            margin-bottom: 20px;
        }

        .institution-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;
        }

        .institution-item {
            background: rgba(255, 255, 255, 0.8);

            padding: 20px;

            border-radius: 14px;

            text-align: center;
        }

        .institution-icon {
            font-size: 28px;

            margin-bottom: 10px;
        }

        .institution-item h3 {
            color: #4d2c70;

            font-size: 15px;

            margin-bottom: 6px;
        }

        .institution-item p {
            color: #706b78;

            font-size: 13px;
        }


        /* ==============================
           IMPORTANTE
        ============================== */

        .notice {
            margin-top: 35px;

            padding: 25px;

            background: #fff8e7;

            border-left: 5px solid #e1aa3c;

            border-radius: 12px;
        }

        .notice p {
            color: #6e6148;

            line-height: 1.7;

            font-size: 14px;
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

        .login-button {
            background: linear-gradient(
                135deg,
                #65379a,
                #4387c1
            );

            color: white;
        }

        .login-button:hover {
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

            .institution-grid {
                grid-template-columns: 1fr;
            }

            .channels {
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

            .channel {
                padding: 22px;
            }

            .institution {
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


/* =====================================
   RESPONSIVE - CONTACTO PAE
===================================== */

/* Tabletas */
@media (max-width: 900px) {
    .navbar {
        flex-direction: column;
        gap: 12px;
        padding: 16px 20px;
    }

    .menu {
        width: 100%;
        justify-content: center;
    }

    .menu a {
        font-size: 12px;
        padding: 9px;
    }

    .channels {
        grid-template-columns: 1fr;
    }

    .institution-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .content {
        padding: 50px 24px;
    }
}

/* Celulares */
@media (max-width: 600px) {
    .navbar {
        padding: 15px;
    }

    .logo {
        font-size: 19px;
    }

    .menu {
        gap: 5px;
    }

    .menu a {
        font-size: 11px;
        padding: 8px;
    }

    .page-header {
        padding: 45px 20px;
    }

    .page-icon {
        width: 72px;
        height: 72px;
        font-size: 36px;
        border-radius: 22px;
    }

    .page-header h1 {
        font-size: clamp(28px, 8vw, 34px);
        line-height: 1.2;
    }

    .page-header p {
        font-size: 15px;
    }

    .content {
        padding: 35px 16px;
    }

    .intro {
        padding: 25px 20px;
        border-radius: 18px;
    }

    .intro h2 {
        font-size: 23px;
    }

    .intro p {
        font-size: 14px;
    }

    .channels {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .channel {
        padding: 20px 16px;
        gap: 14px;
        border-radius: 16px;
        min-width: 0;
    }

    .channel-icon {
        min-width: 52px;
        width: 52px;
        height: 52px;
        font-size: 27px;
        border-radius: 15px;
    }

    .channel h3 {
        font-size: 16px;
    }

    .channel p {
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .institution {
        padding: 22px 16px;
        margin-top: 28px;
    }

    .institution h2 {
        font-size: 22px;
    }

    .institution-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .institution-item {
        padding: 18px;
    }

    .notice {
        padding: 18px;
    }

    .navigation-buttons {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        margin-top: 30px;
    }

    .button {
        width: 100%;
        text-align: center;
    }

    footer {
        padding: 28px 18px;
    }

    footer p {
        line-height: 1.6;
        overflow-wrap: anywhere;
    }
}

/* Celulares pequeños */
@media (max-width: 360px) {
    .menu a {
        font-size: 10px;
        padding: 7px 6px;
    }

    .channel {
        align-items: flex-start;
        padding: 16px 12px;
    }

    .channel-icon {
        min-width: 44px;
        width: 44px;
        height: 44px;
        font-size: 23px;
    }

    .page-header h1 {
        font-size: 27px;
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

            <a href="../informacion/que_es_pae.php">
                🍎 ¿Qué es?
            </a>

            <a href="../informacion/beneficiarios.php">
                👨‍🎓 Beneficiarios
            </a>

            <a href="../informacion/como_funciona.php">
                ⚙️ ¿Cómo funciona?
            </a>

            <a href="../informacion/para_que_sirve.php">
                🎯 ¿Para qué sirve?
            </a>

            <a
                href="contacto.php"
                class="active"
            >
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
        📱
    </div>

    <h1>
        Canales de comunicación
    </h1>

    <p>
        Encuentra los canales oficiales de comunicación de la
        institución para mantenerte informado y resolver tus dudas.
    </p>

</section>


<!-- =====================================
     CONTENIDO
===================================== -->

<main class="content">


    <!-- INTRODUCCIÓN -->

    <div class="intro">

        <h2>
            💬 Estamos para escucharte
        </h2>

        <p>
            Si tienes alguna pregunta, necesitas información adicional
            o deseas conocer las novedades de la institución, puedes
            utilizar los diferentes canales de comunicación disponibles.
        </p>

    </div>


    <!-- CANALES -->

    <div class="channels">


        <!-- FACEBOOK -->

        <a
            href="https://www.facebook.com/lasallemanrique?locale=es_LA"
            class="channel"
        >

            <div class="channel-icon facebook">
                📘
            </div>

            <div>

                <h3>
                    Facebook
                </h3>

                <p>
                    Consulta noticias, novedades y publicaciones
                    de la institución.
                </p>

            </div>

        </a>


        <!-- INSTAGRAM -->

        <a
            href="https://www.instagram.com/lasallemanrique/"
            class="channel"
        >

            <div class="channel-icon instagram">
                📸
            </div>

            <div>

                <h3>
                    Instagram
                </h3>

                <p>
                    Encuentra fotografías, actividades y contenido
                    institucional.
                </p>

            </div>

        </a>


        <!-- YOUTUBE -->

        <a
            href="https://www.youtube.com/@lasallecomunica9495"
            class="channel"
        >

            <div class="channel-icon youtube">
                ▶️
            </div>

            <div>

                <h3>
                    YouTube
                </h3>

                <p>
                    Consulta videos, eventos y contenido audiovisual
                    de la institución.
                </p>

            </div>

        </a>


        <!-- WHATSAPP -->

        <a
            href="https://wa.me/573001234567"
            class="channel"
        >

            <div class="channel-icon whatsapp">
                💬
            </div>

            <div>

                <h3>
                    WhatsApp
                </h3>

                <p>
                    Canal de comunicación para consultas y orientación.
                </p>

            </div>

        </a>


        <!-- CORREO -->

        <a
            href="mailto:ielasalle@gmail.com"
            class="channel"
        >

            <div class="channel-icon email">
                📧
            </div>

            <div>

                <h3>
                    Correo institucional
                </h3>

                <p>
                    Canal destinado a consultas y comunicaciones
                    institucionales.
                </p>

            </div>

        </a>


        <!-- TELÉFONO -->

        <a
            href="tel:+57(604) 522 52 37"
            class="channel"
        >

            <div class="channel-icon phone">
                ☎️
            </div>

            <div>

                <h3>
                    Teléfono
                </h3>

                <p>
                    Comunícate con la institución para recibir
                    orientación.
                </p>

            </div>

        </a>


    </div>


    <!-- INFORMACIÓN INSTITUCIONAL -->

    <div class="institution">

        <h2>
            🏫 Información institucional
        </h2>


        <div class="institution-grid">


            <div class="institution-item">

                <div class="institution-icon">
                    📍
                </div>

                <h3>
                    Ubicación
                </h3>

                <p>
                    Calle 93 No. 40 - 42 Barrio La Salle - Medellín, Antioquia, Colombia
                </p>

            </div>


            <div class="institution-item">

                <div class="institution-icon">
                    🕐
                </div>

                <h3>
                    Horario de atención
                </h3>

                <p>
                    Lunes a Viernes 08:00 a.m. - 04:00 p.m.
                </p>

            </div>


            <div class="institution-item">

                <div class="institution-icon">
                    📧
                </div>

                <h3>
                    Correo
                </h3>

                <p>
                    ielasalle@gmail.com
                </p>

            </div>


        </div>

    </div>


    <!-- AVISO -->

    

    <!-- BOTONES -->

    <div class="navigation-buttons">

        <a
            href="../index.php"
            class="button back-button"
        >
            ← Volver al inicio
        </a>


        <a
            href="../login.php"
            class="button login-button"
        >
            🔐 Ingresar al sistema →
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