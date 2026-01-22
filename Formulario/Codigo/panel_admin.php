<?php
// Inicia la sesión para acceder a variables de sesión
session_start();

/**
 * CONTROL DE ACCESO
 * - Verifica que exista sesión activa.
 * - Verifica que el usuario tenga rol de administrador.
 */
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit();
}

/**
 * CONTROL DE EXPIRACIÓN DE SESIÓN
 * - Si han pasado más de 3600 segundos (1 hora) desde el login,
 *   se destruye la sesión y se redirige al login.
 */
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 3600) {
    session_destroy();
    header('Location: login.html');
    exit();
}

// Incluye configuración de base de datos (PDO)
require_once 'config.php';

// Arreglo donde se almacenarán los usuarios
$users = [];

try {
    /**
     * Consulta para obtener la lista de usuarios
     * - No recibe parámetros del usuario, por lo tanto es segura.
     * - Obtiene: id, nombre, email y tipo de usuario.
     */
    $stmt = $pdo->query("
        SELECT id, nombre, email, tipo_usuario
        FROM usuarios
    ");

    // Obtiene todos los registros en un arreglo asociativo
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    /**
     * Manejo de errores:
     * - El error se registra en un archivo de log del servidor.
     * - No se muestra información sensible al usuario.
     * - Se destruye la sesión y se redirige al login.
     */
    error_log("Error al cargar usuarios: " . $e->getMessage(), 3, "logs/errors.log");
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
    <title>Panel Administrador</title>

    <!-- Hoja de estilos principal -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Contenedor principal del panel -->
    <div class="admin-container">

        <!-- Tarjeta principal -->
        <div class="admin-card">

            <!-- Encabezado del panel -->
            <div class="admin-header">
                <h1>Panel Administrador</h1>

                <!-- Botón para cerrar sesión -->
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>

            <!-- Tabla de usuarios -->
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>

                <!-- Recorre la lista de usuarios -->
                <?php foreach ($users as $u): ?>
                    <tr>
                        <!-- htmlspecialchars evita XSS -->
                        <td><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['tipo_usuario']) ?></td>
                        <td>
                            <!-- Enlace para editar usuario -->
                            <a href="edit_user.php?id=<?= urlencode($u['id']) ?>" class="btn-edit">
                                Editar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>

        </div>

    </div>

</body>
</html>
