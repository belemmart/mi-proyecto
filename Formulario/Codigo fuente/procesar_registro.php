<?php
session_start();
require_once 'config.php';

$mensaje = '';
$tipoMensaje = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');

    $errores = [];

    if (empty($nombre) || strlen($nombre) < 2 || strlen($nombre) > 100 || !preg_match('/^[a-zA-Z\s]+$/', $nombre)) {
        $errores[] = 'Nombre inválido.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Correo electrónico inválido.';
    }

    if (strlen($password) < 8 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
        $errores[] = 'Contraseña insegura.';
    }

    if ($password !== $confirmPassword) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!preg_match('/^\+?\d{7,15}$/', $telefono)) {
        $errores[] = 'Teléfono inválido.';
    }

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $mensaje = 'El correo ya está registrado.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "INSERT INTO usuarios (nombre, email, password, telefono)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([$nombre, $email, $hashedPassword, $telefono]);

                $mensaje = 'Registro exitoso.';
                $tipoMensaje = 'success';
            }
        } catch (PDOException $e) {
            error_log("Error registro: " . $e->getMessage(), 3, __DIR__ . "/logs/errors.log");
            $mensaje = 'Error interno del servidor.';
        }
    } else {
        $mensaje = implode(' ', $errores);
    }
}

$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipoMensaje'] = $tipoMensaje;

header('Location: index.php');
exit;
