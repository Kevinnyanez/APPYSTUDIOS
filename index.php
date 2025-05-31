<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo {
            max-width: 180px;
            margin-bottom: 20px;
        }

        form {
            background: white;
            padding: 25px 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 320px;
            animation: fadeIn 0.8s ease-in-out;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .error {
            color: red;
            margin-bottom: 10px;
            font-size: 0.9em;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px;
            cursor: pointer;
            width: 100%;
            border-radius: 5px;
            font-weight: 600;
        }

        button:hover {
            background: #0056b3;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="logo.jpg" alt="Logo" class="logo">

        <form method="POST" action="">
            <h2 style="text-align:center;">Iniciar Sesión</h2>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <input type="text" name="nombre_usuario" placeholder="Nombre de usuario" required />
            <input type="password" name="clave" placeholder="Contraseña" required />
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
