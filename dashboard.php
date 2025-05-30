<?php


session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

$nombre = $_SESSION['nombre_usuario'];
$sql = "SELECT * FROM stock";

$result = $conn->query($sql);

$sql_clientes = "SELECT * FROM clientes";
$result_clientes = $conn ->query($sql_clientes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <style>

        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #cbd5e1;
            padding: 20px;
            margin: 0;
        }

        nav {
            display: flex;
            align-items: center;
            background: #1f2937;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgb(0 0 0 / 0.1);
            margin-bottom: 30px;
        }

        nav a {
            color: #cbd5e1;
            text-decoration: none;
            margin-right: 25px;
            font-weight: 600;
            transition: color 0.3s ease;
            padding: 6px 8px;
            border-radius: 4px;
        }

        nav a:hover {
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.15);
        }

        nav a.logout {
            margin-left: auto;
            background: #ef4444;
            color: white !important;
            padding: 8px 15px;
            font-weight: 700;
            transition: background 0.3s ease;
        }

        nav a.logout:hover {
            background: #b91c1c;
        }

        /* Contenedor general con flex para dividir en columnas */
        .dashboard {
            display: flex;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap; /* para que sea responsivo */
        }

        .welcome-container {
            background: #ffffff;
            border-left: 5px solid #38bdf8;
            padding: 20px 25px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            flex: 1 1 300px;
            font-size: 1.1em;
            color: #334155;
            line-height: 1.5;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            min-width: 280px;
        }

        .welcome-container:hover {
            background-color: #e0f2fe;
            box-shadow: 0 4px 12px rgba(56,189,248,0.3);
            border-color: #0284c7;
            cursor: default;
        }

        .welcome-container strong {
            color: #2563eb;
            font-weight: 700;
        }

        .welcome-container p {
            margin-top: 12px;
            color: #64748b;
            font-weight: 500;
        }

        /* Contenedor tabla stock */
        table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
  background-color: #222;
  border-radius: 8px;
  overflow: hidden;
}

th, td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #444;
}

th {
  background-color: #333;
  color: #fff;
  font-weight: bold;
  font-size: 14px;
}

td {
  font-size: 14px;
  color: #fff;
}

tr:hover {
  background-color:#383838;
  transition: background-color 0.3s ease;
}

a {
  color: #007bff;
  text-decoration: none;
  margin: 0 5px;
}

a:hover {
  text-decoration: underline;
}

.btn-volver {
  display: inline-block;
  margin-top: 20px;
  padding: 10px 16px;
  background-color: #007bff;
  color: white;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  transition: background-color 0.2s ease;
}

.btn-volver:hover {
  background-color: #0056b3;
}
 

        /* Responsive básico */
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            nav a {
                margin: 8px 10px;
            }

            nav a.logout {
                margin-left: 0;
                width: 100%;
                text-align: center;
            }
        }
        
        .btn-volver {
            
            display: inline-block; padding: 8px 12px; background: #222; color: white; text-decoration: none; border-radius: 4px; margin: 10px;
        }
    </style>
</head>
<body>
    <nav>
        <a href="stock.php">Ver Stock</a>
        <a href="presupuestos.php">Presupuestos</a>
        <a href="ventas.php">Ventas</a>
        <a href="clientes.php">Clientes</a>
        <a href="recomendaciones.php">Recomendaciones</a>
        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </nav>

    <div class="dashboard">
        <div class="welcome-container">
            Bienvenido, <strong><?= htmlspecialchars($nombre) ?></strong> a tu panel de control.
            <p>Desde aquí podés administrar el stock, crear presupuestos y controlar las ventas.</p>
        </div>

       <table>
    <thead>
        <tr>
            <th>ID</th><th>Nombre</th><th>Descripción</th><th>Cantidad</th><th>Precio Unitario</th><th>Tipo</th><th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($row['id_stock'])?></td>
                <td><?=htmlspecialchars($row['nombre'])?></td>
                <td><?=htmlspecialchars($row['descripcion'])?></td>
                <td><?=htmlspecialchars($row['cantidad'])?></td>
                <td>$<?=number_format($row['precio_unitario'], 2)?></td>
                <td><?=htmlspecialchars($row['tipo'])?></td>
                <td>
                    <a href="stock_form.php?id=<?= $row['id_stock'] ?>">Editar</a> |
                    <a href="stock_action.php?action=delete&id=<?= $row['id_stock'] ?>" onclick="return confirm('¿Seguro querés eliminar este item?');">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No se encontraron items.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<a href="stock.php" class="btn-volver">Ir a Stock</a>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Nombre</th><th>Gmail</th><th>Telefono</th><th>Direccion</th><th>Fecha de Registro</th><th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result_clientes && $result_clientes->num_rows > 0): ?>
            <?php while($row = $result_clientes->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($row['id_cliente'])?></td>
                <td><?=htmlspecialchars($row['nombre'])?></td>
                <td><?=htmlspecialchars($row['email'])?></td>
                <td><?=htmlspecialchars($row['telefono'])?></td>
                <td><?=htmlspecialchars($row['direccion'])?></td>
                <td><?=htmlspecialchars($row['fecha_registro'])?></td>
                <td>
                    <a href="clientes.php?id=">Editar</a> |
                    <a href="clientes.php">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No se encontraron items.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<a href="clientes.php" class="btn-volver">Ir a Clientes</a>

    </div>
</body>
</html>
