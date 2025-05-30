<?php
// Iniciar sesión si no está iniciada
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redirigir al login o página principal
header("Location: index.php"); // Cambia esto por index.php si no tenés login
exit;
?>
