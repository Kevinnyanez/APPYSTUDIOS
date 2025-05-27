<?php
session_start();
require_once 'includes/db.php'; // Archivo con conexión $conn

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $clave = trim($_POST['clave'] ?? '');

    if ($nombre_usuario === '' || $clave === '') {
        $error = 'Por favor ingresa nombre y contraseña.';
    } else {
        // Prepara consulta para evitar SQL Injection
        $stmt = $conn->prepare('SELECT id, nombre_usuario, clave FROM usuarios WHERE nombre_usuario = ?');
        $stmt->bind_param('s', $nombre_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Como la contraseña es sencilla, comparamos directamente
            if ($clave === $user['clave']) {
                // Login exitoso
                $_SESSION['id'] = $user['id'];
                $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'Usuario no encontrado.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; display:flex; justify-content:center; align-items:center; height:100vh; }
        form { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px #ccc; width: 300px; }
        input { width: 100%; padding: 8px; margin: 8px 0; box-sizing: border-box; }
        .error { color: red; margin-bottom: 10px; }
        button { background: #007bff; color: white; border: none; padding: 10px; cursor: pointer; width: 100%; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h2>Iniciar Sesión</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <input type="text" name="nombre_usuario" placeholder="Nombre" required />
        <input type="password" name="clave" placeholder="Contraseña" required />
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
