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
    </style>
</head>
<body>
    <div class="container">
        <h1>Recomendaciones de los Desarrolladores</h1>
        <ul class="recomendaciones">
            <li>Guardá tus cambios frecuentemente para evitar pérdida de información.</li>
            <li>Usá navegadores modernos y actualizados para evitar incompatibilidades.</li>
            <li>No compartas tu contraseña ni datos personales con nadie.</li>
            <li>Cerrá sesión si usás la app en dispositivos públicos.</li>
            <li>Ante errores inesperados, recargá la página o volvé a iniciar sesión.</li>
            <li>Limpiá la caché si notás datos desactualizados.</li>
            <li>Consultá los tutoriales o la sección de ayuda para sacarle más provecho a la app.</li>
            <li>Si encontrás un bug, avisanos. Nos ayuda a mejorar 🚀.</li>
        </ul>
        <footer>
            &copy; <?= date("Y") ?> TuAppWeb | Hecho con ❤️ por el equipo de desarrollo
        </footer>
    </div>
</body>
</html>
