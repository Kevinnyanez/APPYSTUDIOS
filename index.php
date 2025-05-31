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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f0f0; display:flex; justify-content:center; align-items:center; height:100vh; }
        form {
    background: #ffffff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

input {
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

button {
    background: #0d6efd;
    transition: background 0.3s;
    font-weight: bold;
}

button:hover {
    background: #0b5ed7;
}
form {
  animation: fadeIn 0.8s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
    </style>
</head>
<body>
    <img src="logo.png" alt="Logo" style="max-width:100px; margin: 0 auto 20px; display:block;">

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
