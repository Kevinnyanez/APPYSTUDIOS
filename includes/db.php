<?php
// Configuración de conexión
$host = 'localhost'; // o la IP o dominio del servidor si es remoto
$usuario = 'u101881599_KevinYanez11';
$contrasena = 'Bocapasion11.';
$base_de_datos = 'u101881599_martinp';

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Opcional: establecer juego de caracteres
$conn->set_charset("utf8");
?>
