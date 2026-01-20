<?php
$host = 'localhost';  
$dbname = 'registro_usuarios';
$username = 'root';  
$password = '';      

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);  
} catch (PDOException $e) {
    error_log("Error de conexión a DB: " . $e->getMessage(), 3, "logs/errors.log");
    die("Error interno del servidor. Inténtalo más tarde.");
}
?>