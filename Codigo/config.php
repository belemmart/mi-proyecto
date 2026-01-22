<?php
// Servidor de base de datos
$host = 'localhost';

// Nombre de la base de datos
$dbname = 'registro_usuarios';

// Usuario de base de datos con permisos limitados (CRUD necesario)
$username = 'app_user';

// Contraseña del usuario de la base de datos
//  En producción, esta contraseña debería almacenarse en variables de entorno
$password = 'secure_password_123';

// =========================
// CONEXIÓN PDO
// =========================

try {
    /**
     * Se crea una instancia de PDO:
     * - charset=utf8 asegura correcta codificación de caracteres
     * - Se evita el uso de consultas emuladas
     */
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );

    /**
     * Configuración de atributos de seguridad:
     * - ERRMODE_EXCEPTION: Lanza excepciones en errores SQL
     * - EMULATE_PREPARES false: Usa consultas preparadas reales
     */
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    /**
     * Manejo seguro de errores:
     * - El error real se guarda en un archivo de log del servidor
     * - No se muestra información sensible al usuario
     */
    error_log(
        "Error de conexión a DB: " . $e->getMessage(),
        3,
        "logs/errors.log"
    );

    // Mensaje genérico para el usuario
    die("Error interno del servidor. Inténtalo más tarde.");
}
?>
