<?php
session_start();              // Inicia sesión para poder guardar mensajes y redirigir con alertas
require_once 'config.php';    // Incluye conexión PDO ($pdo) y configuración general

// Variables para mensaje unificado que se mostrará en el frontend
$mensaje = '';
$tipoMensaje = 'error'; // Por defecto, cualquier fallo se considera "error"

// Solo se procesa si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /**
     * SANITIZACIÓN DE ENTRADAS
     * - trim() elimina espacios al inicio y al final
     * - htmlspecialchars() previene inyección de HTML (XSS) cuando se reutilicen valores
     * - filter_var() con SANITIZE_EMAIL limpia caracteres inválidos del email
     */
    $nombre   = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';              // Contraseña: se valida, no se "sanitiza" porque es sensible
    $confirm  = $_POST['confirmPassword'] ?? '';       // Confirmación de contraseña
    $telefono = htmlspecialchars(trim($_POST['telefono'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Rol por defecto asignado a cualquier registro normal
    $tipoUsuario = 'user';

    // Arreglo para acumular errores del backend
    $errores = [];

    // =========================
    // VALIDACIONES EN BACKEND
    // =========================

    /**
     * Validación del nombre:
     * - No vacío
     * - Longitud mínima y máxima
     * - Solo letras y espacios
     */
    if (
        empty($nombre) ||
        strlen($nombre) < 2 ||
        strlen($nombre) > 100 ||
        !preg_match('/^[a-zA-Z\s]+$/', $nombre)
    ) {
        $errores[] = 'El nombre es inválido.';
    }

    /**
     * Validación del email:
     * - Formato válido con FILTER_VALIDATE_EMAIL
     */
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }

    /**
     * Validación de contraseña:
     * - Mínimo 8 caracteres
     * - Debe contener letras
     * - Debe contener números
     */
    if (
        strlen($password) < 8 ||
        !preg_match('/[a-zA-Z]/', $password) ||
        !preg_match('/\d/', $password)
    ) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres, letras y números.';
    }

    /**
     * Confirmación de contraseña:
     * - Ambas deben ser iguales
     */
    if ($password !== $confirm) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    /**
     * Validación del teléfono:
     * - No vacío
     * - Solo números (con + opcional)
     * - Longitud de 7 a 15 dígitos
     */
    if (empty($telefono) || !preg_match('/^\+?\d{7,15}$/', $telefono)) {
        $errores[] = 'El número de teléfono no es válido.';
    }

    // =========================
    // SI NO HAY ERRORES, REGISTRAR
    // =========================
    if (empty($errores)) {
        try {
            /**
             * Verificar si el correo ya existe:
             * - Consulta preparada con PDO
             * - Evita SQL Injection
             */
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);

            // Si existe un registro, evitar duplicados
            if ($stmt->fetch()) {
                $mensaje = 'El correo electrónico ya está registrado.';
            } else {

                /**
                 * Hash seguro de contraseña:
                 * - PASSWORD_DEFAULT usa el algoritmo recomendado por PHP
                 * - Nunca se guarda la contraseña en texto plano
                 */
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                /**
                 * Insertar el usuario:
                 * - Consulta preparada
                 * - Evita SQL Injection
                 */
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nombre, email, password, telefono, tipo_usuario)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$nombre, $email, $hashedPassword, $telefono, $tipoUsuario]);

                // Mensaje de éxito
                $mensaje = 'Registro exitoso. ¡Bienvenido!';
                $tipoMensaje = 'success';
            }

        } catch (PDOException $e) {
            /**
             * Manejo de error interno:
             * - Se registra el error real en logs
             * - Al usuario solo se le muestra un mensaje genérico
             */
            error_log("Error en registro: " . $e->getMessage(), 3, "logs/errors.log");
            $mensaje = 'Error interno del servidor. Inténtalo más tarde.';
        }

    } else {
        /**
         * Si hubo errores:
         * - Se unen todos en un solo mensaje para mostrarlo en el frontend
         */
        $mensaje = implode(' ', $errores);
    }
}

// =========================
// MENSAJE UNIFICADO + REDIRECCIÓN
// =========================

// Guarda el mensaje para mostrarlo en el formulario
$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipoMensaje'] = $tipoMensaje;

/**
 * Redirige al formulario real del registro.
 * OJO: si tu formulario se llama diferente (registro.php, index.html, etc.)
 * aquí debe coincidir.
 */
header('Location: index.php');
exit;
