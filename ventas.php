<?php
// ventas.php
include 'includes/db.php'; // Asegurate de tener esto apuntando a tu archivo de conexión

// Traemos presupuestos cerrados (ventas)
$sql = "SELECT p.id_presupuesto, p.fecha_creacion, p.total_con_recargo, c.nombre
        FROM presupuestos p
        JOIN clientes c ON p.id_cliente = c.id_cliente
        WHERE p.estado = 'cerrado'
        ORDER BY p.fecha_creacion DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ventas Confirmadas</title>
  <style>
    body {
      font-family: sans-serif;
      padding: 20px;
    }
    h1 {
      color: #2c3e50;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ccc;
    }
    th {
      background-color: #ecf0f1;
    }
    .total {
      font-weight: bold;
      color: green;
    }
  </style>
</head>
<body>

<h1>🧾 Ventas Confirmadas</h1>

<table>
  <thead>
    <tr>
      <th># Presupuesto</th>
      <th>Cliente</th>
      <th>Fecha</th>
      <th>Total Pagado</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td>#<?= $row['id_presupuesto'] ?></td>
          <td><?= htmlspecialchars($row['nombre']) ?></td>
          <td><?= date('d/m/Y', strtotime($row['fecha_creacion'])) ?></td>
          <td class="total">$<?= number_format($row['total_con_recargo'], 2, ',', '.') ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="4">No hay ventas registradas todavía.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

</body>
</html>
