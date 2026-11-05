<!-- 🤖 BAUTISTA.BOT - ASISTENTE VIRTUAL PAE -->

<style>
    /* Botón flotante de Bautista */
    .bautista-boton {
        position: fixed;
        bottom: 25px;
        right: 25px;
        width: 70px;
        height: 70px;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, #6c4ab6, #3f8edb);
        color: white;
        font-size: 32px;
        cursor: pointer;
        box-shadow: 0 8px 25px rgba(50, 40, 100, 0.30);
        z-index: 9999;
        transition: 0.3s;
    }

    .bautista-boton:hover {
        transform: scale(1.08);
    }

    /* Ventana del chat */
    .bautista-chat {
        display: none;
        position: fixed;
        bottom: 110px;
        right: 25px;
        width: 350px;
        max-width: calc(100vw - 40px);
        height: 500px;
        background: white;
        border-radius: 22px;
        box-shadow: 0 12px 40px rgba(40, 30, 80, 0.25);
        overflow: hidden;
        z-index: 9998;
        border: 1px solid #e6def7;
        animation: aparecer 0.25s ease;
    }

    .bautista-chat.activo {
        display: flex;
        flex-direction: column;
    }

    @keyframes aparecer {
        from {
            opacity: 0;
            transform: translateY(15px) scale(0.96);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Encabezado */
    .bautista-header {
        background: linear-gradient(135deg, #6c4ab6, #3f8edb);
        color: white;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .bautista-avatar {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 27px;
    }

    .bautista-header h3 {
        margin: 0;
        font-size: 17px;
    }

    .bautista-header p {
        margin: 3px 0 0;
        font-size: 12px;
        opacity: 0.9;
    }

    .bautista-cerrar {
        margin-left: auto;
        background: transparent;
        border: none;
        color: white;
        font-size: 22px;
        cursor: pointer;
    }

    /* Mensajes */
    .bautista-mensajes {
        flex: 1;
        padding: 18px;
        overflow-y: auto;
        background: #faf9ff;
    }

    .mensaje {
        max-width: 85%;
        padding: 11px 14px;
        border-radius: 15px;
        margin-bottom: 10px;
        font-size: 14px;
        line-height: 1.45;
    }

    .mensaje-bautista {
        background: #eee9fa;
        color: #33284d;
        border-bottom-left-radius: 4px;
    }

    .mensaje-usuario {
        margin-left: auto;
        background: #4c91d9;
        color: white;
        border-bottom-right-radius: 4px;
    }

    /* Preguntas rápidas */
    .preguntas-rapidas {
        padding: 10px 15px;
        background: white;
        border-top: 1px solid #eee;
    }

    .preguntas-rapidas p {
        margin: 0 0 7px;
        font-size: 11px;
        color: #777;
    }

    .preguntas-rapidas button {
        border: 1px solid #d8cdf1;
        background: #f8f5ff;
        color: #5d4099;
        border-radius: 20px;
        padding: 6px 10px;
        margin: 2px;
        cursor: pointer;
        font-size: 11px;
        transition: 0.2s;
    }

    .preguntas-rapidas button:hover {
        background: #e9e1fa;
    }

    /* Zona para escribir */
    .bautista-input {
        display: flex;
        gap: 8px;
        padding: 12px;
        background: white;
        border-top: 1px solid #eee;
    }

    .bautista-input input {
        flex: 1;
        border: 1px solid #dcd6ea;
        border-radius: 20px;
        padding: 10px 13px;
        outline: none;
        font-size: 13px;
    }

    .bautista-input input:focus {
        border-color: #6c4ab6;
    }

    .bautista-input button {
        width: 42px;
        height: 42px;
        border: none;
        border-radius: 50%;
        background: #6c4ab6;
        color: white;
        cursor: pointer;
        font-size: 17px;
    }

    /* Adaptación para celular */
    @media (max-width: 500px) {

        .bautista-chat {
            right: 10px;
            bottom: 90px;
            width: calc(100vw - 20px);
            height: 75vh;
        }

        .bautista-boton {
            right: 15px;
            bottom: 15px;
        }
    }
</style>


<!-- BOTÓN DE BAUTISTA -->
<button class="bautista-boton" onclick="abrirBautista()" title="Hablar con Bautista.bot">
    👨‍🍳
</button>


<!-- VENTANA DEL CHAT -->
<div class="bautista-chat" id="bautistaChat">

    <!-- ENCABEZADO -->
    <div class="bautista-header">

        <div class="bautista-avatar">
            🤖
        </div>

        <div>
            <h3>Bautista.bot 👨‍🍳</h3>
            <p>Asistente virtual de Gestión PAE</p>
        </div>

        <button class="bautista-cerrar" onclick="cerrarBautista()">
            ×
        </button>

    </div>


    <!-- MENSAJES -->
    <div class="bautista-mensajes" id="bautistaMensajes">

        <div class="mensaje mensaje-bautista">
            👋 ¡Hola! Soy <strong>Bautista.bot</strong> 👨‍🍳🤖
            <br><br>
            Estoy aquí para ayudarte con tus dudas sobre el PAE.
            <br><br>
            ¿Qué te gustaría saber?
        </div>

    </div>


    <!-- PREGUNTAS RÁPIDAS -->
    <div class="preguntas-rapidas">

        <p>💡 Preguntas frecuentes:</p>

        <button onclick="preguntarBautista('¿Qué es el PAE?')">
            🍎 ¿Qué es el PAE?
        </button>

        <button onclick="preguntarBautista('¿Quiénes se benefician?')">
            👨‍🎓 Beneficiarios
        </button>

        <button onclick="preguntarBautista('¿Cómo funciona?')">
            ⚙️ ¿Cómo funciona?
        </button>

        <button onclick="preguntarBautista('¿Para qué sirve?')">
            🎯 ¿Para qué sirve?
        </button>

    </div>


    <!-- INPUT -->
    <div class="bautista-input">

        <input
            type="text"
            id="bautistaEntrada"
            placeholder="Escribe tu pregunta..."
            onkeydown="if(event.key === 'Enter') enviarBautista()"
        >

        <button onclick="enviarBautista()">
            ➤
        </button>

    </div>

</div>


<script>

    // Abrir Bautista
    function abrirBautista() {
        document.getElementById("bautistaChat").classList.add("activo");
    }


    // Cerrar Bautista
    function cerrarBautista() {
        document.getElementById("bautistaChat").classList.remove("activo");
    }


    // Enviar pregunta escrita
    function enviarBautista() {

        const entrada = document.getElementById("bautistaEntrada");
        const pregunta = entrada.value.trim();

        if (pregunta === "") {
            return;
        }

        preguntarBautista(pregunta);

        entrada.value = "";
    }


    // Procesar pregunta
    function preguntarBautista(pregunta) {

        const mensajes = document.getElementById("bautistaMensajes");

        // Mostrar pregunta del usuario
        mensajes.innerHTML += `
            <div class="mensaje mensaje-usuario">
                ${pregunta}
            </div>
        `;


        // Convertir a minúsculas y quitar tildes
        const texto = pregunta
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");


        let respuesta = "";


        // ¿Qué es el PAE?
        if (
            texto.includes("que es el pae") ||
            texto.includes("pae")
        ) {

            respuesta = `
                🍎 El <strong>PAE</strong> es el Programa de Alimentación Escolar.
                <br><br>
                Busca apoyar a los estudiantes mediante la entrega de alimentación
                durante su jornada escolar.
            `;

        }
        // Significado del PAE
        else if (
            texto.includes("que significa pae") ||
            texto.includes("significado del pae")
        ) {

            respuesta = `
                🍎 <strong>PAE</strong> significa
                <strong>Programa de Alimentación Escolar</strong>.
                <br><br>
                Es un programa que brinda alimentación a estudiantes
                durante su jornada escolar.
            `;

        }


        // Qué entrega el PAE
        else if (
            texto.includes("que entrega") ||
            texto.includes("que dan") ||
            texto.includes("alimentacion")
        ) {

            respuesta = `
                🍽️ El PAE proporciona alimentación durante la jornada
                escolar, de acuerdo con la organización y las condiciones
                establecidas para la institución educativa.
            `;

        }

        // Beneficiarios
        else if (
            texto.includes("benefici") ||
            texto.includes("quienes reciben") ||
            texto.includes("quien recibe")
        ) {

            respuesta = `
                👨‍🎓 El PAE está dirigido principalmente a estudiantes
                que hacen parte de la institución educativa y cumplen
                con los criterios establecidos para recibir el beneficio.
            `;

        }
        // Estudiantes
        else if (
            texto.includes("estudiantes") ||
            texto.includes("alumnos")
        ) {

            respuesta = `
                👨‍🎓 Los estudiantes son los principales beneficiarios
                del Programa de Alimentación Escolar.
                <br><br>
                En <strong>Gestión PAE</strong> se puede organizar su
                información y registrar si recibieron la alimentación.
            `;

        }


        // Profesor
        else if (
            texto.includes("que hace el profesor") ||
            texto.includes("funcion del profesor") ||
            texto.includes("profesor")
        ) {

            respuesta = `
                👨‍🏫 El profesor puede consultar la información de su grupo
                y revisar los registros relacionados con las entregas del PAE.
            `;

        }


        // Personal PAE
        else if (
            texto.includes("personal pae") ||
            texto.includes("que hace el personal")
        ) {

            respuesta = `
                👨‍🍳 El personal PAE se encarga de realizar y registrar
                la entrega de alimentación a los estudiantes.
                <br><br>
                También puede registrar quién recibió y quién no recibió
                la alimentación.
            `;

        }


        // Administrador
        else if (
            texto.includes("administrador") ||
            texto.includes("que hace el administrador")
        ) {

            respuesta = `
                👑 El administrador gestiona la información del sistema,
                como estudiantes, cursos, profesores y personal PAE.
                <br><br>
                También puede consultar el historial de las entregas.
            `;

        }

        // Funcionamiento
        else if (
            texto.includes("como funciona") ||
            texto.includes("entrega") ||
            texto.includes("funciona")
        ) {

            respuesta = `
                ⚙️ El proceso comienza con la organización de los estudiantes
                por grupos y continúa con la entrega de la alimentación.
                <br><br>
                La información de cada entrega puede registrarse en
                <strong>Gestión PAE</strong>.
            `;

        }


        // Para qué sirve
        else if (
            texto.includes("para que sirve") ||
            texto.includes("utilidad") ||
            texto.includes("objetivo")
        ) {

            respuesta = `
                🎯 Gestión PAE sirve para organizar la información,
                registrar las entregas de alimentación, consultar
                los registros y conservar un historial.
            `;

        }


        // Login
        else if (
            texto.includes("iniciar sesion") ||
            texto.includes("ingresar") ||
            texto.includes("login")
        ) {

            respuesta = `
                🔐 Para ingresar al sistema puedes utilizar la opción
                <strong>Registrar</strong> que aparece en el menú principal.
            `;

        }


        // Saludo
        else if (
            texto.includes("hola") ||
            texto.includes("buenas") ||
            texto.includes("hey")
        ) {

            respuesta = `
                👋 ¡Holaaa! Qué gusto verte.
                <br><br>
                Soy Bautista.bot 👨‍🍳🤖.
                ¿Qué duda tienes sobre el PAE?
            `;

        }


        // Agradecimiento
        else if (
            texto.includes("gracias") ||
            texto.includes("muchas gracias")
        ) {

            respuesta = `
                💜 ¡Con mucho gusto!
                <br><br>
                Para eso estoy aquí. 👨‍🍳🤖
            `;

        }


        // No reconoce la pregunta
        else {

            respuesta = `
                🤔 Mmm... todavía no conozco la respuesta
                a esa pregunta.
                <br><br>
                Puedes preguntarme sobre <strong>el PAE, los beneficiarios,
                el funcionamiento o Gestión PAE</strong>.
            `;

        }


        // Mostrar respuesta
        setTimeout(() => {

            mensajes.innerHTML += `
                <div class="mensaje mensaje-bautista">
                    ${respuesta}
                </div>
            `;

            mensajes.scrollTop = mensajes.scrollHeight;

        }, 300);


        mensajes.scrollTop = mensajes.scrollHeight;
    }

</script>