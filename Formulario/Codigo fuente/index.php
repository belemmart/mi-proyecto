<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">

    <h1>Registro de Usuario</h1>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje <?php echo $_SESSION['tipoMensaje']; ?>">
            <?php echo htmlspecialchars($_SESSION['mensaje']); ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']); ?>
    <?php endif; ?>

    <form id="registroForm" action="procesar_registro.php" method="POST">

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>
        <span class="error" id="errorNombre"></span>

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>
        <span class="error" id="errorEmail"></span>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        <span class="error" id="errorPassword"></span>

        <label for="confirmPassword">Confirmar Contraseña:</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required>
        <span class="error" id="errorConfirmPassword"></span>

        <label for="telefono">Número de Teléfono:</label>
        <input type="tel" id="telefono" name="telefono" required>
        <span class="error" id="errorTelefono"></span>

        <button type="submit">Registrar</button>
    </form>

</div>

<script>
document.getElementById('registroForm').addEventListener('submit', function(e) {
    let valid = true;

    document.querySelectorAll('.error').forEach(el => el.textContent = '');

    const nombre = nombreInput = document.getElementById('nombre').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const telefono = document.getElementById('telefono').value.trim();

    if (nombre.length < 2 || !/^[a-zA-Z\s]+$/.test(nombre)) {
        errorNombre.textContent = 'Nombre inválido.';
        valid = false;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errorEmail.textContent = 'Correo inválido.';
        valid = false;
    }

    if (password.length < 8 || !/[a-zA-Z]/.test(password) || !/\d/.test(password)) {
        errorPassword.textContent = 'Contraseña insegura.';
        valid = false;
    }

    if (password !== confirmPassword) {
        errorConfirmPassword.textContent = 'No coinciden.';
        valid = false;
    }

    if (!/^\+?\d{7,15}$/.test(telefono)) {
        errorTelefono.textContent = 'Teléfono inválido.';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>

</body>
</html>
