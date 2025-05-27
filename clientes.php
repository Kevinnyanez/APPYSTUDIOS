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

<h1><?= $edit_cliente ? "Editar Cliente" : "Nuevo Cliente" ?></h1>

<form action="cliente_action.php" method="post">
  <input type="hidden" name="id_cliente" value="<?= $edit_cliente['id_cliente'] ?? '' ?>">

  
  <label class="claseLabel"> Nombre:</label><br>


  <input type="text" name="nombre" required value="<?= htmlspecialchars($edit_cliente['nombre'] ?? '') ?>"><br><br>
  
  <label>Email:</label><br>
  <input type="email" name="email" value="<?= htmlspecialchars($edit_cliente['email'] ?? '') ?>"><br><br>
  
  <label>Teléfono:</label><br>
  <input type="text" name="telefono" value="<?= htmlspecialchars($edit_cliente['telefono'] ?? '') ?>"><br><br>
  
  <label>Dirección:</label><br>
  <textarea name="direccion"><?= htmlspecialchars($edit_cliente['direccion'] ?? '') ?></textarea><br><br>
  
  <button type="submit"><?= $edit_cliente ? "Actualizar" : "Crear" ?></button>
  <?php if ($edit_cliente): ?>
    <a href="clientes.php">Cancelar</a>
  <?php endif; ?>
</form>

<style>

.claseLabel{
            color: blue;
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
