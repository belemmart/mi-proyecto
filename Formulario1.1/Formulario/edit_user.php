<?php


session_start(); // Inicia sesión para verificar autenticación y rol

/**
 * CONTROL DE ACCESO
 * - Si no hay sesión o no es admin, se bloquea el acceso.
 */
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit();
}

// Conexión a base de datos con PDO
require_once 'config.php';

/**
 * VALIDACIÓN DEL ID RECIBIDO POR GET
 * - Se sanitiza para que solo queden números.
 * - Se valida que sea numérico y exista.
 */
$id = filter_var($_GET['id'] ?? '', FILTER_SANITIZE_NUMBER_INT);

if (!$id || !is_numeric($id)) {
    // Si el ID no es válido, se regresa al panel
    header('Location: panel_admin.php');
    exit();
}

/**
 * CARGA DEL USUARIO A EDITAR
 * - Se busca en la base por su ID.
 * - Se valida que exista realmente.
 */
$user = null;

try {
    // Consulta preparada para evitar SQL Injection
    $stmt = $pdo->prepare("SELECT nombre, tipo_usuario FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);

    // Obtiene los datos del usuario
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no existe, se lanza una excepción controlada
    if (!$user) {
        throw new Exception("Usuario no encontrado.");
    }

} catch (Exception $e) {
    /**
     * Manejo de errores:
     * - Se registra el error real en logs
     * - Se redirige al panel sin exponer detalles
     */
    error_log("Error al cargar usuario: " . $e->getMessage(), 3, "logs/errors.log");
    header('Location: panel_admin.php');
    exit();
}

/**
 * SI SE ENVÍA EL FORMULARIO (POST)
 * - Se valida el nuevo tipo de usuario.
 * - Se actualiza en BD si es válido.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitiza el valor recibido
    $tipo = filter_var($_POST['tipo_usuario'] ?? '', FILTER_SANITIZE_STRING);

    /**
     * Validación estricta:
     * - Solo se permiten los valores "user" o "admin"
     */
    if (!in_array($tipo, ['user', 'admin'], true)) {
        $mensaje = 'Tipo inválido.';
    } else {
        try {
            // Actualización segura con consulta preparada
            $stmt = $pdo->prepare("UPDATE usuarios SET tipo_usuario = ? WHERE id = ?");
            $stmt->execute([$tipo, $id]);

            // Si todo sale bien, regresa al panel
            header('Location: panel_admin.php');
            exit();

        } catch (PDOException $e) {
            /**
             * Manejo de error de BD:
             * - Log interno
             * - Mensaje genérico para el usuario
             */
            error_log("Error al editar: " . $e->getMessage(), 3, "logs/errors.log");
            $mensaje = 'Error interno.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Codificación -->
    <meta charset="UTF-8">

    <!-- Título de la vista -->
    <title>Editar Usuario</title>

    <!-- Estilos externos -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Contenedor principal -->
    <div class="edit-container">

        <!-- Tarjeta visual -->
        <div class="edit-card">

            <h1>Editar Usuario</h1>

            <!-- Muestra el nombre del usuario a editar -->
            <p class="edit-subtitle">
                Usuario: <strong><?= htmlspecialchars($user['nombre']) ?></strong>
            </p>

            <!-- Si existe un mensaje de error, se muestra -->
            <?php if (isset($mensaje)): ?>
                <p class="error"><?= htmlspecialchars($mensaje) ?></p>
            <?php endif; ?>

            <!-- Formulario para cambiar el rol -->
            <form method="POST">

                <label for="tipo_usuario">Tipo de usuario</label>

                <!-- Select con valor actual preseleccionado -->
                <select name="tipo_usuario" id="tipo_usuario" required>
                    <option value="user" <?= $user['tipo_usuario'] === 'user' ? 'selected' : '' ?>>
                        Usuario
                    </option>
                    <option value="admin" <?= $user['tipo_usuario'] === 'admin' ? 'selected' : '' ?>>
                        Administrador
                    </option>
                </select>

                <!-- Botón de guardar -->
                <button type="submit" class="btn-save">Guardar cambios</button>

            </form>

        </div>

    </div>

</body>
</html>
