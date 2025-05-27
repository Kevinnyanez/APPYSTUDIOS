<?php
include 'includes/db.php';

// Cargar presupuestos con el nombre del cliente
$sql = "SELECT p.*, c.nombre AS nombre_cliente
        FROM presupuestos p
        JOIN clientes c ON p.id_cliente = c.id_cliente
        ORDER BY p.fecha_creacion DESC";
$result = $conn->query($sql);

$presupuestos = [];
while ($row = $result->fetch_assoc()) {
    $presupuestos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Presupuestos</title>
</head>
<body>

<h1>Presupuestos</h1>

<a href="presupuesto_form.php">+ Nuevo Presupuesto</a>

<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr>
      <th>ID</th>
      <th>Cliente</th>
      <th>Fecha</th>
      <th>Total</th>
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($presupuestos)): ?>
      <tr><td colspan="6">No hay presupuestos registrados.</td></tr>
    <?php else: ?>
      <?php foreach ($presupuestos as $p): ?>
        <tr>
          <td><?= $p['id_presupuestos'] ?></td>
          <td><?= htmlspecialchars($p['nombre_cliente']) ?></td>
          <td><?= $p['fecha_creacion'] ?></td>
          <td>$<?= number_format($p['total'], 2) ?></td>
          <td><?= ucfirst($p['estado']) ?></td>
          <td>
            <a href="presupuesto_form.php?id_presupuesto=<?= $p['id_presupuestos'] ?>">Ver / Editar</a>
            <?php if ($p['estado'] === 'activo'): ?>
              | <a href="presupuesto_action.php?cerrar=<?= $p['id_presupuestos'] ?>" onclick="return confirm('¿Cerrar presupuesto?')">Cerrar</a>
            <?php endif; ?>
            | <a href="presupuesto_action.php?delete=<?= $p['id_presupuestos'] ?>" onclick="return confirm('¿Eliminar presupuesto?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

</body>
</html>
