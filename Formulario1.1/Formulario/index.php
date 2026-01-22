<?php
// Inicia la sesión para poder usar variables de sesión
// Se utiliza para mostrar mensajes entre páginas (backend → frontend)
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración de caracteres -->
    <meta charset="UTF-8">

    <!-- Título de la página -->
    <title>Registro de Usuario</title>

    <!-- Hace que el diseño sea responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Archivo de estilos externo -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">

    <!-- Título principal del formulario -->
    <h1>Registro de Usuario</h1>

    <!--
        ALERTA UNIFICADA
        - Muestra mensajes enviados desde el backend (PHP)
        - También se reutiliza para mostrar errores del frontend (JS)
    -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div id="mensaje" class="<?php echo $_SESSION['tipoMensaje']; ?>">
            <?php
            // Evita inyección de HTML al mostrar el mensaje
            echo htmlspecialchars($_SESSION['mensaje']);

            // Elimina los mensajes después de mostrarlos
            unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);
            ?>
        </div>
    <?php else: ?>
        <!-- Contenedor vacío para mensajes del frontend -->
        <div id="mensaje"></div>
    <?php endif; ?>

    <!--
        FORMULARIO DE REGISTRO
        - Envía los datos por POST
        - Se procesan en procesar_registro.php
    -->
    <form id="registroForm" action="procesar_registro.php" method="POST">

        <!-- Campo: Nombre -->
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>

        <!-- Campo: Correo electrónico -->
        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>

        <!-- Campo: Contraseña -->
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <!-- Campo: Confirmar contraseña -->
        <label for="confirmPassword">Confirmar Contraseña:</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required>

        <!-- Campo: Teléfono -->
        <label for="telefono">Número de Teléfono:</label>
        <input type="tel" id="telefono" name="telefono" required>

        <!-- Botones de acción -->
        <div class="acciones">
            <button type="submit">Registrar</button>
            <a href="login.html" class="btn-login">Volver a Login</a>
        </div>

    </form>

</div>

<script>
// Validación del formulario del lado del cliente (Frontend)
document.getElementById('registroForm').addEventListener('submit', function(e) {

    // Arreglo para almacenar errores
    let errores = [];

    // Obtención de valores del formulario
    const nombre = document.getElementById('nombre').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const telefono = document.getElementById('telefono').value.trim();
    const mensaje = document.getElementById('mensaje');

    // Limpia mensajes previos
    mensaje.textContent = '';
    mensaje.className = '';

    // Validación del nombre (solo letras y mínimo 2 caracteres)
    if (nombre.length < 2 || !/^[a-zA-Z\s]+$/.test(nombre)) {
        errores.push('El nombre es inválido.');
    }

    // Validación del correo electrónico
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errores.push('El correo electrónico no es válido.');
    }

    // Validación de la contraseña
    // Mínimo 8 caracteres, letras y números
    if (password.length < 8 || !/[a-zA-Z]/.test(password) || !/\d/.test(password)) {
        errores.push('La contraseña debe tener al menos 8 caracteres, letras y números.');
    }

    // Verifica que ambas contraseñas coincidan
    if (password !== confirmPassword) {
        errores.push('Las contraseñas no coinciden.');
    }

    // Validación del teléfono (solo números, con o sin +)
    if (!/^\+?\d{7,15}$/.test(telefono)) {
        errores.push('El número de teléfono no es válido.');
    }

    // Si hay errores, se evita el envío del formulario
    if (errores.length > 0) {
        e.preventDefault();
        mensaje.className = 'error';
        mensaje.innerHTML = errores.join('<br>');
    }
});
</script>

</body>
</html>
