<?php
// Inicia la sesión para acceder a variables de sesión
session_start();

/**
 * CONTROL DE ACCESO
 * - Verifica que exista sesión activa.
 * - Verifica que el rol del usuario sea "user".
 */
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: login.html');
    exit();
}

/**
 * CONTROL DE EXPIRACIÓN DE SESIÓN (opcional)
 * - Si han pasado más de 3600 segundos (1 hora),
 *   se cierra la sesión y se redirige al login.
 */
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 3600) {
    session_destroy();
    header('Location: login.html');
    exit();
}

// Incluye la configuración de la base de datos (PDO)
require_once 'config.php';

// Obtiene el ID del usuario autenticado desde la sesión
$userId = $_SESSION['user_id'];

// Variable para almacenar los datos del usuario
$user = null;

try {
    /**
     * Consulta preparada para obtener los datos del usuario.
     * - Usa el ID de sesión, no datos enviados por el usuario.
     * - Previene SQL Injection.
     */
    $stmt = $pdo->prepare("
        SELECT nombre, email, telefono
        FROM usuarios
        WHERE id = ?
    ");

    // Ejecuta la consulta con el ID del usuario
    $stmt->execute([$userId]);

    // Obtiene los datos del usuario
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no existe el usuario, se considera error de integridad
    if (!$user) {
        throw new Exception("Usuario no encontrado.");
    }

} catch (Exception $e) {
    /**
     * Manejo de errores:
     * - Se registra el error en el log del servidor.
     * - No se muestran detalles al usuario.
     * - Se destruye la sesión por seguridad.
     */
    error_log("Error al cargar perfil: " . $e->getMessage(), 3, "logs/errors.log");
    session_destroy();
    header('Location: login.html');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Codificación de caracteres -->
    <meta charset="UTF-8">

    <!-- Título del panel -->
    <title>Panel Usuario</title>

    <!-- Hoja de estilos principal -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Contenedor principal del panel del usuario -->
    <div class="user-container">

        <!-- Tarjeta del usuario -->
        <div class="user-card">

            <!-- Mensaje de bienvenida -->
            <h1>Bienvenido, <?= htmlspecialchars($user['nombre']) ?></h1>

            <!-- Información del usuario -->
            <div class="user-info">
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($user['telefono']) ?></p>
            </div>

            <!-- Botón para cerrar sesión -->
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>

        </div>

    </div>

</body>
</html>
