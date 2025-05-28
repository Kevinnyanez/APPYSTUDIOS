<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}// Asegurate de tener la conexión a DB

// Función para obtener ventas del mes actual
$mesActual = date('m');
$anioActual = date('Y');

$sql_ventas = "
  SELECT v.*, c.nombre, p.total_con_recargo
  FROM ventas v
  JOIN clientes c ON v.id_cliente = c.id_cliente
  LEFT JOIN presupuestos p ON v.id_presupuesto = p.id_presupuesto
  WHERE MONTH(v.fecha_venta) = $mesActual AND YEAR(v.fecha_venta) = $anioActual
  ORDER BY v.fecha_venta DESC
";
$result_ventas = $conn->query($sql_ventas);

// Clientes con presupuestos activos (estado ≠ 'cerrado')
$sql_presupuestos_activos = "
  SELECT p.*, c.nombre
  FROM presupuestos p
  JOIN clientes c ON p.id_cliente = c.id_cliente
  WHERE p.estado != 'cerrado'
  ORDER BY p.fecha_creacion DESC
";
$result_presupuestos_activos = $conn->query($sql_presupuestos_activos);

// Presupuestos cerrados
$sql_presupuestos_cerrados = "
  SELECT p.*, c.nombre
  FROM presupuestos p
  JOIN clientes c ON p.id_cliente = c.id_cliente
  WHERE p.estado = 'cerrado'
  ORDER BY p.fecha_creacion DESC
";
$result_presupuestos_cerrados = $conn->query($sql_presupuestos_cerrados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Ventas</title>
  <link rel="stylesheet" href="estilos.css"> <!-- si tenés uno -->
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
      margin-bottom: 40px;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 8px 10px;
      text-align: left;
    }

    th {
      background-color: #f3f3f3;
    }

    h2 {
      margin-top: 60px;
    }
  </style>
</head>
<body>
  <h1>Panel de Ventas</h1>

  <h2>🗓️ Ventas del Mes (<?= date('F Y') ?>)</h2>
  <table>
    <thead>
      <tr>
        <th>ID Venta</th>
        <th>Cliente</th>
        <th>Presupuesto</th>
        <th>Fecha</th>
        <th>Monto Total</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result_ventas->num_rows > 0): ?>
        <?php while ($venta = $result_ventas->fetch_assoc()): ?>
          <tr>
            <td><?= $venta['id_ventas'] ?></td>
            <td><?= htmlspecialchars($venta['nombre']) ?></td>
            <td>#<?= $venta['id_presupuesto'] ?? '—' ?></td>
            <td><?= $venta['fecha_venta'] ?></td>
            <td>$<?= number_format($venta['monto_total'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="5">No se registraron ventas este mes.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <h2>📝 Presupuestos en Proceso</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Fecha</th>
        <th>Estado</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result_presupuestos_activos->num_rows > 0): ?>
        <?php while ($p = $result_presupuestos_activos->fetch_assoc()): ?>
          <tr>
            <td>#<?= $p['id_presupuesto'] ?></td>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td><?= $p['fecha_creacion'] ?></td>
            <td><?= ucfirst($p['estado']) ?></td>
            <td>$<?= number_format($p['total_con_recargo'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="5">No hay presupuestos activos.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <form method="post" action="confirmar_venta.php" class="form-confirmar-venta">
    <input type="hidden" name="id_presupuesto" value="<?= $presupuesto['id_presupuesto'] ?>">
    <button type="submit">Confirmar Venta</button>
</form>

  <h2>✅ Presupuestos Cerrados</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Fecha</th>
        <th>Total</th>
        <th>Recargo</th>
        <th>Total Final</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result_presupuestos_cerrados->num_rows > 0): ?>
        <?php while ($p = $result_presupuestos_cerrados->fetch_assoc()): ?>
          <tr>
            <td>#<?= $p['id_presupuesto'] ?></td>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td><?= $p['fecha_creacion'] ?></td>
            <td>$<?= number_format($p['total'], 2) ?></td>
            <td>$<?= number_format($p['recargo_final'], 2) ?></td>
            <td>$<?= number_format($p['total_con_recargo'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6">No hay presupuestos cerrados.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  

</body>
<script>
document.querySelectorAll('.form-confirmar-venta').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        fetch('confirmar_venta.php', {
            method: 'POST',
            body: formData
        })
        .then(resp => resp.text())
        .then(data => {
            if (data.trim() === "ok") {
                alert("Venta registrada correctamente.");
                location.reload();
            } else if (data.trim() === "existente") {
                alert("Este presupuesto ya fue convertido en venta.");
            } else {
                alert("Error al registrar la venta.");
            }
        });
    });
});
</script>

</html>
