<?php

require_once 'config.php'; 
// Incluye la configuración del sistema, normalmente aquí viene:
// - Conexión PDO en $pdo
// - Configuración base del sistema

// Variables para enviar un mensaje al usuario (por sesión)
$mensaje = '';
$tipoMensaje = 'error'; 
// Por defecto, si algo falla, se marca como error

// Solo procesar si el formulario se envió por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitiza el email eliminando espacios y caracteres inválidos
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

    // Obtiene la contraseña (no se sanitiza con FILTER porque debe compararse tal cual)
    $password = trim($_POST['password'] ?? '');

    // =========================
    // VALIDACIONES EN SERVIDOR
    // =========================

    // Valida formato correcto del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Correo electrónico inválido.';

    // Valida que la contraseña no venga vacía
    } elseif (empty($password)) {
        $mensaje = 'Contraseña requerida.';

    } else {
        try {
            /**
             * Consulta preparada para buscar el usuario por email.
             * - Selecciona: id, nombre, password (hash), tipo de usuario, y session_expiry (opcional).
             * - WHERE email = ? -> evita concatenación vulnerable (SQL Injection).
             */
            $stmt = $pdo->prepare("
                SELECT id, nombre, password, tipo_usuario, session_expiry
                FROM usuarios
                WHERE email = ?
            ");

            // Ejecuta la consulta pasando el email como parámetro
            $stmt->execute([$email]);

            // Obtiene la fila del usuario (si existe)
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si el usuario no existe, mensaje genérico
            if (!$user) {
                $mensaje = 'Credenciales inválidas.';

            // Verifica la contraseña contra el hash guardado en BD
            } elseif (!password_verify($password, $user['password'])) {
                $mensaje = 'Credenciales inválidas.';

            // Si hay fecha de expiración y ya pasó, bloquea el acceso
            } elseif ($user['session_expiry'] && strtotime($user['session_expiry']) < time()) {
                $mensaje = 'Sesión expirada. Contacta al administrador.';

            } else {
                // =========================
                // LOGIN EXITOSO (SESION SEGURA)
                // =========================

                // Inicia sesión para guardar datos del usuario autenticado
                session_start();

                // Regenera el ID de sesión para prevenir session fixation
                session_regenerate_id(true);

                // Guarda datos mínimos necesarios en sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_type'] = $user['tipo_usuario'];

                // Guarda el momento del login (sirve para expirar por tiempo en paneles)
                $_SESSION['login_time'] = time();

                // Redirige según el rol del usuario
                // - Si es admin → panel_admin.php
                // - Si no → panel_user.php
                header('Location: ' . ($user['tipo_usuario'] === 'admin' ? 'panel_admin.php' : 'panel_user.php'));
                exit();
            }

        } catch (PDOException $e) {
            /**
             * Si ocurre un error de base de datos:
             * - Se registra en un log del servidor (no se muestra al usuario).
             * - Se muestra un mensaje genérico por seguridad.
             */
            error_log("Error en login: " . $e->getMessage(), 3, "logs/errors.log");
            $mensaje = 'Error interno del servidor.';
        }
    }
}

// =========================
// SI FALLÓ: ENVIAR MENSAJE A LOGIN
// =========================

// IMPORTANTE: se inicia sesión para poder guardar el mensaje de error
session_start();

// Guarda el mensaje y el tipo para mostrarlo en login.html
$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipoMensaje'] = $tipoMensaje;

// Redirige a la pantalla de login para mostrar el mensaje
header('Location: login.html');
exit();
?>
