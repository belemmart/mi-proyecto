<?php
// Inicia la sesión para poder destruirla
session_start();

// Elimina todas las variables almacenadas en la sesión
$_SESSION = [];

// Destruye la sesión actual
session_destroy();

// Redirige al usuario a la página de inicio de sesión
header('Location: login.html');
exit();
?>
