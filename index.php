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
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #dfe9f3 0%, #ffffff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            position: relative;
        }
        .contenedor-principal {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  gap: 40px;
  margin-top: 60px;
}

        .login-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 40px;
            flex: 0 0 auto;
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
            text-align: center;
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
      .marquesina {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 40px;
  background: linear-gradient(90deg, #007bff, #00c3ff, #007bff);
  background-size: 300% 100%;
  animation: bgMove 6s linear infinite;
  overflow: hidden;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.marquesina-texto {
  white-space: nowrap;
  display: inline-block;
  animation: scrollText 20s linear infinite;
  font-weight: bold;
  font-size: 16px;
  color: white;
  text-shadow: 0 0 10px #00000060;
  filter: drop-shadow(0 0 2pxrgb(37, 37, 37));
  background: linear-gradient(to right,rgb(95, 95, 95),rgb(94, 100, 104),rgb(70, 70, 70));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

@keyframes scrollText {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}

@keyframes bgMove {
  0% { background-position: 0% 0%; }
  100% { background-position: 300% 0%; }
}


@keyframes scrollMarquesina {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-100%);
  }
}

 
    .noticia {
      border-bottom: 1px solid #ddd;
      margin-bottom: 15px;
      padding-bottom: 10px;
    }
    .noticia h4 {
      margin: 0;
      color: #007BFF;
    }
    .noticia small {
      color: #666;
    }

    /* EXISTENTE */
.contenedor-principal {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  gap: 40px;
  margin-top: 60px;
  flex-wrap: wrap; /* ✅ Agregado para que se acomode en pantallas chicas */
}

/* MODIFICAR .noticias: sacamos el position absolute */
.noticias {
  background: #f9f9f9;
  padding: 20px;
  border-radius: 10px;
  width: 320px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  margin-top: 30px;
}

/* MEDIA QUERY para celulares */
@media (max-width: 768px) {
  .contenedor-principal {
    flex-direction: column; /* ✅ Vertical en móviles */
    align-items: center;
  }

  .noticias {
    position: static;  /* ✅ Evitamos superposición */
    width: 90%;         /* ✅ Adaptado al ancho de pantalla */
    margin-top: 20px;
  }

  form {
    width: 90%; /* Más adaptable */
  }
}

    </style>
</head>
<body>

        <div class="marquesina">
  <div class="marquesina-texto">Appy Studios Desarrollos Webs | Soluciones creativas | ¡Tu idea, nuestro código!</div>
</div>

   <div class="contenedor-principal">

    <div class="login-container">
        

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
    <section class="noticias" id="noticias">
    <h3>📢 Comunicados Importantes</h3>
    <!-- Aquí se cargarán las noticias desde JS -->
  </section>
            
    </div>

  <script src="noticias.js"></script>
</body>
</html>
