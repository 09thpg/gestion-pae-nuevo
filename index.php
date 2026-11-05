<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión PAE | Programa de Alimentación Escolar</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            background: white;
            width: 100%;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 3px 15px rgba(60, 40, 90, 0.10);
        }

        .navbar {
            max-width: 1250px;
            min-height: 75px;
            margin: auto;
            padding: 0 25px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
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
            color: white;
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
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(83, 52, 145, 0.25);
        }


        /* ==============================
           HERO
        ============================== */

        .hero {
            min-height: 610px;
            padding: 70px 25px;

            background:
                radial-gradient(
                    circle at 10% 20%,
                    rgba(111, 69, 178, 0.18),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 90% 80%,
                    rgba(50, 175, 183, 0.16),
                    transparent 30%
                ),
                #f8f7fc;

            display: flex;
            align-items: center;
        }

        .hero-container {
            max-width: 1150px;
            width: 100%;
            margin: auto;

            display: grid;
            grid-template-columns: 1.1fr 0.9fr;

            gap: 60px;
            align-items: center;
        }

        .hero-text small {
            display: inline-block;

            background: #e9def8;
            color: #643796;

            padding: 8px 14px;
            border-radius: 20px;

            font-weight: bold;
            font-size: 13px;

            margin-bottom: 20px;
        }

        .hero-text h1 {
            font-size: 52px;
            line-height: 1.08;

            color: #38204f;

            margin-bottom: 20px;
        }

        .hero-text h1 span {
            color: #4388bd;
        }

        .hero-text p {
            color: #686272;

            font-size: 17px;
            line-height: 1.7;

            max-width: 650px;

            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 13px;
            flex-wrap: wrap;
        }

        .hero-button {
            text-decoration: none;

            padding: 14px 22px;

            border-radius: 11px;

            font-weight: bold;

            transition: 0.3s;
        }

        .hero-primary {
            background: #65379a;
            color: white;
        }

        .hero-primary:hover {
            background: #542b85;
            transform: translateY(-2px);
        }

        .hero-secondary {
            background: white;
            color: #65379a;

            border: 1px solid #ded5e9;
        }

        .hero-secondary:hover {
            background: #f2edf8;
        }


        /* ==============================
           TARJETA DEL HERO
        ============================== */

        .hero-visual {
            position: relative;
            min-height: 360px;
        }

        .main-card {
            background: white;

            border-radius: 25px;

            padding: 40px;

            box-shadow:
                0 18px 45px rgba(59, 41, 83, 0.12);

            text-align: center;

            position: relative;
            z-index: 2;
        }

        .main-card-icon {
            width: 115px;
            height: 115px;

            margin: auto;
            margin-bottom: 20px;

            border-radius: 35px;

            background: linear-gradient(
                135deg,
                #eadcf8,
                #d8f1f0
            );

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 60px;
        }

        .main-card h2 {
            color: #4f2c73;
            margin-bottom: 10px;
        }

        .main-card p {
            color: #777180;
            line-height: 1.6;
        }

        .floating-card {
            position: absolute;

            padding: 15px 18px;

            background: white;

            border-radius: 14px;

            box-shadow: 0 8px 25px rgba(60, 40, 90, 0.12);

            font-size: 14px;
            font-weight: bold;

            z-index: 3;
        }

        .floating-one {
            top: 20px;
            left: -20px;

            color: #65379a;
        }

        .floating-two {
            bottom: 25px;
            right: -20px;

            color: #258e91;
        }


        /* ==============================
           ACCESOS RÁPIDOS
        ============================== */

        .quick {
            background: white;
            padding: 65px 25px;
        }

        .container {
            max-width: 1150px;
            margin: auto;
        }

        .title {
            text-align: center;
            margin-bottom: 40px;
        }

        .title h2 {
            color: #42255f;
            font-size: 31px;
            margin-bottom: 10px;
        }

        .title p {
            color: #706b79;
            line-height: 1.6;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .quick-card {
            text-decoration: none;

            background: #faf9fd;

            border: 1px solid #eee9f4;

            border-radius: 17px;

            padding: 25px 20px;

            text-align: center;

            transition: 0.3s;
        }

        .quick-card:hover {
            transform: translateY(-5px);

            box-shadow:
                0 10px 25px rgba(62, 42, 91, 0.10);
        }

        .quick-icon {
            width: 58px;
            height: 58px;

            margin: 0 auto 15px;

            border-radius: 17px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 28px;
        }

        .purple {
            background: #eee3fa;
        }

        .blue {
            background: #dfeef9;
        }

        .green {
            background: #dcf2ed;
        }

        .yellow {
            background: #fff2d4;
        }

        .quick-card h3 {
            color: #4c2c6d;
            margin-bottom: 8px;
        }

        .quick-card p {
            color: #777180;
            font-size: 14px;
            line-height: 1.5;
        }


        /* ==============================
           INFORMACIÓN
        ============================== */

        .about {
            padding: 80px 25px;

            background:
                linear-gradient(
                    120deg,
                    #f4effa,
                    #eef8f8
                );
        }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-text h2 {
            color: #432761;
            font-size: 34px;
            margin-bottom: 18px;
        }

        .about-text p {
            color: #686373;
            line-height: 1.7;
            margin-bottom: 15px;
        }

        .about-list {
            list-style: none;
            margin-top: 20px;
        }

        .about-list li {
            margin-bottom: 13px;
            color: #5d5868;
        }

        .about-list span {
            margin-right: 8px;
        }

        .about-box {
            background: white;

            border-radius: 25px;

            padding: 35px;

            box-shadow:
                0 12px 35px rgba(62, 42, 91, 0.09);
        }

        .about-box h3 {
            color: #4d2c70;
            margin-bottom: 20px;
        }

        .about-box-item {
            display: flex;
            align-items: center;
            gap: 15px;

            padding: 15px;

            margin-bottom: 12px;

            border-radius: 12px;

            background: #faf9fc;
        }

        .about-box-item:last-child {
            margin-bottom: 0;
        }

        .about-box-icon {
            font-size: 25px;
        }


        /* ==============================
           CONTACTO
        ============================== */

        .contact {
            padding: 80px 25px;
            background: white;
        }

        .social-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .social {
            text-decoration: none;

            border-radius: 17px;

            padding: 25px 20px;

            text-align: center;

            transition: 0.3s;
        }

        .social:hover {
            transform: translateY(-5px);
        }

        .social-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .social h3 {
            margin-bottom: 8px;
        }

        .social p {
            font-size: 13px;
            line-height: 1.5;
        }

        .facebook {
            background: #edf3ff;
            color: #315ca8;
        }

        .instagram {
            background: #f9eafa;
            color: #9b438e;
        }

        .youtube {
            background: #fff0f0;
            color: #bd3c3c;
        }

        .whatsapp {
            background: #e8f8ef;
            color: #27834c;
        }

        .contact-button {
            display: block;
            width: fit-content;

            margin: 35px auto 0;

            padding: 13px 24px;

            text-decoration: none;

            background: #65379a;
            color: white;

            border-radius: 10px;

            font-weight: bold;
        }


        /* ==============================
           ASISTENTE
        ============================== */

        .assistant-section {
            padding: 70px 25px;

            background:
                linear-gradient(
                    135deg,
                    #57328b,
                    #3f8ca8
                );

            color: white;
        }

        .assistant-content {
            max-width: 950px;
            margin: auto;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 40px;
        }

        .assistant-text {
            max-width: 600px;
        }

        .assistant-text h2 {
            font-size: 31px;
            margin-bottom: 15px;
        }

        .assistant-text p {
            line-height: 1.7;
            opacity: 0.92;
        }

        .assistant-preview {
            background: white;
            color: #45305d;

            padding: 20px;

            border-radius: 18px;

            min-width: 260px;

            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .assistant-preview strong {
            display: block;
            margin-bottom: 10px;
        }

        .assistant-preview p {
            font-size: 13px;
            color: #777;
        }


        /* ==============================
           INGRESO
        ============================== */

        .login-section {
            padding: 65px 25px;
            text-align: center;

            background: #f8f7fc;
        }

        .login-section h2 {
            color: #432761;
            margin-bottom: 12px;
        }

        .login-section p {
            color: #706a79;
            margin-bottom: 25px;
        }

        .login-button {
            display: inline-block;

            padding: 14px 28px;

            background: linear-gradient(
                135deg,
                #65379a,
                #4387c1
            );

            color: white;

            text-decoration: none;

            border-radius: 11px;

            font-weight: bold;
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

        @media (max-width: 950px) {

            .navbar {
                flex-direction: column;
                padding: 15px;
            }

            .menu {
                width: 100%;
            }

            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-text p {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .quick-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .social-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .about-content {
                grid-template-columns: 1fr;
            }

            .assistant-content {
                flex-direction: column;
                text-align: center;
            }
        }


        @media (max-width: 600px) {

            .menu a {
                font-size: 11px;
                padding: 8px;
            }

            .hero {
                padding: 55px 20px;
            }

            .hero-text h1 {
                font-size: 38px;
            }

            .hero-visual {
                min-height: auto;
            }

            .floating-card {
                display: none;
            }

            .quick-grid,
            .social-grid {
                grid-template-columns: 1fr;
            }

            .about,
            .contact {
                padding: 60px 20px;
            }

            .about-text h2 {
                font-size: 29px;
            }
        }

.hero {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    align-items: center;
    gap: 50px;
    padding: 45px 7%;
}

.hero-contenido {
    min-width: 0;
}

.etiqueta {
    display: inline-block;
    padding: 9px 16px;
    border-radius: 30px;
    background: #eee2fb;
    color: #6335a5;
    font-size: 14px;
    margin-bottom: 18px;
}

.hero h1 {
    margin: 0 0 20px;
    color: #38204d;
    font-size: clamp(36px, 4vw, 54px);
    line-height: 1.12;
}

.hero h1 span {
    color: #438fbe;
}

.subtitulo {
    color: #5d536b;
    font-size: 19px;
    line-height: 1.5;
    margin-bottom: 14px;
}

.descripcion {
    color: #625b70;
    font-size: 16px;
    line-height: 1.8;
    margin-bottom: 28px;
}

.hero-botones {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.btn-principal,
.btn-secundario {
    display: inline-block;
    padding: 15px 22px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.2s;
}

.btn-principal {
    background: #6738a4;
    color: white;
}

.btn-principal:hover {
    background: #512783;
}

.btn-secundario {
    background: white;
    border: 1px solid #ded0ee;
    color: #6335a5;
}

.btn-secundario:hover {
    background: #f5effb;
}

.hero-visual {
    display: flex;
    justify-content: center;
    align-items: center;
    min-width: 0;
}


.tarjeta-refrigerio {
    width: 100%;
    max-width: 490px;
    height: 320px;
    padding: 14px;
    overflow: hidden;
    border-radius: 28px;
    background: white;
    box-shadow: 0 15px 40px rgba(58, 39, 82, 0.10);
}

.imagen-refrigerio {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    border-radius: 18px;
}


/* Responsive */
@media (max-width: 850px) {
    .hero {
        grid-template-columns: 1fr;
        gap: 32px;
        padding: 35px 6%;
    }

    .hero h1 {
        font-size: clamp(34px, 7vw, 46px);
    }

    .hero-visual {
        width: 100%;
    }

    .tarjeta-refrigerio {
        max-width: 550px;
        height: 300px;
    }
}

@media (max-width: 480px) {
    .hero {
        padding: 25px 20px;
        gap: 26px;
    }

    .hero h1 {
        font-size: 35px;
    }

    .subtitulo {
        font-size: 17px;
    }

    .hero-botones {
        flex-direction: column;
    }

    .btn-principal,
    .btn-secundario {
        text-align: center;
        width: 100%;
    }

    .tarjeta-refrigerio {
        height: 240px;
        padding: 10px;
        border-radius: 20px;
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

        <a href="index.php" class="logo">

            <span class="logo-icon">
                🍎
            </span>

            Gestión PAE

        </a>


        <div class="menu">

            <a href="index.php">
                🏠 Inicio
            </a>

            <a href="informacion/que_es_pae.php">
                🍎 ¿Qué es?
            </a>

            <a href="informacion/beneficiarios.php">
                👨‍🎓 Beneficiarios
            </a>

            <a href="informacion/como_funciona.php">
                ⚙️ ¿Cómo funciona?
            </a>

            <a href="informacion/para_que_sirve.php">
                🎯 ¿Para qué sirve?
            </a>

            <a href="contacto/contacto.php">
                📱 Contacto
            </a>

            <a href="login.php" class="login">
                🔐 Iniciar Sesión
            </a>

        </div>

    </nav>

</header>


<!-- =====================================
     HERO
===================================== -->


<section class="hero">
    <div class="hero-contenido">
        <span class="etiqueta">
            🍎 Alimentación que acompaña tu jornada
        </span>

        <h1>
            Programa de
            <span>Alimentación Escolar</span>
        </h1>

        <p class="subtitulo">
            Organización y seguimiento de la alimentación
            escolar en la institución.
        </p>

        <p class="descripcion">
            Bienvenido a <strong>Gestión PAE</strong>, una
            plataforma para facilitar la organización,
            el registro y la consulta de la información
            relacionada con el programa.
        </p>

        
    </div>

    <div class="hero-visual">
        <div class="tarjeta-refrigerio">
            <!-- Aquí colocarás tu fotografía del refrigerio -->
            <img
                src="img/salle.png"
                alt="Refrigerio del Programa de Alimentación Escolar"
                class="imagen-refrigerio"
            >
        </div>
    </div>
</section>



<!-- =====================================
     ACCESOS RÁPIDOS
===================================== -->

<section class="quick">

    <div class="container">

        <div class="title">

            <h2>
                Conoce nuestro programa
            </h2>

            <p>
                Encuentra información sobre el funcionamiento del PAE
                y el propósito de nuestra plataforma.
            </p>

        </div>


        <div class="quick-grid">


            <a
                href="informacion/que_es_pae.php"
                class="quick-card"
            >

                <div class="quick-icon purple">
                    🍎
                </div>

                <h3>
                    ¿Qué es el PAE?
                </h3>

                <p>
                    Conoce qué es el Programa de Alimentación Escolar
                    y cuál es su propósito.
                </p>

            </a>


            <a
                href="informacion/beneficiarios.php"
                class="quick-card"
            >

                <div class="quick-icon blue">
                    👨‍🎓
                </div>

                <h3>
                    Beneficiarios
                </h3>

                <p>
                    Conoce quiénes participan y se benefician
                    del proceso.
                </p>

            </a>


            <a
                href="informacion/como_funciona.php"
                class="quick-card"
            >

                <div class="quick-icon green">
                    ⚙️
                </div>

                <h3>
                    ¿Cómo funciona?
                </h3>

                <p>
                    Descubre de manera sencilla cómo se organiza
                    el proceso.
                </p>

            </a>


            <a
                href="informacion/para_que_sirve.php"
                class="quick-card"
            >

                <div class="quick-icon yellow">
                    🎯
                </div>

                <h3>
                    ¿Para qué sirve?
                </h3>

                <p>
                    Conoce cómo Gestión PAE ayuda a organizar
                    la información.
                </p>

            </a>

        </div>

    </div>

</section>


<!-- =====================================
     SOBRE GESTIÓN PAE
===================================== -->

<section class="about">

    <div class="container about-content">


        <div class="about-text">

            <h2>
                Una plataforma pensada para organizar
            </h2>

            <p>
                Gestión PAE permite centralizar información relacionada
                con estudiantes, grupos, docentes, personal PAE y
                registros de alimentación.
            </p>

            <p>
                De esta manera, los usuarios autorizados pueden acceder
                a las funciones correspondientes a su rol dentro de
                la institución.
            </p>


            <ul class="about-list">

                <li>
                    <span>📋</span>
                    Organización de la información
                </li>

                <li>
                    <span>👨‍🎓</span>
                    Registro de estudiantes y grupos
                </li>

                <li>
                    <span>🍽️</span>
                    Registro de entregas
                </li>

                <li>
                    <span>📊</span>
                    Consulta de historial
                </li>

            </ul>

        </div>


        <div class="about-box">

            <h3>
                ¿Qué puedes encontrar?
            </h3>


            <div class="about-box-item">

                <div class="about-box-icon">
                    👨‍🎓
                </div>

                <div>
                    <strong>
                        Información de estudiantes
                    </strong>
                </div>

            </div>


            <div class="about-box-item">

                <div class="about-box-icon">
                    🏫
                </div>

                <div>
                    <strong>
                        Cursos y grupos
                    </strong>
                </div>

            </div>


            <div class="about-box-item">

                <div class="about-box-icon">
                    👨‍🏫
                </div>

                <div>
                    <strong>
                        Información de docentes
                    </strong>
                </div>

            </div>


            <div class="about-box-item">

                <div class="about-box-icon">
                    🍽️
                </div>

                <div>
                    <strong>
                        Registros de alimentación
                    </strong>
                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================
     CONTACTO / REDES
===================================== -->

<section class="contact">

    <div class="container">

        <div class="title">

            <h2>
                📱 Canales de comunicación
            </h2>

            <p>
                Mantente informado y encuentra los canales oficiales
                de comunicación de la institución.
            </p>

        </div>


        <div class="social-grid">


            <a
                href="https://www.facebook.com/lasallemanrique?locale=es_LA"
                class="social facebook"
            >

                <div class="social-icon">
                    📘
                </div>

                <h3>
                    Facebook
                </h3>

                <p>
                    Noticias y novedades de la institución.
                </p>

            </a>


            <a
                href="https://www.instagram.com/lasallemanrique/"
                class="social instagram"
            >

                <div class="social-icon">
                    📸
                </div>

                <h3>
                    Instagram
                </h3>

                <p>
                    Actividades y contenido institucional.
                </p>

            </a>


            <a
                href="https://www.youtube.com/@lasallecomunica9495"
                class="social youtube"
            >

                <div class="social-icon">
                    ▶️
                </div>

                <h3>
                    YouTube
                </h3>

                <p>
                    Videos y contenido audiovisual.
                </p>

            </a>


            <a
                href="https://wa.me/"
                class="social whatsapp"
            >

                <div class="social-icon">
                    💬
                </div>

                <h3>
                    WhatsApp
                </h3>

                <p>
                    Canal de comunicación y atención.
                </p>

            </a>

        </div>


        <a
            href="contacto/contacto.php"
            class="contact-button"
        >
            Ver todos los canales
        </a>

    </div>

</section>


<!-- =====================================
     ASISTENTE VIRTUAL
===================================== -->

<section class="assistant-section">

    <div class="assistant-content">


        <div class="assistant-text">

            <h2>
                🤖 ¿Tienes alguna pregunta?
            </h2>

            <p>
                Próximamente podrás consultar nuestro asistente virtual
                para encontrar respuestas rápidas sobre el PAE, el uso
                de la plataforma y los canales de comunicación.
            </p>

        </div>


        <div class="assistant-preview">

            <strong>
                🤖 Asistente PAE
            </strong>

            <p>
                ¡Hola! 👋 ¿En qué puedo ayudarte?
            </p>

        </div>

    </div>

</section>


<!-- =====================================
     INGRESO
===================================== -->

<section class="login-section">

    <div class="container">

        <h2>
            🔐 ¿Ya tienes una cuenta?
        </h2>

        <p>
            Ingresa a Gestión PAE para acceder a las funciones
            correspondientes a tu usuario.
        </p>

        <a
            href="login.php"
            class="login-button"
        >
            Ingresar al sistema
        </a>

    </div>

</section>


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

    <p>Institución Educativa San Juan Bautista de La Salle
<br>Dirección: Calle 93 No. 40 - 42 Barrio La Salle - Medellín, Antioquia, Colombia
<br>Horario de Atención: Lunes a Viernes 08:00 a.m. - 04:00 p.m.
<br>Teléfono: +57(604) 522 52 37
<br>Correo Electrónico: contactenos@iesanjuanbautista.edu.co

</p>
</footer>

<?php include __DIR__ . "/componentes/asistente_pae.php"; ?>
</body>
</html>