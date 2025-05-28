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
</head>
<body>

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

<style>
  body {
    background-color: #222;
    color: #f0f0f0;
    font-family: 'Segoe UI', sans-serif;
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
</style>


<hr>

<h2> Listado de Clientes </h2>

<table border="1" cellpadding="5" cellspacing="0">
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
  <tr><td colspan="7" style="text-align:center;">No hay clientes registrados.</td></tr>
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
        <a href="clientes.php?id_cliente=<?= $c['id_cliente'] ?>">Editar</a> |
        <a href="cliente_action.php?delete=<?= $c['id_cliente'] ?>" onclick="return confirm('¿Eliminar cliente?')">Eliminar</a>
      </td>
    </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>

</table>

</body>
</html>
