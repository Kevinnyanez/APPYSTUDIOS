<?php
include 'includes/db.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM clientes WHERE id_cliente = $id");
    header("Location: clientes.php"); // redirigí a la lista limpia
    exit;
}
// cargamos $conn

// Cargar cliente para editar si viene id_cliente
$edit_cliente = null;
if (isset($_GET['id_cliente'])) {
    $id = (int)$_GET['id_cliente'];
    $result = $conn->query("SELECT * FROM clientes WHERE id_cliente = $id");
    $edit_cliente = $result->fetch_assoc();
}

// Listar todos los clientes
$result = $conn->query("SELECT * FROM clientes ORDER BY fecha_registro DESC");
$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Clientes</title>
  <link rel="stylesheet" href="styles.css">
  <style>

  

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

        /* Responsive básico */
        @media (max-width: 768px) {
            

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

  .form-titulo {
    text-align: center;
    color: #f0f0f0;
    margin-top: 20px;
    font-size: 24px;
  }

  .form-cliente {
    max-width: 600px;
    margin: 30px auto;
    background-color: #2c2c2c;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  }

  .form-group {
    margin-bottom: 20px;
  }

  label {
    font-weight: 500;
    display: block;
    margin-bottom: 6px;
    color: #cccccc;
  }

  input[type="text"],
  input[type="email"],
  textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #444;
    border-radius: 5px;
    background-color: #1e1e1e;
    color: #f0f0f0;
    font-size: 15px;
    box-sizing: border-box;
  }

  input:focus,
  textarea:focus {
    outline: none;
    border-color: #00bcd4;
    background-color: #252525;
  }

  textarea {
    resize: vertical;
    min-height: 80px;
  }

  .form-actions {
    text-align: right;
    margin-top: 25px;
  }

  .btn-guardar {
    background-color: #00bcd4;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 5px;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  .btn-guardar:hover {
    background-color: #009cb3;
  }

  .btn-cancelar {
    margin-left: 10px;
    text-decoration: none;
    color: #bbb;
    font-size: 15px;
    transition: color 0.3s ease;
  }

  .btn-cancelar:hover {
    color: #eee;
  }

  /* ✅ Responsive */
  @media (max-width: 600px) {
    .form-cliente {
      padding: 20px;
      margin: 20px;
    }

    .form-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
      align-items: stretch;
    }

    .btn-cancelar {
      text-align: center;
      margin-left: 0;
    }
  }

  body {
  background-color: #cbd5e1;
  color: #eee;
  font-family: 'Segoe UI', sans-serif;
}

.titulo-clientes {
  text-align: center;
  margin-top: 20px;
  color: #00bcd4;
  font-size: 24px;
}

.tabla-clientes {
  width: 95%;
  margin: 20px auto;
  border-collapse: collapse;
  background-color: #2b2b2b;
  box-shadow: 0 4px 10px rgba(0,0,0,0.5);
  border-radius: 8px;
  overflow: hidden;
}

.tabla-clientes th,
.tabla-clientes td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #444;
}

.tabla-clientes th {
  background-color: #333;
  color: #fff;
  font-weight: 600;
}

.tabla-clientes tr:nth-child(even) {
  background-color: #262626;
}

.tabla-clientes tr:hover {
  background-color: #383838;
}

.sin-clientes {
  text-align: center;
  padding: 20px;
  color: #bbb;
  font-style: italic;
}

.btn-link {
  color: #00bcd4;
  text-decoration: none;
  margin-right: 8px;
  transition: color 0.2s ease;
}

.btn-link:hover {
  color: #03a9f4;
  text-decoration: underline;
}

.eliminar {
  color: #e57373;
}

.eliminar:hover {
  color: #ef5350;
}

/* ✅ Responsive */
@media (max-width: 768px) {
  .tabla-clientes thead {
    display: none;
  }

  .tabla-clientes tr {
    display: block;
    margin-bottom: 20px;
    background-color: #2b2b2b;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    overflow: hidden;
  }

  .tabla-clientes td {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    border-bottom: 1px solid #333;
  }

  .tabla-clientes td::before {
    content: attr(data-label);
    font-weight: bold;
    color: #aaa;
  }

  .tabla-clientes td:last-child {
    border-bottom: none;
  }
}
</style>
</head>
<body>

<nav>
        <a href="stock.php">Ver Stock</a>
        <a href="presupuestos.php">Presupuestos</a>
        <a href="ventas.php">Ventas</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="recomendaciones.php">Recomendaciones</a>
        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </nav>

<h1 class="form-titulo">
  <?= $edit_cliente ? "Editar Cliente" : "Nuevo Cliente" ?>
</h1>

<form action="cliente_action.php" method="post" class="form-cliente">
  <input type="hidden" name="id_cliente" value="<?= $edit_cliente['id_cliente'] ?? '' ?>">

  <div class="form-group">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($edit_cliente['nombre'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($edit_cliente['email'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="telefono">Teléfono:</label>
    <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($edit_cliente['telefono'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="direccion">Dirección:</label>
    <textarea id="direccion" name="direccion"><?= htmlspecialchars($edit_cliente['direccion'] ?? '') ?></textarea>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn-guardar"><?= $edit_cliente ? "Actualizar" : "Crear" ?></button>
    <?php if ($edit_cliente): ?>
      <a href="clientes.php" class="btn-cancelar">Cancelar</a>
    <?php endif; ?>
  </div>
</form>

<hr>

<?php if (isset($_GET['error']) && $_GET['error'] === 'foranea'): ?>
  <div style="background:#ef4444;color:#fff;padding:14px 20px;border-radius:8px;margin:20px auto;max-width:600px;text-align:center;font-weight:600;font-size:1.1rem;">
    No es posible eliminar este cliente porque tiene presupuestos asociados.<br>Elimine primero los presupuestos relacionados o contacte al administrador.
  </div>
<?php endif; ?>

<h2 class="titulo-clientes">Listado de Clientes</h2>

<table class="tabla-clientes">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Email</th>
      <th>Teléfono</th>
      <th>Dirección</th>
      <th>Fecha Registro</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($clientes)): ?>
      <tr><td colspan="7" class="sin-clientes">No hay clientes registrados.</td></tr>
    <?php else: ?>
      <?php foreach ($clientes as $c): ?>
        <tr>
          <td><?= $c['id_cliente'] ?></td>
          <td><?= htmlspecialchars($c['nombre']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['telefono']) ?></td>
          <td><?= htmlspecialchars($c['direccion']) ?></td>
          <td><?= $c['fecha_registro'] ?></td>
          <td>
            <a href="clientes.php?id_cliente=<?= $c['id_cliente'] ?>" class="btn-link editar">Editar</a>
            <a href="cliente_action.php?delete=<?= $c['id_cliente'] ?>" onclick="return confirm('¿Eliminar cliente?')" class="btn-link eliminar">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
<?php include 'footer.php'; ?>

</body>
</html>
