<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recomendaciones de Uso</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        ul.recomendaciones {
            list-style: none;
            padding: 0;
        }

        ul.recomendaciones li {
            background-color: #ecf0f1;
            border-left: 6px solid #3498db;
            margin-bottom: 15px;
            padding: 15px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        ul.recomendaciones li:hover {
            background-color: #d0e9ff;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            color: #7f8c8d;
            font-size: 14px;
        }

        nav {
  background: #232b36;
  border-radius: 12px;
  padding: 22px 36px 18px 36px;
  margin-bottom: 32px;
  display: flex;
  align-items: center;
  gap: 36px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.10);
}
nav a {
  color: #fff;
  text-decoration: none;
  font-size: 1.18rem;
  font-weight: 500;
  margin-right: 18px;
  transition: color 0.18s, background 0.18s, box-shadow 0.18s;
  padding: 6px 12px;
  border-radius: 6px;
}
nav a:hover {
  background: #0077b6;
  color: #fff;
  box-shadow: 0 2px 8px rgba(0,119,182,0.10);
}
nav .logout {
  margin-left: auto;
  background: #ef4444;
  color: #fff;
  font-weight: 700;
  border-radius: 8px;
  padding: 8px 22px;
  font-size: 1.1rem;
  box-shadow: 0 2px 8px rgba(239,68,68,0.10);
  transition: background 0.18s, color 0.18s;
}
nav .logout:hover {
  background: #b91c1c;
  color: #fff;
}

.minimal-tagline {
    font-family: 'Segoe UI', sans-serif;
    font-size: 12px;
    color: #666;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 500;
    padding: 5px 10px;
    border-left: 2px solid #ccc;
    margin-left: 10px;
    display: inline-block;
}
    </style>
</head>
<body>
<?php include 'header.php'; ?>

    <div class="container">
        <h1>Recomendaciones desde Appy Studios Desarrollo Web</h1>
        <ul class="recomendaciones">
            <li>Guardá tus cambios frecuentemente para evitar pérdida de información.</li>
            <li>Usá navegadores modernos y actualizados para evitar incompatibilidades.</li>
            <li>No compartas tu contraseña ni datos personales con nadie.</li>
            <li>Cerrá sesión si usás la app en dispositivos públicos.</li>
            <li>Ante errores inesperados, recargá la página o volvé a iniciar sesión.</li>
            <li>Limpiá la caché si notás datos desactualizados.</li>
            <li>Consultá con tu administrados siempre que lo necesites.</li>
            <li>Si encontrás un bug, avisanos. Nos ayuda a mejorar 🚀.</li>
        </ul>
        <footer>
            &copy; <?= date("Y") ?> Presupuestos <span class="minimal-tagline">FD | Soñando Bajito! </span> | Hecho con ❤️ por el equipo de desarrollo de Appy Studios

        </footer>
    </div>
</body>
</html>
